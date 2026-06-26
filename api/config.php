<?php
/**
 * API Configuration
 * 
 * Sets up the environment for API requests (headers, auth checks, etc.)
 */

// Prevent HTML error output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set JSON header immediately
header('Content-Type: application/json');

// Define API constant
define('IS_API', true);

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle Preflight options
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include main config with Output Buffering to output noise
ob_start();
$rootPath = dirname(__DIR__);
if (file_exists($rootPath . '/config/config.php')) {
    require_once $rootPath . '/config/config.php';
}
ob_end_clean(); // Discard any output from config.php (whitespace, warnings, etc.)

// Re-enforce error suppression for API
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Ensure headers are correct after config load (in case config managed headers)
if (!headers_sent()) {
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
}

if (!function_exists('sendApiResponse')) {
    /**
     * Standard API Response Helper
     */
    function sendApiResponse($success, $message, $data = null, $statusCode = 200) {
        // Clear any previous buffer just in case
        if (ob_get_length()) ob_clean();
        
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
