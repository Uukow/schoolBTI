<?php
/**
 * AJAX: Get Academic Settings
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
    error_log("Academic settings access denied for user: " . ($currentUser['username'] ?? 'unknown') . " with role: " . ($currentUser['role_name'] ?? 'unknown'));
    jsonResponse(false, 'Permission denied. Only Super Admin and Admin can access academic settings.');
}

try {
    // Get system settings
    $sql = "SELECT current_session, academic_year_start, academic_year_end FROM system_settings LIMIT 1";
    $stmt = executeQuery($sql);
    $systemSettings = fetchOne($stmt);
    
    // Get current session details
    $currentSession = null;
    if ($systemSettings && $systemSettings['current_session']) {
        $sessionSql = "SELECT * FROM academic_sessions WHERE id = ?";
        $sessionStmt = executeQuery($sessionSql, 'i', [$systemSettings['current_session']]);
        $currentSession = fetchOne($sessionStmt);
    }
    
    // Get all sessions
    $sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
    $sessionsStmt = executeQuery($sessionsSql);
    $sessions = fetchAll($sessionsStmt) ?: [];
    
    $response = [
        'current_session' => $systemSettings['current_session'] ?? null,
        'academic_year_start' => $systemSettings['academic_year_start'] ?? null,
        'academic_year_end' => $systemSettings['academic_year_end'] ?? null,
        'current_session_details' => $currentSession,
        'sessions' => $sessions,
    ];
    
    jsonResponse(true, 'Academic settings loaded', $response);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load academic settings: ' . $e->getMessage());
}

