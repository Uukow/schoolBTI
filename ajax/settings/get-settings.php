<?php
/**
 * AJAX: Get General Settings
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

// Check if user has required role
if (!$currentUser || !in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin'])) {
    error_log("Settings access denied for user: " . ($currentUser['username'] ?? 'unknown') . " with role: " . ($currentUser['role_name'] ?? 'unknown'));
    jsonResponse(false, 'Permission denied. Only Super Admin and Admin can access settings.');
}

try {
    $sql = "SELECT * FROM system_settings LIMIT 1";
    $stmt = executeQuery($sql);
    $settings = fetchOne($stmt);
    
    if (!$settings) {
        // Return default settings
        $settings = [
            'id' => 0,
            'school_name' => 'School Name',
            'school_name_somali' => null,
            'school_logo' => null,
            'school_email' => null,
            'school_phone' => null,
            'school_address' => null,
            'currency' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'Africa/Mogadishu',
            'language' => 'en',
            'date_format' => 'd-m-Y',
        ];
    }
    
    jsonResponse(true, 'Settings loaded', $settings);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load settings: ' . $e->getMessage());
}

