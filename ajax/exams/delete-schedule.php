<?php
/**
 * AJAX: Delete Exam Schedule
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$scheduleId = $_POST['id'] ?? 0;

if (empty($scheduleId)) jsonResponse(false, 'Invalid schedule ID');

// Check if marks have been entered
$checkSql = "SELECT COUNT(*) as count FROM student_marks WHERE exam_schedule_id = ?";
$stmt = executeQuery($checkSql, 'i', [$scheduleId]);
$result = fetchOne($stmt);

if ($result['count'] > 0) {
    jsonResponse(false, 'Cannot delete schedule - marks have been entered');
}

$sql = "DELETE FROM exam_schedule WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$scheduleId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Exam Schedule', 'Exams', "Deleted schedule ID: $scheduleId");
    jsonResponse(true, 'Schedule removed successfully');
} else {
    jsonResponse(false, 'Failed to delete schedule');
}

