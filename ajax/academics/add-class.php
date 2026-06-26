<?php
/**
 * AJAX: Add Class
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$className = sanitize($_POST['class_name'] ?? '');
$classCode = sanitize($_POST['class_code'] ?? '');
$branchId = $_POST['branch_id'] ?? '';
$description = sanitize($_POST['description'] ?? '');
$classOrder = $_POST['class_order'] ?? 0;

if (empty($className) || empty($classCode) || empty($branchId)) {
    jsonResponse(false, 'All required fields must be filled');
}

$sql = "INSERT INTO classes (class_name, class_code, branch_id, description, class_order, is_active)
        VALUES (?, ?, ?, ?, ?, 1)";

$stmt = executeQuery($sql, 'ssisi', [$className, $classCode, $branchId, $description, $classOrder]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Class', 'Academics', "Added class: $className");
    jsonResponse(true, 'Class added successfully');
} else {
    jsonResponse(false, 'Failed to add class');
}

