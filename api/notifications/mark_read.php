<?php
/**
 * API Mark Notification as Read
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$userId = $input['user_id'] ?? null;
$notificationId = $input['notification_id'] ?? null;

if (!$userId || !$notificationId) {
    sendApiResponse(false, 'User ID and Notification ID are required', null, 400);
}

// Verify notification belongs to user
$sql = "SELECT id FROM notifications WHERE id = ? AND user_id = ?";
$stmt = executeQuery($sql, 'ii', [$notificationId, $userId]);
$notification = fetchOne($stmt);

if (!$notification) {
    sendApiResponse(false, 'Notification not found', null, 404);
}

// Mark as read
$updateSql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?";
$updateStmt = executeQuery($updateSql, 'i', [$notificationId]);

if ($updateStmt) {
    sendApiResponse(true, 'Notification marked as read');
} else {
    sendApiResponse(false, 'Failed to mark notification as read', null, 500);
}














