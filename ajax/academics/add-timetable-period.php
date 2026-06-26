<?php
/**
 * AJAX: Add Timetable Period
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_POST['class_id'] ?? 0;
$sectionId = $_POST['section_id'] ?? 0;
$subjectId = $_POST['subject_id'] ?? 0;
$teacherId = $_POST['teacher_id'] ?? 0;
$dayOfWeek = $_POST['day_of_week'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$roomNo = sanitize($_POST['room_no'] ?? '');

if (empty($classId) || empty($sectionId) || empty($subjectId) || empty($teacherId) || empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Check if class is graduated
$graduationCheck = validateClassNotGraduated($classId, 'Timetable management');
if (!$graduationCheck['success']) {
    jsonResponse(false, $graduationCheck['message']);
}

// Get current session
$session = getCurrentSession();
$sessionId = $session['id'] ?? 1;

// Check for time conflicts
$checkSql = "SELECT * FROM timetable 
             WHERE class_id = ? AND section_id = ? AND day_of_week = ? 
             AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))
             AND session_id = ?";
$stmt = executeQuery($checkSql, 'iisssssi', [$classId, $sectionId, $dayOfWeek, $startTime, $endTime, $startTime, $endTime, $sessionId]);
$conflict = fetchOne($stmt);

if ($conflict) {
    jsonResponse(false, 'Time conflict detected with existing period');
}

$sql = "INSERT INTO timetable (class_id, section_id, subject_id, teacher_id, day_of_week, start_time, end_time, room_no, session_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'iiiissssi', [
    $classId, $sectionId, $subjectId, $teacherId, $dayOfWeek, $startTime, $endTime, $roomNo, $sessionId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Timetable Period', 'Academics', "Added period for $dayOfWeek");
    jsonResponse(true, 'Period added to timetable successfully!');
} else {
    jsonResponse(false, 'Failed to add period');
}

