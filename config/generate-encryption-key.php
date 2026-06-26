<?php
/**
 * Encryption Key Generator
 * 
 * Generates a secure encryption key for SETTINGS_ENCRYPTION_KEY
 * 
 * Usage: php config/generate-encryption-key.php
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Generate a secure random key
$key = bin2hex(random_bytes(32)); // 64 character hex string

echo "========================================\n";
echo "Encryption Key Generator\n";
echo "========================================\n\n";
echo "Generated Key:\n";
echo $key . "\n\n";
echo "Add this to your .env file:\n";
echo "SETTINGS_ENCRYPTION_KEY=" . $key . "\n\n";
echo "Or add to your environment config file.\n";
echo "========================================\n";


