<?php
/**
 * AJAX: Approve Expense
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$expenseId = (int)($_POST['id'] ?? 0);

if (empty($expenseId)) jsonResponse(false, 'Invalid expense ID');

$sql = "UPDATE expenses SET approved_by = ? WHERE id = ?";
$stmt = executeQuery($sql, 'ii', [getCurrentUser()['id'], $expenseId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Approve Expense', 'Fees', "Approved expense ID: $expenseId");
    jsonResponse(true, 'Expense approved successfully');
} else {
    jsonResponse(false, 'Failed to approve expense');
}

