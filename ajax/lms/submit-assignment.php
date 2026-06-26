<?php
/**
 * AJAX: Submit Assignment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$assignmentId = (int)($_POST['assignment_id'] ?? 0);
$submissionText = sanitize($_POST['submission_text'] ?? '');
$filePath = null;

if (empty($assignmentId)) {
    jsonResponse(false, 'Assignment ID is required');
}

// Get student
$student = getStudentByUserId(getCurrentUser()['id']);
if (!$student) {
    jsonResponse(false, 'Student record not found');
}

// Check if already submitted
$checkSql = "SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?";
$stmt = executeQuery($checkSql, 'ii', [$assignmentId, $student['id']]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Assignment already submitted');
}

// Check due date
$assignmentSql = "SELECT due_date FROM assignments WHERE id = ?";
$stmt = executeQuery($assignmentSql, 'i', [$assignmentId]);
$assignment = fetchOne($stmt);

if (!$assignment) {
    jsonResponse(false, 'Assignment not found');
}

if (strtotime($assignment['due_date']) < time()) {
    jsonResponse(false, 'Assignment due date has passed');
}

// Handle file upload
if (!empty($_FILES['submission_file']['name'])) {
    $uploadDir = UPLOAD_PATH . 'assignment_submissions/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        jsonResponse(false, 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT');
    }
    
    $fileName = 'submission_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetPath)) {
        $filePath = 'assignment_submissions/' . $fileName;
    } else {
        jsonResponse(false, 'Failed to upload file');
    }
}

if (empty($submissionText) && empty($filePath)) {
    jsonResponse(false, 'Please provide submission text or upload a file');
}

$sql = "INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, file_path)
        VALUES (?, ?, ?, ?)";

$stmt = executeQuery($sql, 'iiss', [
    $assignmentId, $student['id'], $submissionText, $filePath
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Submit Assignment', 'LMS', "Submitted assignment ID: $assignmentId");
    jsonResponse(true, 'Assignment submitted successfully!');
} else {
    jsonResponse(false, 'Failed to submit assignment');
}

