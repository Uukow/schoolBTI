<?php
/**
 * AJAX: Get Lesson Plans
 * 
 * Fetch lesson plans
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Start output buffering to catch any unwanted output
ob_start();

// Suppress error display for clean JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../config/config.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header early
header('Content-Type: application/json; charset=utf-8');

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication - verify user exists and is active
    $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
}

$classId = $_GET['class_id'] ?? null;
$subjectId = $_GET['subject_id'] ?? null;
$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Build query
$sql = "SELECT lp.*, 
        lp.lesson_title as title,
        lp.lesson_date as date,
        c.class_name, 
        s.subject_name, 
        st.first_name as teacher_first_name, 
        st.last_name as teacher_last_name
        FROM lesson_plans lp
        LEFT JOIN classes c ON lp.class_id = c.id
        LEFT JOIN subjects s ON lp.subject_id = s.id
        LEFT JOIN staff st ON lp.teacher_id = st.id
        WHERE lp.session_id = ?";

$params = [$sessionId];
$types = 'i';

if ($classId) {
    $sql .= " AND lp.class_id = ?";
    $params[] = $classId;
    $types .= 'i';
}

if ($subjectId) {
    $sql .= " AND lp.subject_id = ?";
    $params[] = $subjectId;
    $types .= 'i';
}

$sql .= " ORDER BY lp.lesson_date DESC, lp.lesson_title";

$stmt = executeQuery($sql, $types, $params);
$lessonPlans = fetchAll($stmt);

jsonResponse(true, 'Lesson plans loaded', $lessonPlans);

