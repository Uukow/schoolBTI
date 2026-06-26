<?php
/**
 * AJAX: Delete Transport Assignment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$assignmentId = (int)($_POST['id'] ?? 0);

if (empty($assignmentId)) jsonResponse(false, 'Invalid assignment ID');

$sql = "DELETE FROM transport_assignments WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$assignmentId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Transport Assignment', 'Facilities', "Deleted assignment ID: $assignmentId");
    jsonResponse(true, 'Assignment deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete assignment');
}

