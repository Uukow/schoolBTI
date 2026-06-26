<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    if (!hasRole(['Super Admin', 'Admin', 'Accountant'])
        && !(function_exists('canPerform') && canPerform('hr_reports', 'view'))) {
        jsonResponse(false, 'Permission denied');
    }

    $currentUser = getCurrentUser();
    $isSuperAdmin = hasRole(['Super Admin']);

    $report = HrReportService::generate([
        'type' => sanitize($_GET['type'] ?? 'summary'),
        'month' => sanitize($_GET['month'] ?? date('Y-m')),
        'year' => (int)($_GET['year'] ?? date('Y')),
        'department' => sanitize($_GET['department'] ?? ''),
        'status' => sanitize($_GET['status'] ?? ''),
        'branch_id' => isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null,
        'is_super_admin' => $isSuperAdmin,
        'user_branch_id' => $currentUser['branch_id'] ?? null,
    ]);

    jsonResponse(true, 'Report loaded', $report);
} catch (Throwable $e) {
    error_log('get-hr-reports.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
