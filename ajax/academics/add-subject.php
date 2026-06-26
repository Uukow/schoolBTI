<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$subjectName = sanitize($_POST['subject_name'] ?? '');
$subjectCode = sanitize($_POST['subject_code'] ?? '');
$subjectType = $_POST['subject_type'] ?? 'Core';
$description = sanitize($_POST['description'] ?? '');

if (empty($subjectName) || empty($subjectCode)) {
    jsonResponse(false, 'Subject name and code are required');
}

// Check if code exists
$checkSql = "SELECT id FROM subjects WHERE subject_code = ?";
$stmt = executeQuery($checkSql, 's', [$subjectCode]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Subject code already exists');
}

$sql = "INSERT INTO subjects (subject_name, subject_code, subject_type, description, is_active)
        VALUES (?, ?, ?, ?, 1)";

$stmt = executeQuery($sql, 'ssss', [$subjectName, $subjectCode, $subjectType, $description]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Subject', 'Academics', "Added subject: $subjectName ($subjectCode)");
    jsonResponse(true, 'Subject added successfully!');
} else {
    jsonResponse(false, 'Failed to add subject');
}

