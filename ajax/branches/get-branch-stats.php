<?php
/**
 * AJAX: Get Branch Statistics
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$branchId = $_GET['id'] ?? 0;

if (empty($branchId)) {
    jsonResponse(false, 'Invalid branch ID');
}

// Get statistics
$studentsSql = "SELECT COUNT(*) as count FROM students WHERE branch_id = ? AND status = 'Active'";
$stmt = executeQuery($studentsSql, 'i', [$branchId]);
$studentsResult = fetchOne($stmt);
$studentsCount = $studentsResult['count'] ?? 0;

$staffSql = "SELECT COUNT(*) as count FROM staff WHERE branch_id = ? AND status = 'Active'";
$stmt = executeQuery($staffSql, 'i', [$branchId]);
$staffResult = fetchOne($stmt);
$staffCount = $staffResult['count'] ?? 0;

$classesSql = "SELECT COUNT(*) as count FROM classes WHERE branch_id = ? AND is_active = 1";
$stmt = executeQuery($classesSql, 'i', [$branchId]);
$classesResult = fetchOne($stmt);
$classesCount = $classesResult['count'] ?? 0;

jsonResponse(true, 'Statistics loaded', [
    'students' => $studentsCount,
    'staff' => $staffCount,
    'classes' => $classesCount
]);

