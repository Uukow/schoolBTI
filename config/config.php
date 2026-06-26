<?php
/**
 * School ERP System - Configuration File
 * 
 * This file contains all configuration settings for the application
 * Uses environment-based configuration for seamless local/production deployment
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

// Prevent direct access
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

// Load Environment Detector first
$env = null;
$envConfig = [];
$isLocal = false;
$isProduction = true; // Default to production for security

if (file_exists(ABSPATH . 'includes/EnvironmentDetector.php')) {
    try {
        require_once ABSPATH . 'includes/EnvironmentDetector.php';
        if (class_exists('EnvironmentDetector')) {
            $env = EnvironmentDetector::getInstance();
            $envConfig = $env->getAll();
            $isLocal = $env->isLocal();
            $isProduction = $env->isProduction();
        }
    } catch (Exception $e) {
        // Log error but continue with defaults
        error_log("Config: Error loading EnvironmentDetector: " . $e->getMessage());
    } catch (Error $e) {
        // Catch fatal errors
        error_log("Config: Fatal error loading EnvironmentDetector: " . $e->getMessage());
    }
}

// Error Reporting (Environment-based)
$errorReporting = $envConfig['ERROR_REPORTING'] ?? ($isLocal ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
$displayErrors = $envConfig['DISPLAY_ERRORS'] ?? $isLocal;
error_reporting($errorReporting);
ini_set('display_errors', $displayErrors ? 1 : 0);
ini_set('log_errors', 1);

// Ensure logs directory exists
$logDir = ABSPATH . 'logs/';
if (!file_exists($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logFile = $envConfig['LOG_FILE'] ?? $logDir . 'error.log';
ini_set('error_log', $logFile);

// Set error handler for production
if (!$isLocal && file_exists(ABSPATH . 'config/error-handler.php')) {
    require_once ABSPATH . 'config/error-handler.php';
    set_error_handler('productionErrorHandler');
    register_shutdown_function('productionShutdownHandler');
}

// Session Configuration (Environment-based)
$sessionSecure = $envConfig['SESSION_SECURE'] ?? $isProduction;
$cookieSecure = $envConfig['COOKIE_SECURE'] ?? $isProduction;
ini_set('session.cookie_httponly', $envConfig['SESSION_HTTPONLY'] ?? 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $cookieSecure ? 1 : 0);
session_name('SCHOOLERP_SESSION');

// Force HTTPS in production if configured (only if not already in a redirect loop)
if ($isProduction && ($envConfig['FORCE_HTTPS'] ?? true)) {
    // Check if we're not already on HTTPS and not in a redirect loop
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    if (!$isHttps && !headers_sent()) {
        $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirectUrl", true, 301);
        exit;
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration (Environment-based)
define('DB_HOST', $envConfig['DB_HOST'] ?? 'localhost');
define('DB_USER', $envConfig['DB_USER'] ?? 'root');
define('DB_PASS', $envConfig['DB_PASS'] ?? '');
define('DB_NAME', $envConfig['DB_NAME'] ?? 'schoolbti');
define('DB_CHARSET', $envConfig['DB_CHARSET'] ?? 'utf8mb4');

// Application Configuration
define('APP_NAME', 'TacliinHub ERP System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', $env ? $env->getEnvironment() : 'production');
define('APP_URL', $env ? $env->getBaseUrl() : 'http://localhost/bti/');
define('BASE_URL', $env ? $env->getBaseUrl() : 'http://localhost/bti/');
define('ADMIN_EMAIL', 'info@uukowtech.com');
define('DEBUG_MODE', $envConfig['DEBUG_MODE'] ?? $isLocal);

// File Upload Configuration
define('UPLOAD_PATH', ABSPATH . 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Upload subdirectories
define('STUDENT_PHOTO_PATH', UPLOAD_PATH . 'students/photos/');
define('STUDENT_DOCS_PATH', UPLOAD_PATH . 'students/documents/');
define('STAFF_PHOTO_PATH', UPLOAD_PATH . 'staff/photos/');
define('STAFF_DOCS_PATH', UPLOAD_PATH . 'staff/documents/');
define('STAFF_CONTRACTS_PATH', UPLOAD_PATH . 'staff/contracts/');
define('RECRUITMENT_CV_PATH', UPLOAD_PATH . 'recruitment/cvs/');
define('RECRUITMENT_OFFER_PATH', UPLOAD_PATH . 'recruitment/offers/');
define('QUOTATION_VENDOR_PATH', UPLOAD_PATH . 'quotations/vendors/');
define('LIBRARY_PATH', UPLOAD_PATH . 'library/');
define('INVOICE_PATH', UPLOAD_PATH . 'invoices/');
define('RECEIPT_PATH', UPLOAD_PATH . 'receipts/');
define('REPORT_CARD_PATH', UPLOAD_PATH . 'report_cards/');
define('BACKUP_PATH', UPLOAD_PATH . 'backups/');

// Timezone
date_default_timezone_set('Africa/Mogadishu');

// Currency Settings
define('CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');

// Pagination
define('RECORDS_PER_PAGE', 25);

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('ACCOUNT_LOCKOUT_TIME', 1800); // 30 minutes in seconds

// Email Configuration (PHPMailer) - Environment-based
define('SMTP_HOST', $envConfig['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $envConfig['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $envConfig['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $envConfig['SMTP_PASSWORD'] ?? '');
define('SMTP_ENCRYPTION', $envConfig['SMTP_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_EMAIL', $envConfig['MAIL_FROM_EMAIL'] ?? 'noreply@uukowtech.com');
define('MAIL_FROM_NAME', $envConfig['MAIL_FROM_NAME'] ?? 'TacliinHub ERP System');

// SMS Configuration
define('SMS_GATEWAY', 'twilio'); // twilio, nexmo, etc.
define('SMS_API_KEY', '');
define('SMS_API_SECRET', '');
define('SMS_FROM_NUMBER', '');

// WhatsApp Configuration
define('WHATSAPP_API_KEY', '');
define('WHATSAPP_API_URL', '');

// Payment Gateway Configuration
define('PAYMENT_GATEWAY', 'stripe'); // stripe, paypal, etc.
define('PAYMENT_API_KEY', '');
define('PAYMENT_API_SECRET', '');

// Barcode/QR Code Settings
define('BARCODE_WIDTH', 200);
define('BARCODE_HEIGHT', 50);
define('QR_CODE_SIZE', 200);

// Date and Time Formats
define('DATE_FORMAT', 'd-m-Y');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'd-m-Y H:i:s');

// Auto-increment Prefixes
define('STUDENT_ID_PREFIX', 'STU');
define('STAFF_ID_PREFIX', 'STF');
define('INVOICE_PREFIX', 'INV');
define('RECEIPT_PREFIX', 'RCT');
define('APPLICATION_PREFIX', 'APP');
define('TICKET_PREFIX', 'TKT');

// Default Values
define('DEFAULT_LANGUAGE', 'en');
define('DEFAULT_BRANCH_ID', 1);

// System Installation Status
define('SYSTEM_INSTALLED', file_exists(ABSPATH . 'config/.installed'));

// Check if system is installed
if (!SYSTEM_INSTALLED && strpos($_SERVER['REQUEST_URI'] ?? '', 'setup.php') === false) {
    header('Location: ' . APP_URL . 'setup.php');
    exit;
}

// Load database connection
if (file_exists(ABSPATH . 'config/database.php')) {
    require_once ABSPATH . 'config/database.php';
}

// Load common functions
if (file_exists(ABSPATH . 'includes/functions.php')) {
    require_once ABSPATH . 'includes/functions.php';
}

// Load dashboard functions
if (file_exists(ABSPATH . 'includes/dashboard-functions.php')) {
    require_once ABSPATH . 'includes/dashboard-functions.php';
}

// Load dashboard helpers
if (file_exists(ABSPATH . 'includes/dashboard-helpers.php')) {
    require_once ABSPATH . 'includes/dashboard-helpers.php';
}

// Load laboratory helpers
if (file_exists(ABSPATH . 'includes/lab-functions.php')) {
    require_once ABSPATH . 'includes/lab-functions.php';
}

// Load language support
if (file_exists(ABSPATH . 'includes/language.php')) {
    require_once ABSPATH . 'includes/language.php';
}

// Fallback translation function if language.php didn't define it
if (!function_exists('__')) {
    function __($key) {
        return $key;
    }
}

// Load authentication
if (file_exists(ABSPATH . 'includes/auth.php')) {
    require_once ABSPATH . 'includes/auth.php';
}

if (file_exists(ABSPATH . 'includes/hr-permission.php')) {
    require_once ABSPATH . 'includes/hr-permission.php';
}

// Load Settings Manager
if (file_exists(ABSPATH . 'includes/SettingsManager.php')) {
    require_once ABSPATH . 'includes/SettingsManager.php';
}

// Load Permission Manager
if (file_exists(ABSPATH . 'includes/PermissionManager.php')) {
    require_once ABSPATH . 'includes/PermissionManager.php';
}

// Load Permission Helpers
if (file_exists(ABSPATH . 'includes/permission-helpers.php')) {
    require_once ABSPATH . 'includes/permission-helpers.php';
}

// Load HR Services
if (file_exists(ABSPATH . 'includes/services/hr/HrServiceLoader.php')) {
    require_once ABSPATH . 'includes/services/hr/HrServiceLoader.php';
}

// Initialize database connection (only if system is installed)
// Use lazy connection - don't fail if DB is temporarily unavailable
if (SYSTEM_INSTALLED && function_exists('getDBConnection')) {
    try {
        $conn = @getDBConnection();
        // Connection will be retried when actually needed
        if ($conn === false) {
            error_log("Config: Database connection failed during init - will retry when needed");
        }
    } catch (Exception $e) {
        error_log("Config: Database connection error during init: " . $e->getMessage());
        // Don't fail config load - connection will be retried when needed
    } catch (Error $e) {
        error_log("Config: Fatal database connection error: " . $e->getMessage());
    }
}


