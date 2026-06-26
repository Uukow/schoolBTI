<?php
/**
 * AJAX: Get Support Ticket Statistics
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
    $isAdmin = in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin']);
    
    $whereClause = $isAdmin ? "WHERE 1=1" : "WHERE user_id = " . $currentUser['id'];
    
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
            SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed,
            SUM(CASE WHEN status = 'Reopened' THEN 1 ELSE 0 END) as reopened,
            SUM(CASE WHEN priority = 'Critical' THEN 1 ELSE 0 END) as critical,
            SUM(CASE WHEN priority = 'High' THEN 1 ELSE 0 END) as high,
            SUM(CASE WHEN priority = 'Medium' THEN 1 ELSE 0 END) as medium,
            SUM(CASE WHEN priority = 'Low' THEN 1 ELSE 0 END) as low
            FROM support_tickets $whereClause";
    
    $stmt = executeQuery($sql);
    $stats = fetchOne($stmt);
    
    jsonResponse(true, 'Statistics loaded', $stats);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load statistics: ' . $e->getMessage());
}

