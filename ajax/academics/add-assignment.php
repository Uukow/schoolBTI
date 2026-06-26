<?php
/**
 * AJAX: Add Class-Subject-Teacher Assignment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_POST['class_id'] ?? 0;
$subjectId = $_POST['subject_id'] ?? 0;
$teacherId = $_POST['teacher_id'] ?? null;
$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

if (empty($classId) || empty($subjectId)) {
    jsonResponse(false, 'Class and subject are required');
}

// Check if class is graduated
$graduationCheck = validateClassNotGraduated($classId, 'Class-Subject-Teacher assignment');
if (!$graduationCheck['success']) {
    jsonResponse(false, $graduationCheck['message']);
}

// Check if assignment already exists
$checkSql = "SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND session_id = ?";
$checkStmt = executeQuery($checkSql, 'iii', [$classId, $subjectId, $sessionId]);
$existing = fetchOne($checkStmt);

if ($existing) {
    jsonResponse(false, 'This class-subject combination already exists for this session');
}

// If teacher_id is provided, validate it exists
if (!empty($teacherId)) {
    $teacherSql = "SELECT id FROM staff WHERE id = ? AND status = 'Active'";
    $teacherStmt = executeQuery($teacherSql, 'i', [$teacherId]);
    if (!fetchOne($teacherStmt)) {
        jsonResponse(false, 'Invalid teacher selected');
    }
}

$sql = "INSERT INTO class_subjects (class_id, subject_id, teacher_id, session_id)
        VALUES (?, ?, ?, ?)";

$params = [$classId, $subjectId, $teacherId, $sessionId];
$types = 'iiii';

$stmt = executeQuery($sql, $types, $params);

if ($stmt) {
    // Get class and subject names for logging
    $classSql = "SELECT class_name FROM classes WHERE id = ?";
    $class = fetchOne(executeQuery($classSql, 'i', [$classId]));
    
    $subjectSql = "SELECT subject_name FROM subjects WHERE id = ?";
    $subject = fetchOne(executeQuery($subjectSql, 'i', [$subjectId]));
    
    $logMessage = "Assigned " . ($subject['subject_name'] ?? '') . " to " . ($class['class_name'] ?? '');
    if ($teacherId) {
        $teacherSql = "SELECT first_name, last_name FROM staff WHERE id = ?";
        $teacher = fetchOne(executeQuery($teacherSql, 'i', [$teacherId]));
        $logMessage .= " - Teacher: " . ($teacher['first_name'] ?? '') . " " . ($teacher['last_name'] ?? '');
    }
    
    logActivity(getCurrentUser()['id'], 'Add Assignment', 'Academics', $logMessage);
    jsonResponse(true, 'Assignment added successfully');
} else {
    jsonResponse(false, 'Failed to add assignment');
}

