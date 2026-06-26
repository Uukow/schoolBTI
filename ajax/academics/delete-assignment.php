<?php
/**
 * AJAX: Delete Class-Subject-Teacher Assignment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$assignmentId = $_POST['id'] ?? 0;

if (empty($assignmentId)) {
    jsonResponse(false, 'Invalid assignment ID');
}

// Get assignment details for logging
$getSql = "SELECT cs.*, c.class_name, s.subject_name, c.graduation_status
           FROM class_subjects cs
           INNER JOIN classes c ON cs.class_id = c.id
           INNER JOIN subjects s ON cs.subject_id = s.id
           WHERE cs.id = ?";
$assignment = fetchOne(executeQuery($getSql, 'i', [$assignmentId]));

if (!$assignment) {
    jsonResponse(false, 'Assignment not found');
}

// Check if class is graduated
if (isset($assignment['graduation_status']) && $assignment['graduation_status'] === 'Graduated') {
    jsonResponse(false, 'Cannot delete assignment for a graduated class. All academic operations are disabled for graduated classes.');
}

$sql = "DELETE FROM class_subjects WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$assignmentId]);

if ($stmt) {
    $logMessage = "Deleted assignment: " . ($assignment['subject_name'] ?? '') . " from " . ($assignment['class_name'] ?? '');
    logActivity(getCurrentUser()['id'], 'Delete Assignment', 'Academics', $logMessage);
    jsonResponse(true, 'Assignment deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete assignment');
}

