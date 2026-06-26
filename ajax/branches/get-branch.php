<?php
/**
 * AJAX: Get Branch Details
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$branchId = $_GET['id'] ?? 0;

if (empty($branchId)) {
    jsonResponse(false, 'Invalid branch ID');
}

$sql = "SELECT * FROM branches WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$branchId]);
$branch = fetchOne($stmt);

if (!$branch) {
    jsonResponse(false, 'Branch not found');
}

jsonResponse(true, 'Branch loaded', $branch);

