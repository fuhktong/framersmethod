<?php
/**
 * Simple SMTP Mailer using PHP sockets
 * No external dependencies required
 */
class SimpleSmtpMailer {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $use_tls;
    
    public function __construct($host, $port, $username, $password, $use_tls = true) {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->use_tls = $use_tls;
    }
    
    public function sendMail($to, $subject, $message, $from_email, $from_name = '', $reply_to = '', $is_html = false) {
        // Use SSL connection for port 465, TLS for 587
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        if ($this->smtp_port == 465) {
            // SSL connection
            $socket = stream_socket_client("ssl://{$this->smtp_host}:{$this->smtp_port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        } else {
            // Regular connection (will upgrade with STARTTLS if needed)
            $socket = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
        }
        
        if (!$socket) {
            return "Connection failed: $errstr ($errno) - Host: {$this->smtp_host}:{$this->smtp_port}";
        }
        
        // Read initial response
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '220') {
            fclose($socket);
            return "SMTP Error: $response";
        }
        
        // EHLO
        $hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
        fputs($socket, "EHLO " . $hostname . "\r\n");
        
        // Read all EHLO responses (multi-line)
        do {
            $response = fgets($socket, 512);
        } while (substr($response, 3, 1) == '-');
        
        // STARTTLS if required
        if ($this->use_tls) {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return "STARTTLS failed: $response";
            }
            
            // Enable TLS encryption
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return "Failed to enable TLS encryption";
            }
            
            // EHLO again after TLS
            fputs($socket, "EHLO " . $hostname . "\r\n");
            
            // Read all EHLO responses again (multi-line)
            do {
                $response = fgets($socket, 512);
            } while (substr($response, 3, 1) == '-');
        }
        
        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 512);
        
        fputs($socket, base64_encode($this->smtp_username) . "\r\n");
        $response = fgets($socket, 512);
        
        fputs($socket, base64_encode($this->smtp_password) . "\r\n");
        $response = fgets($socket, 512);
        
        if (substr($response, 0, 3) != '235') {
            fclose($socket);
            return "Authentication failed: $response";
        }
        
        // MAIL FROM
        fputs($socket, "MAIL FROM: <$from_email>\r\n");
        $response = fgets($socket, 512);
        
        // RCPT TO
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 512);
        
        // DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 512);
        
        // Email headers and body
        $email_data = "From: " . ($from_name ? "$from_name <$from_email>" : $from_email) . "\r\n";
        $email_data .= "To: $to\r\n";
        if ($reply_to) {
            $email_data .= "Reply-To: $reply_to\r\n";
        }
        $email_data .= "Subject: $subject\r\n";
        $email_data .= "MIME-Version: 1.0\r\n";
        
        if ($is_html) {
            $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $email_data .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        // Add additional headers for better deliverability
        $email_data .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $email_data .= "X-Priority: 3\r\n";
        $email_data .= "Date: " . date('r') . "\r\n";
        
        $email_data .= "\r\n";
        $email_data .= $message . "\r\n";
        $email_data .= ".\r\n";
        
        fputs($socket, $email_data);
        $response = fgets($socket, 512);
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        if (substr($response, 0, 3) == '250') {
            return true;
        } else {
            return "Send failed: $response";
        }
    }
}
?>