<?php
/**
 * AJAX: Add Section
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_POST['class_id'] ?? 0;
$sectionName = sanitize($_POST['section_name'] ?? '');
$capacity = $_POST['capacity'] ?? 40;

if (empty($classId) || empty($sectionName)) {
    jsonResponse(false, 'Class and section name are required');
}

$sql = "INSERT INTO sections (section_name, class_id, capacity, is_active)
        VALUES (?, ?, ?, 1)";

$stmt = executeQuery($sql, 'sii', [$sectionName, $classId, $capacity]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Section', 'Academics', "Added section: $sectionName");
    jsonResponse(true, 'Section added successfully');
} else {
    jsonResponse(false, 'Failed to add section');
}

