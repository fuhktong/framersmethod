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
    return (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true)
        || !empty($_SESSION['admin_id']);
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
 * Require authentication - redirect to login if not authenticated.
 * All admin pages live under /admin, so the login route is always /admin/login.
 */
function requireAuth($redirectPath = '/admin/login') {
    if (!isSessionValid()) {
        // Clear invalid session, then send the user to the login page
        session_destroy();
        header("Location: $redirectPath");
        exit();
    }
}

/**
 * Authenticate a username/password against the users table and, on success,
 * populate the session. Single source of truth for logging a user in, shared by
 * the admin login form and the hidden team-page modal so both behave identically.
 *
 * @return bool true if the credentials were valid and the session was created.
 */
function loginUser($username, $password) {
    require_once __DIR__ . '/../database/db.php';

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true); // prevent session fixation
    $_SESSION['admin_id']       = $user['id'];
    $_SESSION['admin_username'] = $username;
    $_SESSION['authenticated']  = true;
    $_SESSION['username']       = $username;
    $_SESSION['login_time']     = time();
    $_SESSION['last_activity']  = time();
    $_SESSION['csrf_token']     = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

    return true;
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
