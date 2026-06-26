<?php
/**
 * AJAX: Get Single Support Ticket
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
    $ticketId = $_GET['ticket_id'] ?? $_POST['ticket_id'] ?? null;
    
    if (!$ticketId) {
        jsonResponse(false, 'Ticket ID is required');
    }
    
    $sql = "SELECT t.*, 
            u.username as created_by_name, u.email as created_by_email,
            a.username as assigned_to_name, a.email as assigned_to_email
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_to = a.id
            WHERE t.id = ?";
    
    $stmt = executeQuery($sql, 'i', [$ticketId]);
    $ticket = fetchOne($stmt);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Check access permission - only owner or admin can view
    if (!in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin']) && 
        $ticket['user_id'] != $currentUser['id']) {
        jsonResponse(false, 'Permission denied. You do not have access to this ticket.');
    }
    
    // Get ticket replies
    $repliesSql = "SELECT tr.*, u.username, u.email, r.role_name
                   FROM ticket_replies tr
                   LEFT JOIN users u ON tr.user_id = u.id
                   LEFT JOIN roles r ON u.role_id = r.id
                   WHERE tr.ticket_id = ?
                   ORDER BY tr.created_at ASC";
    $repliesStmt = executeQuery($repliesSql, 'i', [$ticketId]);
    $replies = fetchAll($repliesStmt) ?: [];
    
    $ticket['replies'] = $replies;
    
    jsonResponse(true, 'Ticket loaded', $ticket);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load ticket: ' . $e->getMessage());
}

