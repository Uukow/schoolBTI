<?php
/**
 * AJAX: Schedule Interview
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$applicationId = (int)($_POST['application_id'] ?? 0);
$interviewDate = $_POST['interview_date'] ?? '';
$notes = sanitize($_POST['notes'] ?? '');

if (empty($applicationId) || empty($interviewDate)) {
    jsonResponse(false, 'Application ID and interview date are required');
}

// Update application
$sql = "UPDATE admission_applications 
        SET status = 'Interview Scheduled', 
            interview_date = ?, 
            interview_notes = ?,
            reviewed_by = ?
        WHERE id = ?";

$stmt = executeQuery($sql, 'ssii', [
    $interviewDate, 
    $notes, 
    getCurrentUser()['id'],
    $applicationId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Schedule Interview', 'Admissions', "Scheduled interview for application ID: $applicationId");
    jsonResponse(true, 'Interview scheduled successfully!');
} else {
    jsonResponse(false, 'Failed to schedule interview');
}

