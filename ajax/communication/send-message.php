<?php
/**
 * AJAX: Send Message
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
    
    $toUserId = $data['to_user_id'] ?? $data['receiver_id'] ?? null;
    $subject = trim($data['subject'] ?? '');
    $message = trim($data['message'] ?? $data['content'] ?? '');
    $attachmentUrl = $data['attachment_url'] ?? null;
    
    if (!$toUserId) {
        jsonResponse(false, 'Recipient is required');
    }
    
    if (empty($message)) {
        jsonResponse(false, 'Message is required');
    }
    
    $senderId = $currentUser['id'] ?? null;
    
    // If to_user_id is a staff_id, map it to user_id
    $staffCheckSql = "SELECT user_id FROM staff WHERE id = ? LIMIT 1";
    $staffCheckStmt = executeQuery($staffCheckSql, 'i', [$toUserId]);
    $staffCheck = fetchOne($staffCheckStmt);
    
    if ($staffCheck && $staffCheck['user_id']) {
        $toUserId = $staffCheck['user_id'];
    }
    
    $sql = "INSERT INTO messages 
            (sender_id, receiver_id, subject, message, attachment_url, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $params = [
        $senderId,
        $toUserId,
        $subject,
        $message,
        $attachmentUrl,
    ];
    
    $types = 'iisss';
    
    executeQuery($sql, $types, $params);
    
    logActivity($senderId, 'Send Message', 'Communication', "Sent message to user ID: $toUserId");
    
    jsonResponse(true, 'Message sent successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to send message: ' . $e->getMessage());
}
