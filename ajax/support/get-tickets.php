<?php
/**
 * AJAX: Get Support Tickets
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
    $status = $_GET['status'] ?? null;
    $priority = $_GET['priority'] ?? null;
    $category = $_GET['category'] ?? null;
    $assignedTo = $_GET['assigned_to'] ?? null;
    
    $sql = "SELECT t.*, 
            u.username as created_by_name, u.email as created_by_email,
            a.username as assigned_to_name, a.email as assigned_to_email,
            (SELECT COUNT(*) FROM ticket_replies WHERE ticket_id = t.id) as reply_count
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_to = a.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Show only own tickets for non-admin users
    if (!in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin'])) {
        $sql .= " AND t.user_id = ?";
        $params[] = $currentUser['id'];
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if ($priority) {
        $sql .= " AND t.priority = ?";
        $params[] = $priority;
        $types .= 's';
    }
    
    if ($category) {
        $sql .= " AND t.category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    if ($assignedTo) {
        $sql .= " AND t.assigned_to = ?";
        $params[] = $assignedTo;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY t.created_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $tickets = fetchAll($stmt) ?: [];
    
    jsonResponse(true, 'Tickets loaded', $tickets);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load tickets: ' . $e->getMessage());
}

