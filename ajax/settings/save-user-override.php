<?php
/**
 * AJAX: Save User Permission Override
 * 
 * Sets or removes user-specific permission overrides
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasRole(['Super Admin'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$moduleKey = sanitize($_POST['module_key'] ?? '');
$actionKey = sanitize($_POST['action_key'] ?? '');
$granted = isset($_POST['granted']) ? (bool)$_POST['granted'] : true;
$remove = isset($_POST['remove']) ? (bool)$_POST['remove'] : false;

if (empty($userId) || empty($moduleKey) || empty($actionKey)) {
    echo json_encode(['success' => false, 'message' => 'User ID, module, and action are required']);
    exit;
}

$currentUser = getCurrentUser();
$changedBy = $currentUser['id'] ?? null;

if ($remove) {
    // Remove override
    $result = PermissionManager::removeUserOverride($userId, $moduleKey, $actionKey, $changedBy);
    $message = 'Override removed successfully';
} else {
    // Set override
    $result = PermissionManager::setUserOverride($userId, $moduleKey, $actionKey, $granted, $changedBy);
    $message = $granted ? 'Permission granted' : 'Permission denied';
}

if ($result) {
    // Get user info for logging
    $userSql = "SELECT username FROM users WHERE id = ?";
    $userStmt = executeQuery($userSql, 'i', [$userId]);
    $user = fetchOne($userStmt);
    $username = $user['username'] ?? "User #{$userId}";
    
    // Log activity
    logActivity($changedBy, 'Update User Override', 'Settings', "Updated permission override for user: {$username} ({$moduleKey}.{$actionKey})");
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save override. Please try again.'
    ]);
}

