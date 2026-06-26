<?php
/**
 * AJAX: Get Assignment Details
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$assignmentId = $_GET['id'] ?? 0;

if (empty($assignmentId)) {
    jsonResponse(false, 'Invalid assignment ID');
}

$sql = "SELECT * FROM class_subjects WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$assignmentId]);
$assignment = fetchOne($stmt);

if (!$assignment) {
    jsonResponse(false, 'Assignment not found');
}

jsonResponse(true, 'Assignment loaded', $assignment);

