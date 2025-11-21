<?php
/**
 * Simple SMTP Mailer using PHP sockets
 * No external dependencies required
 */
require_once 'dkim_signer.php';

class SimpleSmtpMailer {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $use_tls;
    private $dkim_signer;
    
    public function __construct($host, $port, $username, $password, $use_tls = true, $dkim_key_path = null, $dkim_selector = 'mail', $dkim_domain = null) {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->use_tls = $use_tls;
        
        // Initialize DKIM signer if key is provided
        if ($dkim_key_path && file_exists($dkim_key_path)) {
            try {
                $this->dkim_signer = new DKIMSigner($dkim_key_path, $dkim_selector, $dkim_domain);
            } catch (Exception $e) {
                error_log("DKIM initialization failed: " . $e->getMessage());
                $this->dkim_signer = null;
            }
        }
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
        
        // Prepare email data
        $date = date('r');
        $from_header = $from_name ? "$from_name <$from_email>" : $from_email;
        
        // Generate DKIM signature if available
        $dkim_header = '';
        if ($this->dkim_signer) {
            try {
                $dkim_header = $this->dkim_signer->getDKIMHeader($from_header, $to, $subject, $message, $date);
            } catch (Exception $e) {
                error_log("DKIM signing failed: " . $e->getMessage());
            }
        }
        
        // Build complete email headers
        $headers = $dkim_header;
        $headers .= "From: $from_header\r\n";
        $headers .= "To: $to\r\n";
        if ($reply_to) {
            $headers .= "Reply-To: $reply_to\r\n";
        }
        $headers .= "Subject: $subject\r\n";
        $headers .= "Date: $date\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        if ($is_html) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        // Add additional headers for better deliverability
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "X-Priority: 3\r\n";
        
        // Combine all data for SMTP
        $email_data = $headers . "\r\n" . $message . "\r\n.\r\n";
        
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