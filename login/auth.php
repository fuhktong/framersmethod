<?php
/**
 * Authentication Handler
 * Simple ENV-based login system
 */
require_once '../contact/env_loader.php';

// Start session
session_start();

// Load environment variables
$env_paths = [
    __DIR__ . '/../.env',
    __DIR__ . '/../../.env',
];

foreach ($env_paths as $env_path) {
    if (file_exists($env_path)) {
        loadEnv($env_path);
        break;
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

try {
    // Get credentials from environment
    $adminUsername = $_ENV['ADMIN_USERNAME'] ?? '';
    $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? '';
    
    if (empty($adminUsername) || empty($adminPassword)) {
        throw new Exception('Admin credentials not configured');
    }
    
    // Rate limiting - simple IP-based check
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attemptKey = 'login_attempts_' . md5($clientIp);
    
    if (!isset($_SESSION[$attemptKey])) {
        $_SESSION[$attemptKey] = ['count' => 0, 'last_attempt' => 0];
    }
    
    $attempts = $_SESSION[$attemptKey];
    $currentTime = time();
    
    // Reset attempts after 15 minutes
    if ($currentTime - $attempts['last_attempt'] > 900) {
        $_SESSION[$attemptKey] = ['count' => 0, 'last_attempt' => $currentTime];
        $attempts = $_SESSION[$attemptKey];
    }
    
    // Check if too many attempts
    if ($attempts['count'] >= 5) {
        $timeLeft = 900 - ($currentTime - $attempts['last_attempt']);
        throw new Exception("Too many failed attempts. Try again in " . ceil($timeLeft/60) . " minutes.");
    }
    
    // Validate credentials
    if ($username === $adminUsername && $password === $adminPassword) {
        // Success - create session
        session_regenerate_id(true); // Prevent session fixation
        
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Clear failed attempts
        unset($_SESSION[$attemptKey]);
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '../emailservice/index.php'
        ]);
        
    } else {
        // Failed login
        $_SESSION[$attemptKey]['count']++;
        $_SESSION[$attemptKey]['last_attempt'] = $currentTime;
        
        throw new Exception('Invalid username or password');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>