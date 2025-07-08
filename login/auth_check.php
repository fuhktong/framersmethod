<?php
/**
 * Authentication Check
 * Include this file at the top of protected pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Check if session is valid and not expired
 */
function isSessionValid() {
    if (!isAuthenticated()) {
        return false;
    }
    
    // Check session timeout (24 hours)
    $sessionTimeout = 24 * 60 * 60; // 24 hours in seconds
    
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $sessionTimeout) {
            return false;
        }
    }
    
    // Check activity timeout (2 hours of inactivity)
    $activityTimeout = 2 * 60 * 60; // 2 hours in seconds
    
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $activityTimeout) {
            return false;
        }
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth($redirectPath = '../login/login.php') {
    if (!isSessionValid()) {
        // Clear invalid session
        session_destroy();
        
        // Determine the correct login URL based on current location
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        if (strpos($requestUri, '/login/') !== false) {
            // We're in the login directory
            $loginUrl = 'login.php';
        } elseif (strpos($requestUri, '/emailservice/') !== false) {
            // We're in the emailservice directory
            $loginUrl = '../login/login.php';
        } else {
            // We're in the root or other directory
            $loginUrl = 'login/login.php';
        }
        
        header("Location: $loginUrl");
        exit();
    }
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'username' => $_SESSION['username'] ?? 'admin',
        'login_time' => $_SESSION['login_time'] ?? 0,
        'last_activity' => $_SESSION['last_activity'] ?? 0
    ];
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Logout user
 */
function logout() {
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

// Auto-check authentication if this file is included (not called directly)
if (basename($_SERVER['PHP_SELF']) !== 'auth_check.php') {
    // Only auto-check if we're not in the login directory
    if (strpos($_SERVER['REQUEST_URI'], '/login/') === false) {
        requireAuth();
    }
}
?>