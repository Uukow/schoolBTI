<?php
/**
 * AJAX: Update Subject
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$subjectId = $_POST['id'] ?? 0;
$subjectName = sanitize($_POST['subject_name'] ?? '');
$subjectCode = sanitize($_POST['subject_code'] ?? '');
$subjectType = $_POST['subject_type'] ?? 'Core';
$description = sanitize($_POST['description'] ?? '');
$isActive = isset($_POST['is_active']) ? 1 : 0;

if (empty($subjectId) || empty($subjectName) || empty($subjectCode)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Check if subject exists
$checkSql = "SELECT id FROM subjects WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$subjectId]);
$existing = fetchOne($checkStmt);

if (!$existing) {
    jsonResponse(false, 'Subject not found');
}

// Check if code already exists for another subject
$duplicateSql = "SELECT id FROM subjects WHERE subject_code = ? AND id != ?";
$duplicateStmt = executeQuery($duplicateSql, 'si', [$subjectCode, $subjectId]);
$duplicate = fetchOne($duplicateStmt);

if ($duplicate) {
    jsonResponse(false, 'Subject code already exists');
}

$sql = "UPDATE subjects SET subject_name = ?, subject_code = ?, subject_type = ?, description = ?, is_active = ? WHERE id = ?";
$stmt = executeQuery($sql, 'ssssii', [$subjectName, $subjectCode, $subjectType, $description, $isActive, $subjectId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Update Subject', 'Academics', "Updated subject: $subjectName ($subjectCode)");
    jsonResponse(true, 'Subject updated successfully');
} else {
    jsonResponse(false, 'Failed to update subject');
}

