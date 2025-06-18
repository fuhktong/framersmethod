<?php
require_once 'contact/smtp_mailer.php';
require_once 'contact/env_loader.php';

// Load environment variables or use fallback  
$env_loaded = loadEnv(__DIR__ . '/../.env');

// Fallback SMTP configuration if .env file doesn't exist
$smtp_config = [
    'host' => env('SMTP_HOST') ?: 'smtp.hostinger.com',
    'port' => (int)(env('SMTP_PORT') ?: 587),
    'username' => env('SMTP_USERNAME') ?: 'contact@framersmethod.com',
    'password' => env('SMTP_PASSWORD') ?: 'ZAQHos@6nt',
    'use_tls' => (env('SMTP_USE_TLS') ?: 'true') === 'true'
];

echo "Debug Info:<br>";
echo "Env file loaded: " . ($env_loaded ? 'Yes' : 'No') . "<br>";
echo "SMTP Host: " . $smtp_config['host'] . "<br>";
echo "SMTP Port: " . $smtp_config['port'] . "<br>";
echo "SMTP Username: " . $smtp_config['username'] . "<br>";
echo "SMTP Use TLS: " . ($smtp_config['use_tls'] ? 'true' : 'false') . "<br><br>";

$to = "test-9d2664@test.mailgenius.com";
$subject = "Email Deliverability Test - Framers Method";
$message = "This is a test email to verify SMTP email delivery and spam score.\n\n";
$message .= "Test Details:\n";
$message .= "- Sent via: SMTP Authentication\n";
$message .= "- From: " . $smtp_config['username'] . "\n";
$message .= "- Server: " . $smtp_config['host'] . "\n";
$message .= "- Date: " . date('Y-m-d H:i:s') . "\n\n";
$message .= "This email should pass SPF, DKIM, and DMARC checks.";

$mailer = new SimpleSmtpMailer(
    $smtp_config['host'],
    $smtp_config['port'],
    $smtp_config['username'],
    $smtp_config['password'],
    $smtp_config['use_tls']
);

$result = $mailer->sendMail(
    $to,
    $subject,
    $message,
    $smtp_config['username'],
    'Framers Method',
    $smtp_config['username']
);

if ($result === true) {
    echo "✓ Test email sent successfully to " . $to . "\n";
    echo "SMTP Host: " . $smtp_config['host'] . "\n";
    echo "SMTP Port: " . $smtp_config['port'] . "\n";
    echo "From: " . $smtp_config['username'] . "\n";
    echo "\nCheck mail-tester.com for spam score results.";
} else {
    echo "✗ Failed to send test email: " . $result;
}
?>