<?php
/**
 * DKIM Email Signer
 * Adds DKIM signatures to emails for better deliverability
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
        $this->domain = $domain ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    
    /**
     * Sign email headers with DKIM
     */
    public function signEmail($headers, $body) {
        // Normalize headers
        $normalized_headers = $this->normalizeHeaders($headers);
        
        // Create body hash
        $body_hash = base64_encode(hash('sha256', $this->canonicalizeBody($body), true));
        
        // Create DKIM signature header (without signature)
        $dkim_header = "DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed; d={$this->domain}; s={$this->selector}; t=" . time() . "; bh=$body_hash; h=From:To:Subject:Date; b=";
        
        // Canonicalize headers for signing
        $header_string = $this->canonicalizeHeaders($normalized_headers . $dkim_header);
        
        // Sign the header string
        $signature = '';
        if (openssl_sign($header_string, $signature, $this->private_key, OPENSSL_ALGO_SHA256)) {
            $signature_b64 = base64_encode($signature);
            // Break long lines
            $signature_b64 = chunk_split($signature_b64, 76, "\r\n\t");
            $signature_b64 = rtrim($signature_b64);
            
            return "DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed; d={$this->domain}; s={$this->selector}; t=" . time() . "; bh=$body_hash; h=From:To:Subject:Date; b=$signature_b64\r\n";
        } else {
            throw new Exception('Failed to create DKIM signature');
        }
    }
    
    private function normalizeHeaders($headers) {
        return preg_replace('/\r\n\s+/', ' ', $headers);
    }
    
    private function canonicalizeHeaders($headers) {
        // Simple relaxed canonicalization
        $lines = explode("\r\n", $headers);
        $canonical = '';
        
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                // Convert to lowercase header name, trim whitespace
                $line = preg_replace('/^([^:]+):(.*)$/', function($matches) {
                    return strtolower(trim($matches[1])) . ':' . trim($matches[2]);
                }, $line);
                $canonical .= $line . "\r\n";
            }
        }
        
        return rtrim($canonical, "\r\n");
    }
    
    private function canonicalizeBody($body) {
        // Simple relaxed canonicalization
        $body = preg_replace('/[ \t]+/', ' ', $body);
        $body = preg_replace('/[ \t]*\r\n/', "\r\n", $body);
        $body = rtrim($body, "\r\n") . "\r\n";
        
        return $body;
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