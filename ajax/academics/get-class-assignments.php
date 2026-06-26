<?php
/**
 * AJAX: Get Assignments for a Class
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication - get user by ID
    $sql = "SELECT u.*, r.role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
    
    // Check permission
    $allowedRoles = ['Super Admin', 'Admin'];
    if (!in_array($currentUser['role_name'], $allowedRoles)) {
        jsonResponse(false, 'Permission denied');
    }
} else {
    // Web session-based authentication
    if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
    if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');
}

$classId = $_GET['class_id'] ?? 0;

if (empty($classId)) {
    jsonResponse(false, 'Invalid class ID');
}

$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Get assignments for this class
$sql = "SELECT cs.*, 
        s.subject_name, s.subject_code, s.subject_type,
        st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id
        FROM class_subjects cs
        INNER JOIN subjects s ON cs.subject_id = s.id
        LEFT JOIN staff st ON cs.teacher_id = st.id
        WHERE cs.class_id = ? AND cs.session_id = ?
        ORDER BY s.subject_name";

$stmt = executeQuery($sql, 'ii', [$classId, $sessionId]);
$assignments = fetchAll($stmt);

jsonResponse(true, 'Assignments loaded', $assignments);

