<?php
/**
 * AJAX: Get System Information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    // Get database version
    $dbVersion = 'Unknown';
    try {
        $versionStmt = executeQuery("SELECT VERSION() as version");
        $versionData = fetchOne($versionStmt);
        $dbVersion = $versionData['version'] ?? 'Unknown';
    } catch (Exception $e) {
        // Ignore
    }
    
    $info = [
        'app_name' => APP_NAME,
        'app_version' => APP_VERSION,
        'php_version' => PHP_VERSION,
        'database_version' => $dbVersion,
        'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'license_key' => null,
        'license_type' => null,
        'license_expiry' => null,
    ];
    
    jsonResponse(true, 'System info loaded', $info);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load system info: ' . $e->getMessage());
}

