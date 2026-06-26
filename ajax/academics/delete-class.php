<?php
/**
 * AJAX: Delete Class
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_POST['id'] ?? 0;

if (empty($classId)) jsonResponse(false, 'Invalid class ID');

// Check if class has students
$checkSql = "SELECT COUNT(*) as count FROM students WHERE current_class_id = ?";
$stmt = executeQuery($checkSql, 'i', [$classId]);
$result = fetchOne($stmt);

if ($result['count'] > 0) {
    jsonResponse(false, 'Cannot delete class with active students');
}

$sql = "DELETE FROM classes WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$classId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Class', 'Academics', "Deleted class ID: $classId");
    jsonResponse(true, 'Class deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete class');
}

