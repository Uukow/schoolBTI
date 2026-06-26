<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$userId = $_POST['user_id'] ?? 0;
$isVerified = $_POST['is_verified'] ?? 0;

if (empty($userId)) jsonResponse(false, 'Invalid user ID');

// Update verification status
$sql = "UPDATE users SET is_verified = ? WHERE id = ?";
$stmt = executeQuery($sql, 'ii', [$isVerified, $userId]);

if ($stmt) {
    $action = $isVerified ? 'verified' : 'unverified';
    logActivity(getCurrentUser()['id'], 'Toggle User Verification', 'Users', "$action user ID: $userId");
    jsonResponse(true, "User $action successfully");
} else {
    jsonResponse(false, 'Failed to update verification status');
}

