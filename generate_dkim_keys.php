<?php
/**
 * Generate DKIM Keys for Email Authentication
 */
require_once 'contact/dkim_signer.php';

$domain = 'framersmethod.com';
$selector = 'mail';

echo "Generating DKIM keys for domain: $domain\n";
echo "Selector: $selector\n\n";

try {
    // Generate key pair
    $keys = DKIMSigner::generateKeyPair(1024);
    
    // Create keys directory if it doesn't exist
    $keys_dir = __DIR__ . '/keys';
    if (!is_dir($keys_dir)) {
        mkdir($keys_dir, 0700, true);
    }
    
    // Save private key
    $private_key_path = "$keys_dir/dkim_private.key";
    file_put_contents($private_key_path, $keys['private']);
    chmod($private_key_path, 0600);
    
    // Save public key
    $public_key_path = "$keys_dir/dkim_public.key";
    file_put_contents($public_key_path, $keys['public']);
    
    echo "✓ Private key saved to: $private_key_path\n";
    echo "✓ Public key saved to: $public_key_path\n\n";
    
    // Generate DNS record
    $dns_record = DKIMSigner::getDNSRecord($keys['public'], $selector);
    
    echo "DNS Record to add:\n";
    echo "================\n";
    echo "Name: {$dns_record['name']}.{$domain}\n";
    echo "Type: {$dns_record['type']}\n";
    echo "Value: {$dns_record['value']}\n\n";
    
    echo "Complete DNS record:\n";
    echo "{$dns_record['name']}.{$domain}. IN TXT \"{$dns_record['value']}\"\n\n";
    
    echo "Configuration:\n";
    echo "=============\n";
    echo "Add to your .env file:\n";
    echo "DKIM_PRIVATE_KEY_PATH=" . realpath($private_key_path) . "\n";
    echo "DKIM_SELECTOR=$selector\n";
    echo "DKIM_DOMAIN=$domain\n\n";
    
    echo "After adding the DNS record, test with:\n";
    echo "dig TXT {$dns_record['name']}.{$domain}\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>