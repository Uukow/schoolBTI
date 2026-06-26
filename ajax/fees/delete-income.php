<?php
/**
 * AJAX: Delete Income
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$incomeId = (int)($_POST['id'] ?? 0);

if (empty($incomeId)) jsonResponse(false, 'Invalid income ID');

$sql = "DELETE FROM income WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$incomeId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Income', 'Fees', "Deleted income ID: $incomeId");
    jsonResponse(true, 'Income record deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete income record');
}

