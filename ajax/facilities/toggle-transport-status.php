<?php
/**
 * AJAX: Toggle Transport Assignment Status
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$assignmentId = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if (empty($assignmentId) || empty($status)) {
    jsonResponse(false, 'Assignment ID and status are required');
}

$sql = "UPDATE transport_assignments SET status = ? WHERE id = ?";
$stmt = executeQuery($sql, 'si', [$status, $assignmentId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Toggle Transport Status', 'Facilities', "Changed assignment ID: $assignmentId to $status");
    jsonResponse(true, 'Assignment status updated successfully');
} else {
    jsonResponse(false, 'Failed to update status');
}

