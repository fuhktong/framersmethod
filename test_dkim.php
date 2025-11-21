<?php
/**
 * Test DKIM Implementation
 */
require_once 'contact/smtp_mailer.php';
require_once 'contact/env_loader.php';

// Load environment
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    loadEnv($env_path);
}

echo "Testing DKIM Implementation\n";
echo "==========================\n\n";

// Check DKIM configuration
$dkim_key_path = $_ENV['DKIM_PRIVATE_KEY_PATH'] ?? '';
$dkim_selector = $_ENV['DKIM_SELECTOR'] ?? 'mail';
$dkim_domain = $_ENV['DKIM_DOMAIN'] ?? 'framersmethod.com';

echo "DKIM Key Path: $dkim_key_path\n";
echo "DKIM Selector: $dkim_selector\n";
echo "DKIM Domain: $dkim_domain\n";
echo "Key Exists: " . (file_exists($dkim_key_path) ? 'YES' : 'NO') . "\n\n";

if (!file_exists($dkim_key_path)) {
    echo "ERROR: DKIM private key not found. Generate keys first.\n";
    exit(1);
}

// Test SMTP configuration
$smtp_config = [
    'host' => $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com',
    'port' => (int)($_ENV['SMTP_PORT'] ?? 587),
    'username' => $_ENV['SMTP_USERNAME'] ?? '',
    'password' => $_ENV['SMTP_PASSWORD'] ?? '',
    'use_tls' => ($_ENV['SMTP_USE_TLS'] ?? 'true') === 'true'
];

echo "SMTP Configuration:\n";
echo "Host: {$smtp_config['host']}:{$smtp_config['port']}\n";
echo "Username: {$smtp_config['username']}\n";
echo "Password: " . (empty($smtp_config['password']) ? 'NOT SET' : 'SET') . "\n";
echo "TLS: " . ($smtp_config['use_tls'] ? 'YES' : 'NO') . "\n\n";

try {
    // Create mailer with DKIM
    $mailer = new SimpleSmtpMailer(
        $smtp_config['host'],
        $smtp_config['port'],
        $smtp_config['username'],
        $smtp_config['password'],
        $smtp_config['use_tls'],
        $dkim_key_path,
        $dkim_selector,
        $dkim_domain
    );
    
    echo "✓ SMTP Mailer with DKIM initialized successfully\n";
    
    // Test email (change this to your email)
    $test_email = 'contact@framersmethod.com'; 
    
    if ($test_email === 'your-email@example.com') {
        echo "\nTo send a test email, edit this script and set \$test_email to your actual email address.\n";
    } else {
        echo "\nSending test email to: $test_email\n";
        
        $result = $mailer->sendMail(
            $test_email,
            '[DKIM TEST] Test Email with DKIM Signature',
            'This is a test email to verify DKIM signing is working properly.',
            $smtp_config['username'],
            'The Framers Method',
            $smtp_config['username'],
            false
        );
        
        if ($result === true) {
            echo "✓ Test email sent successfully!\n";
            echo "\nCheck the email headers for DKIM-Signature to verify it's working.\n";
        } else {
            echo "✗ Test email failed: $result\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
