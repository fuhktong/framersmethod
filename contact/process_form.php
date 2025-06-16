<?php
ini_set('display_errors', 0);
error_reporting(0);

ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include SMTP mailer and environment loader
require_once 'smtp_mailer.php';
require_once 'env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../.env');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(trim($data['message']), ENT_QUOTES, 'UTF-8');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// SMTP Configuration - Load from environment variables
$smtp_config = [
    'host' => env('SMTP_HOST', 'smtp.hostinger.com'),
    'port' => (int)env('SMTP_PORT', 587),
    'username' => env('SMTP_USERNAME'),
    'password' => env('SMTP_PASSWORD'),
    'use_tls' => env('SMTP_USE_TLS', 'true') === 'true'
];

$to = "contact@framersmethod.com";
$subject = 'New Contact Form Submission';
$email_message = "Name: $name\n";
$email_message .= "Email: $email\n\n";
$email_message .= "Message:\n$message";

// Create SMTP mailer instance
$mailer = new SimpleSmtpMailer(
    $smtp_config['host'],
    $smtp_config['port'],
    $smtp_config['username'],
    $smtp_config['password'],
    $smtp_config['use_tls']
);

// Send email
$mail_result = $mailer->sendMail(
    $to,                           // To
    $subject,                      // Subject
    $email_message,               // Message
    $smtp_config['username'],     // From email
    'Website Contact Form',       // From name
    $email                        // Reply-to
);

$mail_sent = ($mail_result === true);

if (ob_get_length()) ob_clean();

echo json_encode([
    'success' => $mail_sent,
    'message' => $mail_sent ? 'Message sent successfully' : 'Failed to send message: ' . $mail_result
]);
exit;
?>