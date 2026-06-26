<?php
/**
 * Environment Detector Class
 * 
 * Automatically detects and manages environment configuration (local vs production)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class EnvironmentDetector {
    private static $instance = null;
    private $environment = null;
    private $isLocal = null;
    private $isProduction = null;
    private $baseUrl = null;
    private $config = [];
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->detectEnvironment();
        $this->loadConfiguration();
    }
    
    /**
     * Get singleton instance
     * 
     * @return EnvironmentDetector
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Detect current environment
     */
    private function detectEnvironment() {
        // Method 1: Check for explicit environment variable
        if (defined('APP_ENV')) {
            $this->environment = strtolower(APP_ENV);
            $this->isLocal = ($this->environment === 'local' || $this->environment === 'development');
            $this->isProduction = ($this->environment === 'production' || $this->environment === 'live');
            return;
        }
        
        // Method 2: Check for .env file with APP_ENV
        $envFile = ABSPATH . '.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            if (preg_match('/APP_ENV\s*=\s*(\w+)/i', $envContent, $matches)) {
                $this->environment = strtolower(trim($matches[1]));
                $this->isLocal = ($this->environment === 'local' || $this->environment === 'development');
                $this->isProduction = ($this->environment === 'production' || $this->environment === 'live');
                return;
            }
        }
        
        // Method 3: Auto-detect based on server name and host
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $host = strtolower($host);
        
        // Check if it's localhost or local development
        if (
            $host === 'localhost' || 
            $host === '127.0.0.1' ||
            $host === '::1' ||
            strpos($host, '.local') !== false ||
            strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false ||
            (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1']))
        ) {
            $this->environment = 'local';
            $this->isLocal = true;
            $this->isProduction = false;
            return;
        }
        
        // Check for production domain
        if (strpos($host, 'tacliinhub.uukowtech.com') !== false) {
            $this->environment = 'production';
            $this->isLocal = false;
            $this->isProduction = true;
            return;
        }
        
        // Default to production for safety
        $this->environment = 'production';
        $this->isLocal = false;
        $this->isProduction = true;
    }
    
    /**
     * Load environment-specific configuration
     */
    private function loadConfiguration() {
        $configFile = ABSPATH . 'config/environments/' . $this->environment . '.php';
        
        // Load environment-specific config if exists
        if (file_exists($configFile)) {
            try {
                $this->config = require $configFile;
                // Ensure it's an array
                if (!is_array($this->config)) {
                    $this->config = $this->getDefaultConfig();
                }
            } catch (Exception $e) {
                // If config file has errors, use defaults
                error_log("EnvironmentDetector: Error loading config file: " . $e->getMessage());
                $this->config = $this->getDefaultConfig();
            } catch (Error $e) {
                // Catch fatal errors too
                error_log("EnvironmentDetector: Fatal error loading config file: " . $e->getMessage());
                $this->config = $this->getDefaultConfig();
            }
        } else {
            // Fallback to default config structure
            $this->config = $this->getDefaultConfig();
        }
        
        // Override with .env file values if exists
        $this->loadEnvFile();
        
        // Auto-detect base URL
        $this->detectBaseUrl();
    }
    
    /**
     * Load configuration from .env file
     */
    private function loadEnvFile() {
        $envFile = ABSPATH . '.env';
        if (!file_exists($envFile)) {
            return;
        }
        
        try {
            $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                return;
            }
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse KEY=VALUE pairs
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    
                    // Override config if key exists
                    if (isset($this->config[$key])) {
                        $this->config[$key] = $value;
                    }
                }
            }
        } catch (Exception $e) {
            // Silently fail if .env file can't be read
            error_log("EnvironmentDetector: Error reading .env file: " . $e->getMessage());
        }
    }
    
    /**
     * Auto-detect base URL
     */
    private function detectBaseUrl() {
        // Check if explicitly set in config
        if (isset($this->config['APP_URL']) && !empty($this->config['APP_URL'])) {
            $this->baseUrl = rtrim($this->config['APP_URL'], '/') . '/';
            return;
        }
        
        // Auto-detect from server variables
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $path = dirname($scriptName);
        
        // Remove /bti if present in path (for local development)
        if (strpos($path, '/bti') !== false) {
            $path = str_replace('/bti', '', $path);
        }
        
        // For production, use root path
        if ($this->isProduction) {
            $this->baseUrl = $protocol . $host . '/';
        } else {
            // For local, include /bti if it exists in the path
            $fullPath = $protocol . $host . $path;
            if (strpos($_SERVER['REQUEST_URI'] ?? '', '/bti') !== false) {
                $this->baseUrl = $protocol . $host . '/bti/';
            } else {
                $this->baseUrl = rtrim($fullPath, '/') . '/';
            }
        }
    }
    
    /**
     * Get default configuration structure
     */
    private function getDefaultConfig() {
        return [
            'DB_HOST' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_NAME' => 'schoolerp_db',
            'DB_CHARSET' => 'utf8mb4',
            'APP_URL' => '',
            'DEBUG_MODE' => $this->isLocal,
            'DISPLAY_ERRORS' => $this->isLocal,
            'SESSION_SECURE' => !$this->isLocal,
            'SMTP_HOST' => 'smtp.gmail.com',
            'SMTP_PORT' => 587,
            'SMTP_USERNAME' => '',
            'SMTP_PASSWORD' => '',
            'SMTP_ENCRYPTION' => 'tls',
        ];
    }
    
    /**
     * Get current environment
     * 
     * @return string Environment name (local, production, etc.)
     */
    public function getEnvironment() {
        return $this->environment;
    }
    
    /**
     * Check if running in local environment
     * 
     * @return bool
     */
    public function isLocal() {
        return $this->isLocal === true;
    }
    
    /**
     * Check if running in production environment
     * 
     * @return bool
     */
    public function isProduction() {
        return $this->isProduction === true;
    }
    
    /**
     * Get base URL
     * 
     * @return string Base URL
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if not found
     * @return mixed Configuration value
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Get all configuration
     * 
     * @return array All configuration values
     */
    public function getAll() {
        return $this->config;
    }
    
    /**
     * Set configuration value (runtime only)
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
    }
}


