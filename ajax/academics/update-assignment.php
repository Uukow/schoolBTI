<?php
/**
 * AJAX: Update Class-Subject-Teacher Assignment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$assignmentId = $_POST['id'] ?? 0;
$classId = $_POST['class_id'] ?? 0;
$subjectId = $_POST['subject_id'] ?? 0;
$teacherId = $_POST['teacher_id'] ?? null;
$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

if (empty($assignmentId) || empty($classId) || empty($subjectId)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Check if class is graduated
$graduationCheck = validateClassNotGraduated($classId, 'Class-Subject-Teacher assignment update');
if (!$graduationCheck['success']) {
    jsonResponse(false, $graduationCheck['message']);
}

// Check if assignment exists
$checkSql = "SELECT id FROM class_subjects WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$assignmentId]);
$existing = fetchOne($checkStmt);

if (!$existing) {
    jsonResponse(false, 'Assignment not found');
}

// Check if another assignment with same class-subject-session exists
$duplicateSql = "SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND session_id = ? AND id != ?";
$duplicateStmt = executeQuery($duplicateSql, 'iiii', [$classId, $subjectId, $sessionId, $assignmentId]);
$duplicate = fetchOne($duplicateStmt);

if ($duplicate) {
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

$sql = "UPDATE class_subjects SET class_id = ?, subject_id = ?, teacher_id = ? WHERE id = ?";
$params = [$classId, $subjectId, $teacherId, $assignmentId];
$types = 'iiii';

$stmt = executeQuery($sql, $types, $params);

if ($stmt) {
    // Get class and subject names for logging
    $classSql = "SELECT class_name FROM classes WHERE id = ?";
    $class = fetchOne(executeQuery($classSql, 'i', [$classId]));
    
    $subjectSql = "SELECT subject_name FROM subjects WHERE id = ?";
    $subject = fetchOne(executeQuery($subjectSql, 'i', [$subjectId]));
    
    $logMessage = "Updated assignment: " . ($subject['subject_name'] ?? '') . " to " . ($class['class_name'] ?? '');
    if ($teacherId) {
        $teacherSql = "SELECT first_name, last_name FROM staff WHERE id = ?";
        $teacher = fetchOne(executeQuery($teacherSql, 'i', [$teacherId]));
        $logMessage .= " - Teacher: " . ($teacher['first_name'] ?? '') . " " . ($teacher['last_name'] ?? '');
    }
    
    logActivity(getCurrentUser()['id'], 'Update Assignment', 'Academics', $logMessage);
    jsonResponse(true, 'Assignment updated successfully');
} else {
    jsonResponse(false, 'Failed to update assignment');
}

