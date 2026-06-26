<?php
/**
 * AJAX: Update Support Ticket Status
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

try {
    // Get JSON input first
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    // Get user_id from various sources
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? null;
    
    $currentUser = null;
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
    
    $ticketId = $input['ticket_id'] ?? null;
    $status = $input['status'] ?? null;
    $resolution = sanitize($input['resolution'] ?? null);
    $assignedTo = $input['assigned_to'] ?? null;
    
    if (!$ticketId || !$status) {
        jsonResponse(false, 'Ticket ID and status are required');
    }
    
    // Validate status
    $validStatuses = ['Open', 'In Progress', 'Resolved', 'Closed', 'Reopened'];
    if (!in_array($status, $validStatuses)) {
        jsonResponse(false, 'Invalid status');
    }
    
    // Verify ticket exists
    $ticketSql = "SELECT * FROM support_tickets WHERE id = ?";
    $ticketStmt = executeQuery($ticketSql, 'i', [$ticketId]);
    $ticket = fetchOne($ticketStmt);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Check permission - only admin can change status/assignment, owner can reopen closed tickets
    $isAdmin = in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin']);
    $isOwner = $ticket['user_id'] == $currentUser['id'];
    
    if (!$isAdmin && !($isOwner && $status == 'Reopened' && in_array($ticket['status'], ['Closed', 'Resolved']))) {
        jsonResponse(false, 'Permission denied. Only admins can update ticket status.');
    }
    
    // Build update query
    $updateFields = ['status = ?'];
    $params = [$status];
    $types = 's';
    
    if ($resolution !== null && $isAdmin) {
        $updateFields[] = 'resolution = ?';
        $params[] = $resolution;
        $types .= 's';
    }
    
    if ($assignedTo !== null && $isAdmin) {
        $updateFields[] = 'assigned_to = ?';
        $params[] = $assignedTo;
        $types .= 'i';
    }
    
    $updateFields[] = 'updated_at = NOW()';
    $params[] = $ticketId;
    $types .= 'i';
    
    $sql = "UPDATE support_tickets SET " . implode(', ', $updateFields) . " WHERE id = ?";
    
    $stmt = executeQuery($sql, $types, $params);
    
    if ($stmt) {
        logActivity($currentUser['id'], 'Update Ticket Status', 'Support', "Updated ticket {$ticket['ticket_no']} status to: $status");
        jsonResponse(true, 'Ticket status updated successfully');
    } else {
        jsonResponse(false, 'Failed to update ticket status');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Failed to update ticket status: ' . $e->getMessage());
}

