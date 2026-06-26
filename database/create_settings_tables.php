<?php
/**
 * Create Settings Tables
 * 
 * Helper script to create settings_cache and settings_audit_log tables
 * Run this once to set up the required tables for the centralized settings module
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../config/config.php';

// Only allow Super Admin to run this
if (!isLoggedIn() || !hasRole(['Super Admin'])) {
    die('Access denied. Only Super Admin can run this script.');
}

echo "<h2>Creating Settings Tables</h2>";

// Create settings_cache table
echo "<p>Creating settings_cache table...</p>";
$createCacheTable = "
CREATE TABLE IF NOT EXISTS `settings_cache` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cache_key` VARCHAR(100) NOT NULL UNIQUE,
  `cache_value` LONGTEXT NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `cache_key` (`cache_key`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $stmt = executeQuery($createCacheTable);
    echo "<p style='color: green;'>✓ settings_cache table created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ settings_cache table may already exist: " . $e->getMessage() . "</p>";
}

// Create settings_audit_log table
echo "<p>Creating settings_audit_log table...</p>";
$createAuditTable = "
CREATE TABLE IF NOT EXISTS `settings_audit_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `changed_by` INT(11) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `setting_key` (`setting_key`),
  KEY `changed_by` (`changed_by`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `settings_audit_log_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $stmt = executeQuery($createAuditTable);
    echo "<p style='color: green;'>✓ settings_audit_log table created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ settings_audit_log table may already exist: " . $e->getMessage() . "</p>";
}

echo "<h3 style='color: green;'>Setup Complete!</h3>";
echo "<p><a href='" . APP_URL . "settings.php'>Go to Settings Page</a></p>";

