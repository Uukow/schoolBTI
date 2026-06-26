<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$userId = $_POST['user_id'] ?? 0;

if (empty($userId)) jsonResponse(false, 'Invalid user ID');

// Can't delete yourself
if ($userId == getCurrentUser()['id']) {
    jsonResponse(false, 'You cannot delete your own account');
}

// Get user info for logging
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);
$user = fetchOne($stmt);

if (!$user) {
    jsonResponse(false, 'User not found');
}

// Begin transaction
beginTransaction();

try {
    // Delete user
    $deleteSql = "DELETE FROM users WHERE id = ?";
    $deleteStmt = executeQuery($deleteSql, 'i', [$userId]);
    
    if ($deleteStmt) {
        // Log activity
        logActivity(
            getCurrentUser()['id'],
            'Delete User',
            'Users',
            "Deleted user: {$user['username']} ({$user['email']})"
        );
        
        commitTransaction();
        jsonResponse(true, 'User deleted successfully');
    } else {
        rollbackTransaction();
        jsonResponse(false, 'Failed to delete user');
    }
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

