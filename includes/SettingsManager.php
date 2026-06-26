<?php
/**
 * Settings Manager Class
 * 
 * Centralized settings management with caching, encryption, and validation
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class SettingsManager {
    private static $instance = null;
    private $settings = [];
    private $cacheEnabled = true;
    private $cacheTTL = 3600; // 1 hour
    private $encryptionKey = null;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->loadEncryptionKey();
        $this->loadSettings();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load encryption key from config or generate one
     */
    private function loadEncryptionKey() {
        // Priority 1: Check for explicit encryption key constant
        if (defined('SETTINGS_ENCRYPTION_KEY') && !empty(constant('SETTINGS_ENCRYPTION_KEY'))) {
            $this->encryptionKey = constant('SETTINGS_ENCRYPTION_KEY');
            return;
        }
        
        // Priority 2: Check environment detector for encryption key
        if (class_exists('EnvironmentDetector')) {
            $env = EnvironmentDetector::getInstance();
            $envKey = $env->get('SETTINGS_ENCRYPTION_KEY');
            if (!empty($envKey)) {
                $this->encryptionKey = $envKey;
                return;
            }
        }
        
        // Priority 3: Generate environment-specific key
        // Use different keys for local vs production for security
        $isLocal = defined('APP_ENV') && (APP_ENV === 'local' || APP_ENV === 'development');
        $keySuffix = $isLocal ? 'local_dev_2025' : 'production_prod_2025';
        $this->encryptionKey = hash('sha256', DB_PASS . APP_NAME . $keySuffix . (defined('DB_HOST') ? DB_HOST : 'default'));
    }
    
    /**
     * Load all settings from database with caching
     */
    private function loadSettings() {
        // Try to load from cache first (only if cache table exists)
        if ($this->cacheEnabled && $this->cacheTableExists()) {
            $cached = $this->getFromCache('all_settings');
            if ($cached !== false) {
                $this->settings = $cached;
                return;
            }
        }
        
        // Load from database
        try {
            // Check if database connection is available
            if (!function_exists('executeQuery') || !function_exists('getDBConnection')) {
                $this->settings = $this->getDefaults();
                return;
            }
            
            $conn = getDBConnection();
            if ($conn === false) {
                // Database not available, use defaults
                $this->settings = $this->getDefaults();
                return;
            }
            
            $sql = "SELECT * FROM system_settings LIMIT 1";
            $stmt = executeQuery($sql);
            
            if ($stmt === false) {
                // Query failed, use defaults
                $this->settings = $this->getDefaults();
                return;
            }
            
            $dbSettings = fetchOne($stmt);
            
            if ($dbSettings) {
                // Decrypt sensitive fields
                $sensitiveFields = [
                    'smtp_password', 'sms_api_key', 'whatsapp_api_key', 
                    'payment_api_key', 'payment_api_secret', 'api_key', 'license_key'
                ];
                
                foreach ($sensitiveFields as $field) {
                    if (isset($dbSettings[$field]) && !empty($dbSettings[$field])) {
                        $dbSettings[$field] = $this->decrypt($dbSettings[$field]);
                    }
                }
                
                $this->settings = $dbSettings;
                
                // Cache the settings (only if cache table exists)
                if ($this->cacheEnabled && $this->cacheTableExists()) {
                    $this->setCache('all_settings', $this->settings, $this->cacheTTL);
                }
            } else {
                // Load defaults
                $this->settings = $this->getDefaults();
            }
        } catch (Exception $e) {
            // If system_settings table doesn't exist or error, use defaults
            $this->settings = $this->getDefaults();
        }
    }
    
    /**
     * Get setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public function get($key, $default = null) {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        
        // Check defaults
        $defaults = $this->getDefaults();
        if (isset($defaults[$key])) {
            return $defaults[$key];
        }
        
        return $default;
    }
    
    /**
     * Set setting value (runtime, not persisted)
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     */
    public function set($key, $value) {
        $this->settings[$key] = $value;
        
        // Update cache
        if ($this->cacheEnabled) {
            $this->setCache('all_settings', $this->settings, $this->cacheTTL);
        }
    }
    
    /**
     * Save setting to database
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param int $userId User ID making the change
     * @return bool Success status
     */
    public function save($key, $value, $userId = null) {
        // Validate setting
        if (!$this->validate($key, $value)) {
            return false;
        }
        
        // Get old value for audit log
        $oldValue = $this->get($key);
        
        // Encrypt sensitive fields
        $sensitiveFields = [
            'smtp_password', 'sms_api_key', 'whatsapp_api_key', 
            'payment_api_key', 'payment_api_secret', 'api_key', 'license_key'
        ];
        
        $dbValue = $value;
        if (in_array($key, $sensitiveFields) && !empty($value)) {
            $dbValue = $this->encrypt($value);
        }
        
        // Get current settings record
        $sql = "SELECT id FROM system_settings LIMIT 1";
        $stmt = executeQuery($sql);
        $settings = fetchOne($stmt);
        
        if ($settings) {
            // Check if column exists
            try {
                $checkSql = "SHOW COLUMNS FROM system_settings LIKE ?";
                $checkStmt = executeQuery($checkSql, 's', [$key]);
                $columnExists = fetchOne($checkStmt);
                
                if ($columnExists) {
                    // Update existing record
                    $updateSql = "UPDATE system_settings SET `{$key}` = ?, `updated_at` = NOW(), `updated_by` = ? WHERE id = ?";
                    $updateStmt = executeQuery($updateSql, 'sii', [$dbValue, $userId, $settings['id']]);
                    
                    if ($updateStmt) {
                        // Update local cache
                        $this->settings[$key] = $value;
                        
                        // Log audit
                        $this->logAudit($key, $oldValue, $value, $userId);
                        
                        // Clear cache
                        $this->clearCache('all_settings');
                        
                        return true;
                    }
                } else {
                    // Column doesn't exist - return false so caller knows it failed
                    // The actual database column will need to be added via migration
                    $this->settings[$key] = $value; // Update cache anyway
                    return false; // Return false to indicate DB save failed
                }
            } catch (Exception $e) {
                // Error checking column - try to update anyway
                try {
                    $updateSql = "UPDATE system_settings SET `{$key}` = ?, `updated_at` = NOW(), `updated_by` = ? WHERE id = ?";
                    $updateStmt = executeQuery($updateSql, 'sii', [$dbValue, $userId, $settings['id']]);
                    
                    if ($updateStmt) {
                        $this->settings[$key] = $value;
                        $this->logAudit($key, $oldValue, $value, $userId);
                        $this->clearCache('all_settings');
                        return true;
                    }
                } catch (Exception $e2) {
                    // Column likely doesn't exist - return false
                    $this->settings[$key] = $value; // Update cache anyway
                    return false; // Return false to indicate DB save failed
                }
            }
        } else {
            // Create new record with this setting
            try {
                $insertSql = "INSERT INTO system_settings (`{$key}`, `updated_by`) VALUES (?, ?)";
                $insertStmt = executeQuery($insertSql, 'si', [$dbValue, $userId]);
                
                if ($insertStmt) {
                    $this->settings[$key] = $value;
                    $this->logAudit($key, null, $value, $userId);
                    $this->clearCache('all_settings');
                    return true;
                }
            } catch (Exception $e) {
                // Column might not exist - return false
                $this->settings[$key] = $value; // Update cache anyway
                return false; // Return false to indicate DB save failed
            }
        }
        
        return false;
    }
    
    /**
     * Save multiple settings at once
     * 
     * @param array $settings Array of key-value pairs
     * @param int $userId User ID making the change
     * @return array Results for each setting
     */
    public function saveMultiple($settings, $userId = null) {
        $results = [];
        
        foreach ($settings as $key => $value) {
            $results[$key] = $this->save($key, $value, $userId);
        }
        
        return $results;
    }
    
    /**
     * Get all settings
     * 
     * @return array All settings
     */
    public function getAll() {
        return $this->settings;
    }
    
    /**
     * Get default values for all settings
     * 
     * @return array Default settings
     */
    private function getDefaults() {
        return [
            // System Identity
            'school_name' => 'TacliinHub ERP System',
            'system_short_name' => 'TacliinHub',
            'system_logo' => null,
            'system_favicon' => null,
            'developer_name' => 'Uukow Technology Solutions (UTech)',
            'license_text' => null,
            
            // Academic
            'grading_system' => 'Percentage',
            'gpa_scale' => 4.00,
            'attendance_threshold' => 75,
            'class_graduation_enabled' => 1,
            
            // Financial
            'currency' => 'USD',
            'currency_symbol' => '$',
            'tuition_fee_behavior' => 'Monthly',
            'discount_enabled' => 1,
            'penalty_enabled' => 1,
            'penalty_rate' => 0.00,
            'payroll_enabled' => 1,
            'tax_enabled' => 0,
            'tax_rate' => 0.00,
            
            // UI/UX
            'timezone' => 'Africa/Mogadishu',
            'language' => 'en',
            'date_format' => 'd-m-Y',
            'time_format' => 'H:i:s',
            'datetime_format' => 'd-m-Y H:i:s',
            'theme' => 'default',
            'pagination_limit' => 25,
            'records_per_page' => 25,
            
            // Security
            'session_timeout' => 3600,
            'password_min_length' => 8,
            'password_require_uppercase' => 0,
            'password_require_lowercase' => 1,
            'password_require_number' => 1,
            'password_require_special' => 0,
            'max_login_attempts' => 5,
            'account_lockout_time' => 1800,
            'two_factor_enabled' => 0,
            'audit_logging_enabled' => 1,
            
            // Communication
            'email_enabled' => 1,
            'sms_enabled' => 0,
            'whatsapp_enabled' => 0,
            'notification_enabled' => 1,
            'notification_email' => 1,
            'notification_sms' => 0,
            'notification_whatsapp' => 0,
            
            // Integration
            'api_enabled' => 0,
            'webhook_enabled' => 0,
            'license_verification_enabled' => 0,
            
            // Features
            'feature_lms' => 1,
            'feature_library' => 1,
            'feature_transport' => 1,
            'feature_hostel' => 0,
            'feature_certificates' => 1,
            'feature_events' => 1,
        ];
    }
    
    /**
     * Validate setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Validation result
     */
    private function validate($key, $value) {
        $validators = [
            'school_name' => function($v) { return !empty($v) && strlen($v) <= 255; },
            'school_email' => function($v) { return empty($v) || filter_var($v, FILTER_VALIDATE_EMAIL); },
            'currency' => function($v) { return in_array($v, ['USD', 'EUR', 'GBP', 'SOS', 'ETB', 'KES']); },
            'gpa_scale' => function($v) { return is_numeric($v) && $v >= 0 && $v <= 10; },
            'attendance_threshold' => function($v) { return is_numeric($v) && $v >= 0 && $v <= 100; },
            'session_timeout' => function($v) { return is_numeric($v) && $v >= 300 && $v <= 86400; },
            'password_min_length' => function($v) { return is_numeric($v) && $v >= 6 && $v <= 32; },
            'max_login_attempts' => function($v) { return is_numeric($v) && $v >= 3 && $v <= 10; },
            'pagination_limit' => function($v) { return is_numeric($v) && $v >= 10 && $v <= 100; },
        ];
        
        if (isset($validators[$key])) {
            return $validators[$key]($value);
        }
        
        return true; // Default: accept all
    }
    
    /**
     * Encrypt sensitive value
     * 
     * @param string $value Value to encrypt
     * @return string Encrypted value
     */
    private function encrypt($value) {
        if (empty($value)) {
            return $value;
        }
        
        $method = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encrypted = openssl_encrypt($value, $method, $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive value
     * 
     * @param string $value Encrypted value
     * @return string Decrypted value
     */
    private function decrypt($value) {
        if (empty($value)) {
            return $value;
        }
        
        try {
            $method = 'AES-256-CBC';
            $data = base64_decode($value);
            $ivLength = openssl_cipher_iv_length($method);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            return openssl_decrypt($encrypted, $method, $this->encryptionKey, 0, $iv);
        } catch (Exception $e) {
            // If decryption fails, return original (might be plain text from old system)
            return $value;
        }
    }
    
    /**
     * Log audit trail
     * 
     * @param string $key Setting key
     * @param mixed $oldValue Old value
     * @param mixed $newValue New value
     * @param int $userId User ID
     */
    private function logAudit($key, $oldValue, $newValue, $userId) {
        if (!$this->get('audit_logging_enabled', true)) {
            return;
        }
        
        if (!$this->auditLogTableExists()) {
            return; // Silently fail if audit log table doesn't exist
        }
        
        try {
            // Don't log sensitive values
            $sensitiveFields = [
                'smtp_password', 'sms_api_key', 'whatsapp_api_key', 
                'payment_api_key', 'payment_api_secret', 'api_key', 'license_key'
            ];
            
            if (in_array($key, $sensitiveFields)) {
                $oldValue = '***ENCRYPTED***';
                $newValue = '***ENCRYPTED***';
            }
            
            $sql = "INSERT INTO settings_audit_log (setting_key, old_value, new_value, changed_by, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $ip = getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            executeQuery($sql, 'sssiss', [
                $key,
                is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                is_array($newValue) ? json_encode($newValue) : $newValue,
                $userId,
                $ip,
                $userAgent
            ]);
        } catch (Exception $e) {
            // Silently fail if audit logging fails
        }
    }
    
    /**
     * Check if cache table exists
     * 
     * @return bool True if table exists
     */
    private function cacheTableExists() {
        try {
            $sql = "SHOW TABLES LIKE 'settings_cache'";
            $stmt = executeQuery($sql);
            $result = fetchOne($stmt);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if audit log table exists
     * 
     * @return bool True if table exists
     */
    private function auditLogTableExists() {
        try {
            $sql = "SHOW TABLES LIKE 'settings_audit_log'";
            $stmt = executeQuery($sql);
            $result = fetchOne($stmt);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get from cache
     * 
     * @param string $key Cache key
     * @return mixed|false Cached value or false
     */
    private function getFromCache($key) {
        if (!$this->cacheTableExists()) {
            return false;
        }
        
        try {
            $sql = "SELECT cache_value FROM settings_cache 
                    WHERE cache_key = ? AND expires_at > NOW() LIMIT 1";
            $stmt = executeQuery($sql, 's', [$key]);
            $result = fetchOne($stmt);
            
            if ($result) {
                return json_decode($result['cache_value'], true);
            }
        } catch (Exception $e) {
            // Cache table doesn't exist or error occurred, return false
            return false;
        }
        
        return false;
    }
    
    /**
     * Set cache
     * 
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param int $ttl Time to live in seconds
     */
    private function setCache($key, $value, $ttl) {
        if (!$this->cacheTableExists()) {
            return; // Silently fail if cache table doesn't exist
        }
        
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
            $cacheValue = json_encode($value);
            
            $sql = "INSERT INTO settings_cache (cache_key, cache_value, expires_at)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE cache_value = ?, expires_at = ?";
            
            executeQuery($sql, 'sssss', [$key, $cacheValue, $expiresAt, $cacheValue, $expiresAt]);
        } catch (Exception $e) {
            // Silently fail if cache operation fails
        }
    }
    
    /**
     * Clear cache
     * 
     * @param string $key Cache key (optional, clears all if null)
     */
    public function clearCache($key = null) {
        if (!$this->cacheTableExists()) {
            return; // Silently fail if cache table doesn't exist
        }
        
        try {
            if ($key) {
                $sql = "DELETE FROM settings_cache WHERE cache_key = ?";
                executeQuery($sql, 's', [$key]);
            } else {
                $sql = "DELETE FROM settings_cache";
                executeQuery($sql);
            }
        } catch (Exception $e) {
            // Silently fail if cache operation fails
        }
    }
    
    /**
     * Get audit log
     * 
     * @param int $limit Number of records
     * @param string $key Filter by setting key (optional)
     * @return array Audit log entries
     */
    public function getAuditLog($limit = 50, $key = null) {
        if (!$this->auditLogTableExists()) {
            return []; // Return empty array if table doesn't exist
        }
        
        try {
            if ($key) {
                $sql = "SELECT sal.*, u.username 
                        FROM settings_audit_log sal
                        LEFT JOIN users u ON sal.changed_by = u.id
                        WHERE sal.setting_key = ?
                        ORDER BY sal.created_at DESC
                        LIMIT ?";
                $stmt = executeQuery($sql, 'si', [$key, $limit]);
            } else {
                $sql = "SELECT sal.*, u.username 
                        FROM settings_audit_log sal
                        LEFT JOIN users u ON sal.changed_by = u.id
                        ORDER BY sal.created_at DESC
                        LIMIT ?";
                $stmt = executeQuery($sql, 'i', [$limit]);
            }
            
            return fetchAll($stmt) ?: [];
        } catch (Exception $e) {
            return []; // Return empty array on error
        }
    }
    
    /**
     * Reload settings from database
     */
    public function reload() {
        $this->clearCache('all_settings');
        $this->loadSettings();
    }
}

// Helper function for easy access
function getSetting($key, $default = null) {
    return SettingsManager::getInstance()->get($key, $default);
}

function setSetting($key, $value, $userId = null) {
    return SettingsManager::getInstance()->save($key, $value, $userId);
}

