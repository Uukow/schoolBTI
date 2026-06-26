<?php
/**
 * AJAX: Delete Message
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$messageId = (int)($_POST['id'] ?? 0);

if (empty($messageId)) jsonResponse(false, 'Invalid message ID');

// Check if user owns the message (sender or receiver)
$checkSql = "SELECT id FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)";
$stmt = executeQuery($checkSql, 'iii', [$messageId, getCurrentUser()['id'], getCurrentUser()['id']]);
if (!fetchOne($stmt)) {
    jsonResponse(false, 'You do not have permission to delete this message');
}

$sql = "DELETE FROM messages WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$messageId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Message', 'Communication', "Deleted message ID: $messageId");
    jsonResponse(true, 'Message deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete message');
}

