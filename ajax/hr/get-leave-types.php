<?php
/**
 * AJAX: Get Leave Types
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
    $sql = "SELECT * FROM leave_types ORDER BY leave_name";
    $stmt = executeQuery($sql);
    $types = fetchAll($stmt);
    
    $formatted = [];
    foreach ($types as $type) {
        $formatted[] = [
            'id' => $type['id'],
            'leave_name' => $type['leave_name'],
            'leave_code' => $type['leave_code'],
            'days_allowed' => $type['days_allowed'],
        ];
    }
    
    jsonResponse(true, 'Leave types loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load leave types: ' . $e->getMessage());
}

