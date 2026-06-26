<?php
/**
 * AJAX: Create Support Ticket
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
    
    $subject = sanitize($input['subject'] ?? '');
    $description = sanitize($input['description'] ?? '');
    $category = sanitize($input['category'] ?? 'General');
    $priority = $input['priority'] ?? 'Medium';
    
    if (empty($subject) || empty($description)) {
        jsonResponse(false, 'Subject and description are required');
    }
    
    // Validate priority
    $validPriorities = ['Low', 'Medium', 'High', 'Critical'];
    if (!in_array($priority, $validPriorities)) {
        $priority = 'Medium';
    }
    
    // Generate ticket number
    $sql = "SELECT MAX(id) as max_id FROM support_tickets";
    $result = executeQuery($sql);
    $row = fetchOne($result);
    $nextId = ($row['max_id'] ?? 0) + 1;
    $ticketNo = generateUniqueId(TICKET_PREFIX, $nextId, 6);
    
    $userId = $currentUser['id'];
    
    $sql = "INSERT INTO support_tickets (ticket_no, user_id, category, priority, subject, description, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Open')";
    
    $stmt = executeQuery($sql, 'sissss', [$ticketNo, $userId, $category, $priority, $subject, $description]);
    
    if ($stmt) {
        logActivity($userId, 'Create Support Ticket', 'Support', "Created ticket: $ticketNo");
        jsonResponse(true, 'Support ticket created successfully! Ticket No: ' . $ticketNo, ['ticket_no' => $ticketNo]);
    } else {
        jsonResponse(false, 'Failed to create ticket');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Failed to create ticket: ' . $e->getMessage());
}

