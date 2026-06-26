<?php
/**
 * Get User Permissions - AJAX Endpoint
 * 
 * Returns user's permissions based on their role
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

try {
    // Support both session-based and user_id parameter authentication
    $requestUserId = $_GET['user_id'] ?? null;
    
    if ($requestUserId) {
        // Authenticate via user_id parameter (for Flutter app)
        $userSql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.is_active = 1";
        $userStmt = executeQuery($userSql, 'i', [$requestUserId]);
        $currentUser = fetchOne($userStmt);
        
        if (!$currentUser) {
            jsonResponse(false, 'Invalid user ID', null, 401);
            exit;
        }
        
        // Set session for compatibility
        $_SESSION['user_id'] = $currentUser['id'];
        $_SESSION['role_name'] = $currentUser['role_name'];
    } else {
        // Session-based authentication
        if (!isLoggedIn()) {
            jsonResponse(false, 'Unauthorized', null, 401);
            exit;
        }
        $currentUser = getCurrentUser();
    }

    if (!$currentUser) {
        jsonResponse(false, 'User not found', null, 404);
        exit;
    }

    $roleName = $currentUser['role_name'];
    $roleId = $currentUser['role_id'];

    // Get permissions for the role
    $sql = "SELECT p.permission_key, p.module, p.permission_name
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
            ORDER BY p.module, p.permission_name";
    $stmt = executeQuery($sql, 'i', [$roleId]);
    $permissions = fetchAll($stmt);

    // Build permission map by module
    $permissionMap = [];
    foreach ($permissions as $perm) {
        $module = $perm['module'];
        if (!isset($permissionMap[$module])) {
            $permissionMap[$module] = [];
        }
        $permissionMap[$module][] = $perm['permission_key'];
    }

    // Define module access based on role (fallback if permissions table is empty)
    $moduleAccess = [];
    
    if ($roleName === 'Super Admin') {
        // Super Admin has access to everything
        $moduleAccess = [
            'dashboard' => true,
            'students' => true,
            'admissions' => true,
            'academics' => true,
            'attendance' => true,
            'examinations' => true,
            'fees' => true,
            'library' => true,
            'facilities' => true,
            'hr' => true,
            'lms' => true,
            'communication' => true,
            'events' => true,
            'reports' => true,
            'settings' => true,
            'support' => true,
        ];
    } elseif ($roleName === 'Admin') {
        // Admin has access to most modules except system settings
        $moduleAccess = [
            'dashboard' => true,
            'students' => true,
            'admissions' => true,
            'academics' => true,
            'attendance' => true,
            'examinations' => true,
            'fees' => true,
            'library' => true,
            'facilities' => true,
            'hr' => true,
            'lms' => true,
            'communication' => true,
            'events' => true,
            'reports' => true,
            'settings' => false, // Limited settings access
            'support' => true,
        ];
    } elseif ($roleName === 'Teacher') {
        // Teachers have limited access
        $moduleAccess = [
            'dashboard' => true,
            'teacher_portal' => true, // Teacher-specific portal
            'academics' => true, // View-only for assigned classes
            'attendance' => true, // For assigned classes
            'examinations' => true, // For assigned classes
            'library' => true, // View resources
            'lms' => true, // View and create materials
            'communication' => true, // Send messages
            'events' => true, // View events
            'reports' => false, // Limited reports
            'support' => true,
            // No access to:
            'students' => false,
            'admissions' => false,
            'fees' => false,
            'facilities' => false,
            'hr' => false,
            'settings' => false,
        ];
    } else {
        // Default: no access
        $moduleAccess = [
            'dashboard' => true,
            'support' => true,
        ];
    }

    // Merge permission-based access with role-based access
    // If permissions exist, use them; otherwise use role defaults
    if (!empty($permissionMap)) {
        // Build module access from permissions
        $moduleAccess = [];
        foreach ($permissionMap as $module => $perms) {
            // If user has any permission in a module, they can access it
            $moduleAccess[$module] = !empty($perms);
        }
    }

    jsonResponse(true, 'Permissions loaded', [
        'role' => $roleName,
        'permissions' => $permissionMap,
        'module_access' => $moduleAccess,
    ]);

} catch (Exception $e) {
    error_log('Get permissions error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load permissions: ' . $e->getMessage());
    exit;
}

