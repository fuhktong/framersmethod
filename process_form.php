<?php
// Prevent any unwanted output
ini_set('display_errors', 0);
error_reporting(0);

// Ensure clean output buffer
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log errors to file instead of output
error_log("Received form submission: " . print_r($data, true));

// Validate the data
if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Sanitize the inputs
$name = filter_var($data['name'], FILTER_SANITIZE_STRING);
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$message = filter_var($data['message'], FILTER_SANITIZE_STRING);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Email settings
$to = 'info@framersmethod.com';  // Replace with your email
$subject = 'New Contact Form Submission';
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Compose email message
$email_message = "Name: $name\n";
$email_message .= "Email: $email\n\n";
$email_message .= "Message:\n$message";

// Send email
$mail_sent = mail($to, $subject, $email_message, $headers);

// Ensure no other output has occurred
if (ob_get_length()) ob_clean();

// Return response
echo json_encode([
    'success' => $mail_sent,
    'message' => $mail_sent ? 'Message sent successfully' : 'Failed to send message'
]);
exit;
?>