<?php
/**
 * AJAX: Save Role Permissions
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$roleId = (int)($_POST['role_id'] ?? 0);
$permissions = json_decode($_POST['permissions'] ?? '[]', true);

if (empty($roleId)) {
    jsonResponse(false, 'Role ID is required');
}

beginTransaction();

try {
    // Delete existing permissions
    $deleteSql = "DELETE FROM role_permissions WHERE role_id = ?";
    executeQuery($deleteSql, 'i', [$roleId]);
    
    // Insert new permissions
    if (!empty($permissions)) {
        $insertSql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
        $stmt = getDB()->prepare($insertSql);
        
        foreach ($permissions as $permissionId) {
            $stmt->bind_param('ii', $roleId, $permissionId);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    logActivity(getCurrentUser()['id'], 'Save Permissions', 'Settings', "Updated permissions for role ID: $roleId");
    
    commitTransaction();
    jsonResponse(true, 'Permissions saved successfully!');
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

