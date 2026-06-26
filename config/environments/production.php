<?php
/**
 * Production Environment Configuration
 * 
 * Configuration for production environment (tacliinhub.uukowtech.com)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

return [
    // Database Configuration
    'DB_HOST' => 'localhost', // Usually localhost on same server
    'DB_USER' => 'schoolerp_user', // Change to production DB user
    'DB_PASS' => '', // Set via .env file
    'DB_NAME' => 'schoolerp_db', // Production database name
    'DB_CHARSET' => 'utf8mb4',
    
    // Application URLs
    'APP_URL' => 'https://tacliinhub.uukowtech.com/',
    'BASE_URL' => 'https://tacliinhub.uukowtech.com/',
    
    // Debug & Error Reporting
    'DEBUG_MODE' => false,
    'DISPLAY_ERRORS' => false,
    'ERROR_REPORTING' => E_ALL & ~E_DEPRECATED & ~E_STRICT,
    
    // Security Settings
    'SESSION_SECURE' => true, // HTTPS required
    'SESSION_HTTPONLY' => true,
    'COOKIE_SECURE' => true,
    'FORCE_HTTPS' => true,
    
    // Email Configuration
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => 587,
    'SMTP_USERNAME' => '', // Set via .env file
    'SMTP_PASSWORD' => '', // Set via .env file
    'SMTP_ENCRYPTION' => 'tls',
    'MAIL_FROM_EMAIL' => 'noreply@uukowtech.com',
    'MAIL_FROM_NAME' => 'TacliinHub ERP System',
    
    // API Configuration
    'API_ENABLED' => true,
    'API_DEBUG' => false,
    'CORS_ALLOWED_ORIGINS' => ['https://tacliinhub.uukowtech.com'],
    
    // Cache Settings
    'CACHE_ENABLED' => true, // Enable cache in production
    'CACHE_TTL' => 3600, // 1 hour
    
    // Logging
    'LOG_LEVEL' => 'ERROR',
    'LOG_FILE' => dirname(dirname(__DIR__)) . '/logs/production.log',
    
    // Performance
    'OPCACHE_ENABLED' => true,
    'QUERY_CACHE' => true,
];

