<?php
/**
 * AJAX: Toggle Branch Status
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$branchId = $_POST['id'] ?? 0;

if (empty($branchId)) {
    jsonResponse(false, 'Invalid branch ID');
}

// Get branch info
$checkSql = "SELECT branch_name, is_active FROM branches WHERE id = ?";
$stmt = executeQuery($checkSql, 'i', [$branchId]);
$branch = fetchOne($stmt);

if (!$branch) {
    jsonResponse(false, 'Branch not found');
}

// Toggle status
$newStatus = $branch['is_active'] ? 0 : 1;
$sql = "UPDATE branches SET is_active = ? WHERE id = ?";
$stmt = executeQuery($sql, 'ii', [$newStatus, $branchId]);

if ($stmt) {
    $statusText = $newStatus ? 'activated' : 'deactivated';
    logActivity(getCurrentUser()['id'], 'Toggle Branch Status', 'Branches', "{$statusText} branch: {$branch['branch_name']} (ID: $branchId)");
    jsonResponse(true, "Branch {$statusText} successfully");
} else {
    jsonResponse(false, 'Failed to update branch status');
}

