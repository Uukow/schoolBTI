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
$runId = (int)($data['run_id'] ?? 0);
$action = sanitize($data['action'] ?? 'approve');

if (!$runId) jsonResponse(false, 'Run ID required');

if ($action === 'approve') {
    PayrollService::approvePayrollRun($runId, $currentUser['id']);
    executeQuery("UPDATE hr_payroll_runs SET status='Approved' WHERE id=?", 'i', [$runId]);
    logActivity($currentUser['id'], 'Approve Payroll Run', 'HR', "Run ID: $runId");
    jsonResponse(true, 'Payroll run approved');
}

if ($action === 'lock') {
    executeQuery("UPDATE hr_payroll_runs SET status='Locked', locked_at=NOW() WHERE id=?", 'i', [$runId]);
    jsonResponse(true, 'Payroll run locked');
}

jsonResponse(false, 'Invalid action');
