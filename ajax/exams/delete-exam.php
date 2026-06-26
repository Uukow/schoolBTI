<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$examId = $_POST['id'] ?? 0;

if (empty($examId)) jsonResponse(false, 'Invalid exam ID');

$sql = "DELETE FROM exams WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$examId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Exam', 'Exams', "Deleted exam ID: $examId");
    jsonResponse(true, 'Exam deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete exam');
}

