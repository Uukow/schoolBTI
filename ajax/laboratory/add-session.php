<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])) jsonResponse(false, 'Permission denied');

$currentUser   = getCurrentUser();
$experimentId  = (int)($_POST['experiment_id'] ?? 0);
$sectionId     = !empty($_POST['section_id'])    ? (int)$_POST['section_id']    : null;
$sessionDate   = sanitize($_POST['session_date']  ?? '');
$startTime     = sanitize($_POST['start_time']    ?? '');
$endTime       = sanitize($_POST['end_time']      ?? '');
$instructorId  = !empty($_POST['instructor_id'])  ? (int)$_POST['instructor_id']  : null;
$studentCount  = (int)($_POST['student_count'] ?? 0);
$classGroup    = sanitize($_POST['class_group'] ?? '');
$notes         = sanitize($_POST['notes'] ?? '');

if (!$experimentId || empty($sessionDate) || empty($startTime) || empty($endTime)) {
    jsonResponse(false, 'Experiment, date, and times are required');
}

if ($instructorId && !validateLabTeacherId($instructorId)) {
    jsonResponse(false, 'Please select a valid teacher as instructor');
}

$sql = "INSERT INTO lab_experiment_sessions (experiment_id, section_id, session_date, start_time, end_time,
        instructor_id, student_count, class_group, notes, status, branch_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, ?)";
$stmt = executeQuery($sql, 'iissssissii', [
    $experimentId, $sectionId, $sessionDate, $startTime, $endTime,
    $instructorId, $studentCount, $classGroup, $notes,
    $currentUser['branch_id'], $currentUser['id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Schedule Session', 'Laboratory', "Scheduled experiment session on $sessionDate");
    jsonResponse(true, 'Session scheduled successfully!');
} else {
    jsonResponse(false, 'Failed to schedule session');
}
