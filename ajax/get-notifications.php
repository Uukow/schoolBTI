<?php
/**
 * AJAX: Get Notifications
 * 
 * Fetch unread notifications for current user
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

// Get unread notifications
$sql = "SELECT * FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC 
        LIMIT 10";

$stmt = executeQuery($sql, 'i', [$currentUser['id']]);
$notifications = fetchAll($stmt);

jsonResponse(true, 'Notifications loaded', $notifications);


