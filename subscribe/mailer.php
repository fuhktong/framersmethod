<?php
/**
 * Sends the double opt-in confirmation email for new subscribers.
 * Reuses the SMTP config (SMTP_* env vars) and SimpleSmtpMailer used elsewhere.
 */
require_once __DIR__ . '/../contact/smtp_mailer.php';

function subscribe_base_url(): string
{
    if (!empty($_ENV['BASE_URL'])) {
        return rtrim($_ENV['BASE_URL'], '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

/**
 * @return bool true if the email was accepted by the SMTP server.
 */
function send_confirmation_email(string $email, string $token): bool
{
    $from = $_ENV['SMTP_FROM_EMAIL'] ?? '';
    $host = $_ENV['SMTP_HOST'] ?? '';

    if ($from === '' || $host === '') {
        error_log('Subscribe: SMTP not configured; confirmation email not sent to ' . $email);
        return false;
    }

    $url = subscribe_base_url() . '/subscribe/confirm.php?token=' . urlencode($token);
    $subject = "Confirm your subscription to The Framers' Method";
    $body =
        "Thanks for subscribing to The Framers' Method.\r\n\r\n" .
        "Please confirm your email address by opening this link:\r\n" .
        $url . "\r\n\r\n" .
        "If you didn't request this, you can safely ignore this email.";

    $mailer = new SimpleSmtpMailer(
        $host,
        (int) ($_ENV['SMTP_PORT'] ?? 587),
        $_ENV['SMTP_USERNAME'] ?? '',
        $_ENV['SMTP_PASSWORD'] ?? '',
        ($_ENV['SMTP_USE_TLS'] ?? 'true') === 'true'
    );

    $result = $mailer->sendMail(
        $email,
        $subject,
        $body,
        $from,
        $_ENV['SMTP_FROM_NAME'] ?? "The Framers' Method"
    );

    if ($result !== true) {
        error_log('Subscribe: confirmation email failed: ' . $result);
        return false;
    }
    return true;
}
