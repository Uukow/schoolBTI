<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Teacher'])) jsonResponse(false, 'Permission denied');

$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$classId = $_POST['class_id'] ?? 0;
$subjectId = $_POST['subject_id'] ?? 0;

if (empty($title) || empty($classId) || empty($subjectId)) {
    jsonResponse(false, 'Title, class, and subject are required');
}

// Handle file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
    jsonResponse(false, 'Please select a file to upload');
}

$allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip'];
$uploadDir = ABSPATH . 'uploads/study_materials/';

// Create directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadResult = uploadFile($_FILES['file'], $uploadDir, $allowedTypes);

if (!$uploadResult['success']) {
    jsonResponse(false, $uploadResult['error']);
}

$filePath = 'uploads/study_materials/' . $uploadResult['filename'];
$fileSize = $_FILES['file']['size'];
$fileType = $_FILES['file']['type'];

// Get current session
$session = getCurrentSession();
$sessionId = $session['id'] ?? 1;

$sql = "INSERT INTO study_materials (title, description, class_id, subject_id, session_id, file_type, file_path, file_size, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'ssiiissii', [
    $title, $description, $classId, $subjectId, $sessionId, $fileType, $filePath, $fileSize, getCurrentUser()['id']
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Upload Study Material', 'LMS', "Uploaded: $title");
    jsonResponse(true, 'Study material uploaded successfully!');
} else {
    jsonResponse(false, 'Failed to upload material');
}

