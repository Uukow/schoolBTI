<?php
/**
 * AJAX: Get Roles and Permissions
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

// Check if user has required role
if (!$currentUser || !in_array($currentUser['role_name'] ?? '', ['Super Admin', 'Admin'])) {
    error_log("Roles access denied for user: " . ($currentUser['username'] ?? 'unknown') . " with role: " . ($currentUser['role_name'] ?? 'unknown'));
    jsonResponse(false, 'Permission denied. Only Super Admin and Admin can access roles and permissions.');
}

try {
    // Get all roles
    $rolesSql = "SELECT * FROM roles ORDER BY role_name ASC";
    $rolesStmt = executeQuery($rolesSql);
    $roles = fetchAll($rolesStmt) ?: [];
    
    // Get all permissions
    $permsSql = "SELECT * FROM permissions ORDER BY module, permission_name ASC";
    $permsStmt = executeQuery($permsSql);
    $permissions = fetchAll($permsStmt) ?: [];
    
    // Get role permissions mapping
    $rolePermsSql = "SELECT role_id, permission_id FROM role_permissions";
    $rolePermsStmt = executeQuery($rolePermsSql);
    $rolePermissions = fetchAll($rolePermsStmt) ?: [];
    
    // Group permissions by role
    $rolePermMap = [];
    foreach ($rolePermissions as $rp) {
        $roleId = $rp['role_id'];
        if (!isset($rolePermMap[$roleId])) {
            $rolePermMap[$roleId] = [];
        }
        $rolePermMap[$roleId][] = $rp['permission_id'];
    }
    
    // Attach permissions to roles
    foreach ($roles as &$role) {
        $role['permissions'] = $rolePermMap[$role['id']] ?? [];
    }
    
    $response = [
        'roles' => $roles,
        'permissions' => $permissions,
    ];
    
    jsonResponse(true, 'Roles and permissions loaded', $response);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load roles: ' . $e->getMessage());
}

