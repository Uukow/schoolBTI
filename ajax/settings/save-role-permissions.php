<?php
/**
 * AJAX: Save Role Permissions (Granular)
 * 
 * Saves role-based permissions using the granular permission system
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

$roleId = (int)($_POST['role_id'] ?? 0);
$permissionsJson = $_POST['permissions'] ?? '{}';
$permissions = json_decode($permissionsJson, true);

if (empty($roleId)) {
    echo json_encode(['success' => false, 'message' => 'Role ID is required']);
    exit;
}

if (!is_array($permissions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid permissions data']);
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? null;

// Save permissions using PermissionManager
$result = PermissionManager::saveRolePermissions($roleId, $permissions, $userId);

if ($result) {
    // Get role name for logging
    $roleSql = "SELECT role_name FROM roles WHERE id = ?";
    $roleStmt = executeQuery($roleSql, 'i', [$roleId]);
    $role = fetchOne($roleStmt);
    $roleName = $role['role_name'] ?? "Role #{$roleId}";
    
    // Log activity
    logActivity($userId, 'Update Permissions', 'Settings', "Updated granular permissions for role: {$roleName}");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Permissions saved successfully!'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save permissions. Please try again.'
    ]);
}

