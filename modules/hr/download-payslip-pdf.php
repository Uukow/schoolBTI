<?php
require_once '../../config/config.php';
requireLogin();

$paymentId = (int)($_GET['id'] ?? 0);
if (!$paymentId) die('Invalid payslip ID');

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isStaffSelf = false;

if (hasRole(['Teacher', 'Staff']) && !hasRole(['Super Admin', 'Admin', 'Accountant'])) {
    $staff = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    $payment = fetchOne(executeQuery("SELECT staff_id FROM salary_payments WHERE id = ?", 'i', [$paymentId]));
    if (!$staff || !$payment || $staff['id'] != $payment['staff_id']) die('Access denied');
    $isStaffSelf = true;
} elseif (!hasRole(['Super Admin', 'Admin', 'Accountant'])) {
    die('Access denied');
}

PayslipService::downloadPdf($paymentId, $currentUser['branch_id'] ?? null, $isSuperAdmin || $isStaffSelf);
