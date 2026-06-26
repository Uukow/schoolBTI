<?php
/**
 * AJAX: Get Messages
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
    $messageType = $_GET['message_type'] ?? 'Inbox'; // 'Inbox', 'Sent', 'Draft'
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';
    
    $currentUserId = $currentUser['id'];
    
    if ($messageType == 'Inbox') {
        $sql = "SELECT m.*, 
                sender.username as from_user_name,
                receiver.username as to_user_name,
                receiver.role_id as to_user_role_id,
                r.role_name as to_user_role
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.id
                LEFT JOIN users receiver ON m.receiver_id = receiver.id
                LEFT JOIN roles r ON receiver.role_id = r.id
                WHERE m.receiver_id = ?";
        
        if ($unreadOnly) {
            $sql .= " AND m.is_read = 0";
        }
        
        $sql .= " ORDER BY m.sent_at DESC";
        
        $stmt = executeQuery($sql, 'i', [$currentUserId]);
    } elseif ($messageType == 'Sent') {
        $sql = "SELECT m.*, 
                sender.username as from_user_name,
                receiver.username as to_user_name,
                receiver.role_id as to_user_role_id,
                r.role_name as to_user_role
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.id
                LEFT JOIN users receiver ON m.receiver_id = receiver.id
                LEFT JOIN roles r ON receiver.role_id = r.id
                WHERE m.sender_id = ?
                ORDER BY m.sent_at DESC";
        
        $stmt = executeQuery($sql, 'i', [$currentUserId]);
    } else {
        // Draft messages - Note: messages table doesn't have status column
        // For now, return empty array or all sent messages
        // In a real implementation, you'd need a status column or separate drafts table
        $sql = "SELECT m.*, 
                sender.username as from_user_name,
                receiver.username as to_user_name,
                receiver.role_id as to_user_role_id,
                r.role_name as to_user_role
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.id
                LEFT JOIN users receiver ON m.receiver_id = receiver.id
                LEFT JOIN roles r ON receiver.role_id = r.id
                WHERE m.sender_id = ?
                ORDER BY m.sent_at DESC";
        
        $stmt = executeQuery($sql, 'i', [$currentUserId]);
    }
    
    $messages = fetchAll($stmt);
    
    $formatted = [];
    foreach ($messages as $msg) {
        $formatted[] = [
            'id' => $msg['id'],
            'from_user_id' => $msg['sender_id'],
            'from_user_name' => $msg['from_user_name'],
            'to_user_id' => $msg['receiver_id'],
            'to_user_name' => $msg['to_user_name'],
            'to_user_role' => $msg['to_user_role'],
            'subject' => $msg['subject'] ?? '',
            'message' => $msg['message'] ?? $msg['content'] ?? '',
            'attachment_url' => $msg['attachment_url'] ?? null,
            'is_read' => $msg['is_read'] == 1 || $msg['is_read'] == true,
            'read_at' => null, // messages table doesn't have read_at
            'created_at' => $msg['sent_at'] ?? $msg['created_at'],
            'message_type' => $messageType,
        ];
    }
    
    jsonResponse(true, 'Messages loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load messages: ' . $e->getMessage());
}

