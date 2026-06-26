<?php
/**
 * AJAX: Mark All Notifications as Read
 * 
 * Mark all notifications as read for current user
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$currentUser = getCurrentUser();

// Update all notifications
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";

$stmt = executeQuery($sql, 'i', [$currentUser['id']]);

if ($stmt) {
    jsonResponse(true, 'All notifications marked as read');
} else {
    jsonResponse(false, 'Failed to update notifications');
}


