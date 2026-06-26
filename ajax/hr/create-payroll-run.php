<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$paymentMonth = sanitize($data['payment_month'] ?? '');
$branchId = !empty($data['branch_id']) ? (int)$data['branch_id'] : null;
$remarks = sanitize($data['remarks'] ?? '');

if (empty($paymentMonth)) jsonResponse(false, 'Payment month is required');

if (!hasRole(['Super Admin']) && $branchId === null) {
    $branchId = $currentUser['branch_id'] ?? null;
}

try {
    $result = PayrollService::createPayrollRun($paymentMonth, $branchId, $currentUser['id'], $remarks);
    logActivity($currentUser['id'], 'Create Payroll Run', 'HR', "Run: {$result['run_no']}");
    NotificationService::notifyHrAdmins($branchId, 'Payroll Run Created', "Payroll run {$result['run_no']} created for review.", 'hr_payroll');
    jsonResponse(true, "Payroll run {$result['run_no']} created for {$result['staff_count']} staff. Total: " . CURRENCY_SYMBOL . number_format($result['total_amount'], 2), $result);
} catch (Throwable $e) {
    jsonResponse(false, 'Failed: ' . $e->getMessage());
}
