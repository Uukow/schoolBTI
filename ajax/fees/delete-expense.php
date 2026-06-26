<?php
/**
 * AJAX: Delete Expense
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$expenseId = (int)($_POST['id'] ?? 0);

if (empty($expenseId)) jsonResponse(false, 'Invalid expense ID');

$sql = "DELETE FROM expenses WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$expenseId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Expense', 'Fees', "Deleted expense ID: $expenseId");
    jsonResponse(true, 'Expense record deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete expense record');
}

