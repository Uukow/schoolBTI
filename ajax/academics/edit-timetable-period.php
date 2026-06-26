<?php
/**
 * AJAX: Edit Timetable Period
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$periodId = $_POST['id'] ?? 0;
$subjectId = $_POST['subject_id'] ?? 0;
$teacherId = $_POST['teacher_id'] ?? 0;
$dayOfWeek = $_POST['day_of_week'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$roomNo = sanitize($_POST['room_no'] ?? '');

if (empty($periodId) || empty($subjectId) || empty($teacherId) || empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Get current period details
$getSql = "SELECT * FROM timetable WHERE id = ?";
$stmt = executeQuery($getSql, 'i', [$periodId]);
$currentPeriod = fetchOne($stmt);

if (!$currentPeriod) {
    jsonResponse(false, 'Period not found');
}

// Check if class is graduated
$graduationCheck = validateClassNotGraduated($currentPeriod['class_id'], 'Timetable management');
if (!$graduationCheck['success']) {
    jsonResponse(false, $graduationCheck['message']);
}

// Check for time conflicts (excluding current period)
$checkSql = "SELECT * FROM timetable 
             WHERE class_id = ? AND section_id = ? AND day_of_week = ? 
             AND id != ?
             AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))
             AND session_id = ?";
$stmt = executeQuery($checkSql, 'iisissssi', [
    $currentPeriod['class_id'], 
    $currentPeriod['section_id'], 
    $dayOfWeek, 
    $periodId,
    $startTime, 
    $endTime, 
    $startTime, 
    $endTime, 
    $currentPeriod['session_id']
]);
$conflict = fetchOne($stmt);

if ($conflict) {
    jsonResponse(false, 'Time conflict detected with existing period');
}

$sql = "UPDATE timetable SET 
        subject_id = ?, 
        teacher_id = ?, 
        day_of_week = ?, 
        start_time = ?, 
        end_time = ?, 
        room_no = ?
        WHERE id = ?";

$stmt = executeQuery($sql, 'iissssi', [
    $subjectId, 
    $teacherId, 
    $dayOfWeek, 
    $startTime, 
    $endTime, 
    $roomNo,
    $periodId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Edit Timetable Period', 'Academics', "Updated period ID: $periodId");
    jsonResponse(true, 'Period updated successfully!');
} else {
    jsonResponse(false, 'Failed to update period');
}

