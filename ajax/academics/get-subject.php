<?php
/**
 * AJAX: Get Subject Details
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$subjectId = $_GET['id'] ?? 0;

if (empty($subjectId)) {
    jsonResponse(false, 'Invalid subject ID');
}

$sql = "SELECT * FROM subjects WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$subjectId]);
$subject = fetchOne($stmt);

if (!$subject) {
    jsonResponse(false, 'Subject not found');
}

jsonResponse(true, 'Subject loaded', $subject);

