<?php
/**
 * Public subscribe endpoint (double opt-in).
 * Adds the email as "pending" and emails a confirmation link; the subscriber
 * becomes "active" only after clicking it (see confirm.php).
 * "website" is a honeypot field — if filled, it's a bot.
 */
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/mailer.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email = trim($input['email'] ?? '');
$honey = trim($input['website'] ?? '');

// Honeypot tripped: pretend success so the bot moves on, but do nothing.
if ($honey !== '') {
    echo json_encode(['success' => true, 'message' => 'Almost there! Check your inbox to confirm.']);
    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT id, status FROM subscribers WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing && $existing['status'] === 'active') {
        echo json_encode(['success' => true, 'message' => "You're already subscribed."]);
        exit;
    }

    $confirm = bin2hex(random_bytes(32)); // 64 hex chars

    if ($existing) {
        // pending / unsubscribed / paused → reset to pending with a fresh token
        $pdo->prepare("UPDATE subscribers SET status = 'pending', confirm_token = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$confirm, $existing['id']]);
    } else {
        $unsubscribe = hash('sha256', $email . microtime(true) . bin2hex(random_bytes(8)));
        $pdo->prepare(
            "INSERT INTO subscribers (email, status, unsubscribe_token, confirm_token, subscribed_at)
             VALUES (?, 'pending', ?, ?, NOW())"
        )->execute([$email, $unsubscribe, $confirm]);
    }

    // Sends the confirmation link (logs, doesn't throw, if SMTP isn't configured)
    send_confirmation_email($email, $confirm);

    $message = $existing && $existing['status'] === 'pending'
        ? "We've re-sent your confirmation link — please check your inbox."
        : 'Almost there! Check your inbox to confirm your subscription.';

    echo json_encode(['success' => true, 'message' => $message]);
} catch (Throwable $e) {
    error_log('Subscribe error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
