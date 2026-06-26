<?php
/**
 * AJAX: HR Dashboard Stats
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $currentUser['branch_id'] ?? null;

try {
    $kpis = HrDashboardService::getKpis($branchId, $isSuperAdmin);
    $departments = HrDashboardService::getDepartmentBreakdown($branchId, $isSuperAdmin);
    $attendanceTrend = HrDashboardService::getAttendanceTrend($branchId, $isSuperAdmin);
    $recentActivity = HrDashboardService::getRecentActivity(8);

    jsonResponse(true, 'Dashboard data loaded', [
        'kpis' => $kpis,
        'departments' => $departments,
        'attendance_trend' => $attendanceTrend,
        'recent_activity' => $recentActivity,
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load dashboard: ' . $e->getMessage());
}
