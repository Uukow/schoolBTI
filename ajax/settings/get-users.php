<?php
/**
 * AJAX: Get Users List
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
    error_log("User management access denied for user: " . ($currentUser['username'] ?? 'unknown') . " with role: " . ($currentUser['role_name'] ?? 'unknown'));
    jsonResponse(false, 'Permission denied. Only Super Admin and Admin can access user management.');
}

try {
    $roleId = $_GET['role_id'] ?? null;
    $branchId = $_GET['branch_id'] ?? null;
    $isActive = $_GET['is_active'] ?? null;
    
    $sql = "SELECT u.id, u.username, u.email, u.is_active, u.last_login, u.created_at,
            r.role_name, b.branch_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($roleId) {
        $sql .= " AND u.role_id = ?";
        $params[] = $roleId;
        $types .= 'i';
    }
    
    if ($branchId) {
        $sql .= " AND u.branch_id = ?";
        $params[] = $branchId;
        $types .= 'i';
    }
    
    if ($isActive !== null) {
        $sql .= " AND u.is_active = ?";
        $params[] = $isActive;
        $types .= 'i';
    }
    
    // Branch filter for non-super admins
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND (u.branch_id IS NULL OR u.branch_id = ?)";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY u.created_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $users = fetchAll($stmt) ?: [];
    
    jsonResponse(true, 'Users loaded', $users);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load users: ' . $e->getMessage());
}

