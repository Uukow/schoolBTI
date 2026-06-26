<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$subjectId = $_POST['id'] ?? 0;

if (empty($subjectId)) jsonResponse(false, 'Invalid subject ID');

// Check if subject is assigned to any class
$checkSql = "SELECT COUNT(*) as count FROM class_subjects WHERE subject_id = ?";
$stmt = executeQuery($checkSql, 'i', [$subjectId]);
$result = fetchOne($stmt);

if ($result['count'] > 0) {
    jsonResponse(false, 'Cannot delete subject that is assigned to classes');
}

$sql = "DELETE FROM subjects WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$subjectId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Subject', 'Academics', "Deleted subject ID: $subjectId");
    jsonResponse(true, 'Subject deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete subject');
}

