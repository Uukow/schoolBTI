<?php
require_once '../../config/config.php';
requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$runId = (int)($_GET['run_id'] ?? 0);
if (!$runId) die('Run ID required');

$rows = PayrollService::bankExportCsv($runId);
$run = fetchOne(executeQuery("SELECT run_no, payment_month FROM hr_payroll_runs WHERE id=?", 'i', [$runId]));

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bank_export_' . ($run['run_no'] ?? $runId) . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Staff ID', 'Employee Name', 'Bank Name', 'Account No', 'Amount', 'Reference']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['staff_id'],
        $r['first_name'] . ' ' . $r['last_name'],
        $r['bank_name'] ?? '',
        $r['bank_account_no'] ?? '',
        number_format($r['net_salary'], 2, '.', ''),
        'SAL-' . ($run['run_no'] ?? '') . '-' . $r['staff_id'],
    ]);
}
fclose($out);
exit;
