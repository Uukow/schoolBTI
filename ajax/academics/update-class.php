<?php
/**
 * AJAX: Update Class
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_POST['id'] ?? 0;
$className = sanitize($_POST['class_name'] ?? '');
$classCode = sanitize($_POST['class_code'] ?? '');
$branchId = $_POST['branch_id'] ?? '';
$description = sanitize($_POST['description'] ?? '');
$classOrder = $_POST['class_order'] ?? 0;
$isActive = isset($_POST['is_active']) ? 1 : 0;

if (empty($classId) || empty($className) || empty($classCode) || empty($branchId)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Check if class exists
$checkSql = "SELECT id FROM classes WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$classId]);
$existingClass = fetchOne($checkStmt);

if (!$existingClass) {
    jsonResponse(false, 'Class not found');
}

$sql = "UPDATE classes SET class_name = ?, class_code = ?, branch_id = ?, description = ?, class_order = ?, is_active = ? WHERE id = ?";
$stmt = executeQuery($sql, 'ssisiii', [$className, $classCode, $branchId, $description, $classOrder, $isActive, $classId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Update Class', 'Academics', "Updated class: $className");
    jsonResponse(true, 'Class updated successfully');
} else {
    jsonResponse(false, 'Failed to update class');
}

