<?php
/**
 * API Mark All Notifications as Read
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$userId = $input['user_id'] ?? null;

if (!$userId) {
    sendApiResponse(false, 'User ID is required', null, 400);
}

// Mark all as read
$sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0";
$stmt = executeQuery($sql, 'i', [$userId]);

if ($stmt) {
    sendApiResponse(true, 'All notifications marked as read');
} else {
    sendApiResponse(false, 'Failed to mark notifications as read', null, 500);
}














