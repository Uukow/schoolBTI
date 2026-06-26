<?php
/**
 * AJAX: Send Notification to Applicant
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$applicationId = $_POST['application_id'] ?? 0;

if (empty($applicationId)) jsonResponse(false, 'Invalid application ID');

// Get application details
$sql = "SELECT * FROM admission_applications WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$applicationId]);
$app = fetchOne($stmt);

if (!$app) jsonResponse(false, 'Application not found');

// Send notification via email/SMS
$message = "Dear {$app['parent_name']}, Your admission application (No: {$app['application_no']}) for {$app['first_name']} {$app['last_name']} has been accepted. Please visit the school to complete enrollment. Contact: " . ADMIN_EMAIL;

// Log communication
$logSql = "INSERT INTO communication_logs (communication_type, recipient, subject, message, status, sent_by)
           VALUES ('Email', ?, 'Admission Acceptance', ?, 'Sent', ?)";
executeQuery($logSql, 'ssi', [$app['parent_email'] ?? $app['parent_phone'], $message, getCurrentUser()['id']]);

logActivity(getCurrentUser()['id'], 'Send Admission Notification', 'Admissions', "Sent notification for application: {$app['application_no']}");

jsonResponse(true, 'Notification sent successfully!');

