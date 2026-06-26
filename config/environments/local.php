<?php
/**
 * Local Development Environment Configuration
 * 
 * Configuration for localhost/development environment
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

return [
    // Database Configuration
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'schoolbti',
    'DB_CHARSET' => 'utf8mb4',
    
    // Application URLs
    'APP_URL' => 'http://localhost/bti/',
    'BASE_URL' => 'http://localhost/bti/',
    
    // Debug & Error Reporting
    'DEBUG_MODE' => true,
    'DISPLAY_ERRORS' => true,
    'ERROR_REPORTING' => E_ALL,
    
    // Security Settings
    'SESSION_SECURE' => false, // HTTP only for local
    'SESSION_HTTPONLY' => true,
    'COOKIE_SECURE' => false,
    'FORCE_HTTPS' => false,
    
    // Email Configuration
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => 587,
    'SMTP_USERNAME' => 'uukowtech@gmail.com',
    'SMTP_PASSWORD' => 'dkgiqxztpnnjmsru',
    'SMTP_ENCRYPTION' => 'tls',
    'MAIL_FROM_EMAIL' => 'uukowtech@gmail.com',
    'MAIL_FROM_NAME' => 'TacliinHub ERP (Local)',
    
    // API Configuration
    'API_ENABLED' => true,
    'API_DEBUG' => true,
    'CORS_ALLOWED_ORIGINS' => ['http://localhost', 'http://localhost:3000', 'http://127.0.0.1'],
    
    // Cache Settings
    'CACHE_ENABLED' => false, // Disable cache in development
    'CACHE_TTL' => 0,
    
    // Logging
    'LOG_LEVEL' => 'DEBUG',
    'LOG_FILE' => dirname(dirname(__DIR__)) . '/logs/local.log',
    
    // Performance
    'OPCACHE_ENABLED' => false,
    'QUERY_CACHE' => false,
];

