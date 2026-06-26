<?php
/**
 * AJAX: Get Syllabus
 * 
 * Fetch syllabus files
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

// Build query - using curriculum table
$sql = "SELECT c.id,
        c.class_id,
        c.subject_id,
        c.session_id,
        c.syllabus as description,
        c.file_path,
        SUBSTRING_INDEX(c.file_path, '/', -1) as file_name,
        c.created_at as uploaded_at,
        NULL as uploaded_by,
        NULL as uploaded_by_name,
        cl.class_name, 
        s.subject_name,
        CONCAT(cl.class_name, ' - ', s.subject_name) as title
        FROM curriculum c
        LEFT JOIN classes cl ON c.class_id = cl.id
        LEFT JOIN subjects s ON c.subject_id = s.id
        WHERE c.session_id = ?
        AND (cl.graduation_status IS NULL OR cl.graduation_status != 'Graduated')";

$params = [$sessionId];
$types = 'i';

if ($classId) {
    $sql .= " AND c.class_id = ?";
    $params[] = $classId;
    $types .= 'i';
}

if ($subjectId) {
    $sql .= " AND c.subject_id = ?";
    $params[] = $subjectId;
    $types .= 'i';
}

$sql .= " ORDER BY c.created_at DESC";

if (!empty($params)) {
    $stmt = executeQuery($sql, $types, $params);
} else {
    $stmt = executeQuery($sql);
}
$syllabus = fetchAll($stmt);

jsonResponse(true, 'Syllabus loaded', $syllabus);

