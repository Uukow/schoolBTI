<?php
/**
 * AJAX: Add Role
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$roleName = sanitize($_POST['role_name'] ?? '');
$roleDescription = sanitize($_POST['role_description'] ?? '');

if (empty($roleName)) {
    jsonResponse(false, 'Role name is required');
}

// Check if role exists
$checkSql = "SELECT id FROM roles WHERE role_name = ?";
$stmt = executeQuery($checkSql, 's', [$roleName]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Role name already exists');
}

$sql = "INSERT INTO roles (role_name, role_description, is_system_role)
        VALUES (?, ?, 0)";

$stmt = executeQuery($sql, 'ss', [$roleName, $roleDescription]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Role', 'Settings', "Added role: $roleName");
    jsonResponse(true, 'Role added successfully!');
} else {
    jsonResponse(false, 'Failed to add role');
}

