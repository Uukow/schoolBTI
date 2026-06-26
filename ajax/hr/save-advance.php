<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = (int)($data['id'] ?? 0);
$staffId = (int)($data['staff_id'] ?? $currentUser['staff_id'] ?? 0);
$amount = (float)($data['requested_amount'] ?? 0);
$reason = sanitize($data['reason'] ?? '');
$recoveryMonths = (int)($data['recovery_months'] ?? 1);
$status = sanitize($data['status'] ?? '');

if ($id > 0 && !empty($status)) {
    if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');
    $approvedAmount = (float)($data['approved_amount'] ?? $amount);
    $monthlyRecovery = $recoveryMonths > 0 ? round($approvedAmount / $recoveryMonths, 2) : $approvedAmount;
    executeQuery(
        "UPDATE hr_salary_advances SET status=?, approved_amount=?, monthly_recovery=?, approved_by=?, approved_at=NOW() WHERE id=?",
        'sddii', [$status, $approvedAmount, $monthlyRecovery, $currentUser['id'], $id]
    );
    if ($status === 'Disbursed') {
        executeQuery("UPDATE hr_salary_advances SET disbursed_at=NOW() WHERE id=?", 'i', [$id]);
    }
    logActivity($currentUser['id'], 'Update Advance Status', 'HR', "Advance ID: $id -> $status");
    jsonResponse(true, 'Advance updated');
}

if (!$staffId || $amount <= 0 || empty($reason)) jsonResponse(false, 'Staff, amount and reason required');

$advanceNo = HrNumberService::next('ADV-', 'hr_salary_advances', 'advance_no');
$monthlyRecovery = $recoveryMonths > 0 ? round($amount / $recoveryMonths, 2) : $amount;
$sql = "INSERT INTO hr_salary_advances (advance_no, staff_id, requested_amount, reason, recovery_months, monthly_recovery)
     VALUES (?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'sidisd', [$advanceNo, $staffId, $amount, $reason, $recoveryMonths, $monthlyRecovery]);

if ($stmt) {
    NotificationService::notifyHrAdmins($currentUser['branch_id'] ?? null, 'Advance Salary Request', "New advance request $advanceNo", 'hr_payroll');
    jsonResponse(true, 'Advance request submitted');
}
jsonResponse(false, 'Failed to submit request');
