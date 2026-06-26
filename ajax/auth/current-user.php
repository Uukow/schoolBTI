<?php
/**
 * Current User API Endpoint
 * 
 * Returns the currently logged-in user's information
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
    // Check if user is logged in
    if (!isLoggedIn()) {
        jsonResponse(false, 'Not authenticated', null, 401);
        exit;
    }
    
    // Get current user ID from session
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        jsonResponse(false, 'User session not found', null, 401);
        exit;
    }
    
    // Get full user details
    $sql = "SELECT u.*, r.role_name,
            CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as full_name,
            s.id as staff_id,
            st.id as student_id
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN staff s ON u.id = s.user_id
            LEFT JOIN students st ON u.id = st.user_id
            WHERE u.id = ? AND u.is_active = 1";
    
    $stmt = executeQuery($sql, 'i', [$userId]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        jsonResponse(false, 'User not found', null, 404);
        exit;
    }
    
    // Prepare user data for response
    $userData = [
        'user_id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role_name'],
        'branch_id' => $user['branch_id'] ? (int)$user['branch_id'] : null,
        'full_name' => trim($user['full_name'] ?? $user['username']),
        'profile_image' => $user['profile_image'] ?? null,
        'staff_id' => $user['staff_id'] ? (int)$user['staff_id'] : null,
        'student_id' => $user['student_id'] ? (int)$user['student_id'] : null,
    ];
    
    // Return success with user data
    jsonResponse(true, 'User data retrieved', $userData);
    
} catch (Exception $e) {
    error_log('Current user error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred while retrieving user data.');
    exit;
}

