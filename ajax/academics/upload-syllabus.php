<?php
/**
 * AJAX: Upload Syllabus
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Teacher'])) jsonResponse(false, 'Permission denied');

$classId = $_POST['class_id'] ?? 0;
$subjectId = $_POST['subject_id'] ?? 0;
$syllabusContent = sanitize($_POST['syllabus'] ?? '');
$learningObjectives = sanitize($_POST['learning_objectives'] ?? '');

if (empty($classId) || empty($subjectId)) {
    jsonResponse(false, 'Class and subject are required');
}

// Check if class is graduated
$graduationCheck = validateClassNotGraduated($classId, 'Syllabus upload');
if (!$graduationCheck['success']) {
    jsonResponse(false, $graduationCheck['message']);
}

// Get current session
$session = getCurrentSession();
$sessionId = $session['id'] ?? 1;

// Handle file upload if provided
$filePath = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $uploadDir = ABSPATH . 'uploads/syllabus/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadResult = uploadFile($_FILES['file'], $uploadDir, ['pdf', 'doc', 'docx']);
    if ($uploadResult['success']) {
        $filePath = 'uploads/syllabus/' . $uploadResult['filename'];
    }
}

// Check if syllabus already exists
$checkSql = "SELECT id FROM curriculum WHERE class_id = ? AND subject_id = ? AND session_id = ?";
$stmt = executeQuery($checkSql, 'iii', [$classId, $subjectId, $sessionId]);
$existing = fetchOne($stmt);

if ($existing) {
    // Update existing
    $sql = "UPDATE curriculum SET syllabus = ?, learning_objectives = ?, file_path = ? WHERE id = ?";
    $stmt = executeQuery($sql, 'sssi', [$syllabusContent, $learningObjectives, $filePath, $existing['id']]);
    $message = 'Syllabus updated successfully!';
} else {
    // Insert new
    $sql = "INSERT INTO curriculum (class_id, subject_id, session_id, syllabus, learning_objectives, file_path)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = executeQuery($sql, 'iiisss', [$classId, $subjectId, $sessionId, $syllabusContent, $learningObjectives, $filePath]);
    $message = 'Syllabus uploaded successfully!';
}

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Upload Syllabus', 'Academics', "Uploaded syllabus for class ID: $classId");
    jsonResponse(true, $message);
} else {
    jsonResponse(false, 'Failed to upload syllabus');
}

