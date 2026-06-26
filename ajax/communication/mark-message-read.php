<?php
/**
 * AJAX: Mark Message as Read
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
    
    $messageId = $data['message_id'] ?? null;
    
    if (!$messageId) {
        jsonResponse(false, 'Message ID is required');
    }
    
    $currentUserId = $currentUser['id'] ?? null;
    
    $sql = "UPDATE messages 
            SET is_read = 1, read_at = NOW()
            WHERE id = ? AND receiver_id = ?";
    
    executeQuery($sql, 'ii', [$messageId, $currentUserId]);
    
    jsonResponse(true, 'Message marked as read');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to mark message as read: ' . $e->getMessage());
}

