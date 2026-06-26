<?php
/**
 * AJAX: Mark Notification as Read
 * 
 * Mark a single notification as read
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$notificationId = $_POST['notification_id'] ?? 0;

if (empty($notificationId)) {
    jsonResponse(false, 'Invalid notification ID');
}

$currentUser = getCurrentUser();

// Update notification
$sql = "UPDATE notifications SET is_read = 1 
        WHERE id = ? AND user_id = ?";

$stmt = executeQuery($sql, 'ii', [$notificationId, $currentUser['id']]);

if ($stmt) {
    jsonResponse(true, 'Notification marked as read');
} else {
    jsonResponse(false, 'Failed to update notification');
}


