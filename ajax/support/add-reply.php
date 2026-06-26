<?php
/**
 * AJAX: Add Reply to Support Ticket
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
    $message = sanitize($input['message'] ?? '');
    
    if (!$ticketId || empty($message)) {
        jsonResponse(false, 'Ticket ID and message are required');
    }
    
    // Verify ticket exists and user has access
    $ticketSql = "SELECT * FROM support_tickets WHERE id = ?";
    $ticketStmt = executeQuery($ticketSql, 'i', [$ticketId]);
    $ticket = fetchOne($ticketStmt);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Check access permission
    if (!in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin']) && 
        $ticket['user_id'] != $currentUser['id']) {
        jsonResponse(false, 'Permission denied. You do not have access to this ticket.');
    }
    
    // Add reply
    $sql = "INSERT INTO ticket_replies (ticket_id, user_id, message)
            VALUES (?, ?, ?)";
    
    $stmt = executeQuery($sql, 'iis', [$ticketId, $currentUser['id'], $message]);
    
    if ($stmt) {
        // Update ticket status if it's closed/resolved and user is replying
        if (in_array($ticket['status'], ['Closed', 'Resolved'])) {
            $updateSql = "UPDATE support_tickets SET status = 'Reopened', updated_at = NOW() WHERE id = ?";
            executeQuery($updateSql, 'i', [$ticketId]);
        } else {
            // Update ticket timestamp
            $updateSql = "UPDATE support_tickets SET updated_at = NOW() WHERE id = ?";
            executeQuery($updateSql, 'i', [$ticketId]);
        }
        
        logActivity($currentUser['id'], 'Add Ticket Reply', 'Support', "Added reply to ticket: " . $ticket['ticket_no']);
        jsonResponse(true, 'Reply added successfully');
    } else {
        jsonResponse(false, 'Failed to add reply');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Failed to add reply: ' . $e->getMessage());
}

