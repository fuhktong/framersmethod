<?php
/**
 * Simple DKIM Email Signer
 * Reliable DKIM implementation for email authentication
 */
class DKIMSigner {
    private $private_key;
    private $selector;
    private $domain;
    
    public function __construct($private_key_path, $selector = 'mail', $domain = '') {
        if (!file_exists($private_key_path)) {
            throw new Exception("DKIM private key not found: $private_key_path");
        }
        
        $this->private_key = file_get_contents($private_key_path);
        $this->selector = $selector;
        $this->domain = $domain ?: 'framersmethod.com';
    }
    
    /**
     * Generate DKIM signature header
     */
    public function getDKIMHeader($from, $to, $subject, $body, $date) {
        // Create body hash (simple canonicalization)
        $body_canonical = $this->canonicalizeBody($body);
        $body_hash = base64_encode(hash('sha256', $body_canonical, true));
        
        // Headers to sign
        $headers_to_sign = [
            'from' => $from,
            'to' => $to, 
            'subject' => $subject,
            'date' => $date
        ];
        
        // Build headers string for signing
        $header_string = '';
        foreach ($headers_to_sign as $name => $value) {
            $header_string .= $name . ':' . $this->canonicalizeHeaderValue($value) . "\r\n";
        }
        
        // DKIM signature header (without signature value)
        $dkim_header = "dkim-signature:v=1; a=rsa-sha256; c=simple/simple; d={$this->domain}; s={$this->selector}; t=" . time() . "; h=from:to:subject:date; bh=$body_hash; b=";
        
        // Add DKIM header to signing string
        $signing_string = $header_string . $dkim_header;
        
        // Create signature
        $signature = '';
        if (!openssl_sign($signing_string, $signature, $this->private_key, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Failed to create DKIM signature');
        }
        
        $signature_b64 = base64_encode($signature);
        
        // Return complete DKIM-Signature header
        return "DKIM-Signature: v=1; a=rsa-sha256; c=simple/simple; d={$this->domain}; s={$this->selector}; t=" . time() . "; h=from:to:subject:date; bh=$body_hash; b=$signature_b64\r\n";
    }
    
    private function canonicalizeBody($body) {
        // Simple canonicalization: normalize line endings
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("\n", "\r\n", $body);
        
        // Ensure body ends with CRLF
        if (!str_ends_with($body, "\r\n")) {
            $body .= "\r\n";
        }
        
        return $body;
    }
    
    private function canonicalizeHeaderValue($value) {
        // Simple canonicalization: trim whitespace
        return trim($value);
    }
    
    /**
     * Generate DKIM key pair
     */
    public static function generateKeyPair($key_size = 1024) {
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => $key_size,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        $res = openssl_pkey_new($config);
        if (!$res) {
            throw new Exception('Failed to generate key pair');
        }
        
        // Extract private key
        openssl_pkey_export($res, $private_key);
        
        // Extract public key
        $public_key_details = openssl_pkey_get_details($res);
        $public_key = $public_key_details["key"];
        
        return [
            'private' => $private_key,
            'public' => $public_key
        ];
    }
    
    /**
     * Get DNS record for DKIM public key
     */
    public static function getDNSRecord($public_key, $selector = 'mail') {
        // Extract public key modulus and exponent
        $public_key_resource = openssl_pkey_get_public($public_key);
        $public_key_details = openssl_pkey_get_details($public_key_resource);
        
        // Convert to base64 without headers
        $public_key_clean = preg_replace('/-----[^-]+-----/', '', $public_key);
        $public_key_clean = str_replace(["\r", "\n", " "], '', $public_key_clean);
        
        return [
            'name' => "$selector._domainkey",
            'type' => 'TXT',
            'value' => "v=DKIM1; k=rsa; p=$public_key_clean"
        ];
    }
}
?>