<?php
/**
 * AJAX: Delete Branch
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$branchId = $_POST['id'] ?? 0;

if (empty($branchId)) {
    jsonResponse(false, 'Invalid branch ID');
}

// Check if branch exists
$checkSql = "SELECT branch_name FROM branches WHERE id = ?";
$stmt = executeQuery($checkSql, 'i', [$branchId]);
$branch = fetchOne($stmt);

if (!$branch) {
    jsonResponse(false, 'Branch not found');
}

// Check if branch has students
$studentsSql = "SELECT COUNT(*) as count FROM students WHERE branch_id = ?";
$stmt = executeQuery($studentsSql, 'i', [$branchId]);
$studentsResult = fetchOne($stmt);

if ($studentsResult['count'] > 0) {
    jsonResponse(false, 'Cannot delete branch with active students. Please transfer or remove students first.');
}

// Check if branch has staff
$staffSql = "SELECT COUNT(*) as count FROM staff WHERE branch_id = ?";
$stmt = executeQuery($staffSql, 'i', [$branchId]);
$staffResult = fetchOne($stmt);

if ($staffResult['count'] > 0) {
    jsonResponse(false, 'Cannot delete branch with active staff. Please transfer or remove staff first.');
}

// Check if branch has classes
$classesSql = "SELECT COUNT(*) as count FROM classes WHERE branch_id = ?";
$stmt = executeQuery($classesSql, 'i', [$branchId]);
$classesResult = fetchOne($stmt);

if ($classesResult['count'] > 0) {
    jsonResponse(false, 'Cannot delete branch with active classes. Please transfer or remove classes first.');
}

// Delete branch
$deleteSql = "DELETE FROM branches WHERE id = ?";
$stmt = executeQuery($deleteSql, 'i', [$branchId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Branch', 'Branches', "Deleted branch: {$branch['branch_name']} (ID: $branchId)");
    jsonResponse(true, 'Branch deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete branch');
}

