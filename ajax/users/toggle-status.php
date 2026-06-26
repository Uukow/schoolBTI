<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$userId = $_POST['user_id'] ?? 0;

if (empty($userId)) jsonResponse(false, 'Invalid user ID');

// Can't deactivate yourself
if ($userId == getCurrentUser()['id']) {
    jsonResponse(false, 'You cannot deactivate your own account');
}

// Toggle status
$sql = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Toggle User Status', 'Users', "Toggled status for user ID: $userId");
    jsonResponse(true, 'User status updated successfully');
} else {
    jsonResponse(false, 'Failed to update user status');
}

