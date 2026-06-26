<?php
ob_start();
require_once '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

require_once __DIR__ . '/_auth.php';

try {
    $auth = hrApiAuth();
    if (!in_array($auth['role'], ['Super Admin', 'Admin', 'Accountant'])) {
        sendApiResponse(false, 'Permission denied', null, 403);
    }
    require_once ABSPATH . 'includes/services/hr/HrDashboardService.php';
    $kpis = HrDashboardService::getKpis($auth['branch_id'], $auth['is_super_admin']);
    $charts = [
        'departments' => HrDashboardService::getDepartmentBreakdown($auth['branch_id'], $auth['is_super_admin']),
        'attendance_trend' => HrDashboardService::getAttendanceTrend($auth['branch_id'], $auth['is_super_admin']),
    ];
    sendApiResponse(true, 'HR dashboard data', ['kpis' => $kpis, 'charts' => $charts]);
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}
