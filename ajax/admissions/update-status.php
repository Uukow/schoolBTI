<?php
/**
 * AJAX: Update Admission Application Status
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Receptionist'])) jsonResponse(false, 'Permission denied');

$applicationId = (int)($_POST['application_id'] ?? 0);
$status = $_POST['status'] ?? '';
$notes = sanitize($_POST['notes'] ?? '');
$reason = sanitize($_POST['reason'] ?? $notes); // Support both 'notes' and 'reason'
$interviewDate = $_POST['interview_date'] ?? null;

if (empty($applicationId) || empty($status)) {
    jsonResponse(false, 'Application ID and status are required');
}

// Get application details
$sql = "SELECT * FROM admission_applications WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$applicationId]);
$app = fetchOne($stmt);

if (!$app) jsonResponse(false, 'Application not found');

// Update status with appropriate fields
if ($status == 'Rejected') {
    $updateSql = "UPDATE admission_applications SET status = ?, rejection_reason = ?, reviewed_by = ? WHERE id = ?";
    $stmt = executeQuery($updateSql, 'ssii', [$status, $reason, getCurrentUser()['id'], $applicationId]);
} else {
    $updateSql = "UPDATE admission_applications SET status = ?, interview_notes = ?, reviewed_by = ? WHERE id = ?";
    $stmt = executeQuery($updateSql, 'ssii', [$status, $notes, getCurrentUser()['id'], $applicationId]);
}

if ($stmt) {
    // Send email notification if email system available
    if (function_exists('sendAdmissionStatusEmail') && !empty($app['email'])) {
        sendAdmissionStatusEmail($app['email'], $app['first_name'] . ' ' . $app['last_name'], $app['application_no'], $status);
    }
    
    logActivity(getCurrentUser()['id'], 'Update Application Status', 'Admissions', 
                "Updated application {$app['application_no']} to status: $status");
    
    jsonResponse(true, "Application status updated to: $status");
} else {
    jsonResponse(false, 'Failed to update status');
}

