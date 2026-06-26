<?php
/**
 * AJAX: Execute Custom Report
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
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? null;

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
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $reportId = $data['report_id'] ?? null;
    
    if (!$reportId) {
        jsonResponse(false, 'Report ID is required');
    }
    
    // For now, return empty result as custom reports need to be implemented
    // In a full implementation, this would execute the SQL query from custom_reports table
    $formatted = [
        'message' => 'Custom report execution not yet implemented',
    ];
    
    jsonResponse(true, 'Custom report executed', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to execute custom report: ' . $e->getMessage());
}

