<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

try {
    if (!isLoggedIn()) {
        header('Content-Type: application/json; charset=utf-8');
        jsonResponse(false, 'Unauthorized');
    }

    $canExport = hasRole(['Super Admin', 'Admin', 'Accountant'])
        || (function_exists('canPerform') && canPerform('hr_reports', 'export'));

    if (!$canExport) {
        header('Content-Type: application/json; charset=utf-8');
        jsonResponse(false, 'Export permission denied');
    }

    $currentUser = getCurrentUser();
    $isSuperAdmin = hasRole(['Super Admin']);
    $format = strtolower(sanitize($_GET['format'] ?? 'pdf'));

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

    $branchName = 'All Branches';
    if (!empty($report['filters']['branch_id'])) {
        $b = fetchOne(executeQuery('SELECT branch_name FROM branches WHERE id = ?', 'i', [$report['filters']['branch_id']]));
        $branchName = $b['branch_name'] ?? $branchName;
    } elseif (!$isSuperAdmin && !empty($currentUser['branch_id'])) {
        $b = fetchOne(executeQuery('SELECT branch_name FROM branches WHERE id = ?', 'i', [(int)$currentUser['branch_id']]));
        $branchName = $b['branch_name'] ?? $branchName;
    }

    $meta = [
        'user_name' => trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?: ($currentUser['username'] ?? 'User'),
        'branch_name' => $branchName,
    ];

    logActivity($currentUser['id'], 'Export HR Report', 'HR', ($report['type'] ?? '') . ' / ' . $format);

    if ($format === 'csv') {
        HrReportService::outputCsv($report);
        exit;
    }

    if ($format === 'pdf') {
        HrReportService::outputPdf($report, $meta);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    jsonResponse(false, 'Unsupported export format');
} catch (Throwable $e) {
    error_log('export-hr-report.php: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    jsonResponse(false, 'Export failed: ' . $e->getMessage());
}
