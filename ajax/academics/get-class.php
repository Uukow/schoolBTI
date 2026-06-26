<?php
/**
 * AJAX: Get Class Details
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_GET['id'] ?? 0;

if (empty($classId)) {
    jsonResponse(false, 'Invalid class ID');
}

$sql = "SELECT * FROM classes WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$classId]);
$class = fetchOne($stmt);

if (!$class) {
    jsonResponse(false, 'Class not found');
}

jsonResponse(true, 'Class loaded', $class);

