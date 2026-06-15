<?php
/**
 * Authentication handler for the hidden team-page login modal.
 * Validates credentials against the users table (same store as /admin/login)
 * via the shared loginUser() helper, with simple IP-based rate limiting.
 */
require_once __DIR__ . '/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

try {
    // Rate limiting - simple IP-based check
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attemptKey = 'login_attempts_' . md5($clientIp);
    $currentTime = time();

    if (!isset($_SESSION[$attemptKey])) {
        $_SESSION[$attemptKey] = ['count' => 0, 'last_attempt' => 0];
    }

    $attempts = $_SESSION[$attemptKey];

    // Reset attempts after 15 minutes
    if ($currentTime - $attempts['last_attempt'] > 900) {
        $_SESSION[$attemptKey] = ['count' => 0, 'last_attempt' => $currentTime];
        $attempts = $_SESSION[$attemptKey];
    }

    // Check if too many attempts
    if ($attempts['count'] >= 5) {
        $timeLeft = 900 - ($currentTime - $attempts['last_attempt']);
        throw new Exception('Too many failed attempts. Try again in ' . ceil($timeLeft / 60) . ' minutes.');
    }

    if ($username && $password && loginUser($username, $password)) {
        // Success - clear failed attempts (session data survives regenerate)
        unset($_SESSION[$attemptKey]);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/admin'
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
