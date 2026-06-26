<?php
/**
 * AJAX: Delete Payment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$paymentId = (int)($_POST['id'] ?? 0);

if (empty($paymentId)) jsonResponse(false, 'Invalid payment ID');

$sql = "DELETE FROM salary_payments WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$paymentId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Payment', 'HR', "Deleted payment ID: $paymentId");
    jsonResponse(true, 'Payment deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete payment');
}

