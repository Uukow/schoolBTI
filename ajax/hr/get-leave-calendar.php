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
    if (!hasRole(['Super Admin', 'Admin', 'Teacher', 'Staff'])
        && !(function_exists('canPerform') && canPerform('hr_leave', 'view'))) {
        jsonResponse(false, 'Permission denied');
    }

    $currentUser = getCurrentUser();
    $isSuperAdmin = hasRole(['Super Admin']);
    $startDate = sanitize($_GET['start'] ?? date('Y-m-01'));
    $endDate = sanitize($_GET['end'] ?? date('Y-m-t'));

    $staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    $myStaffId = (int)($staffRow['id'] ?? 0);

    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== ''
        ? (int)$_GET['branch_id']
        : (!$isSuperAdmin ? (int)($currentUser['branch_id'] ?? 0) : null);
    if ($branchId === 0) {
        $branchId = null;
    }

    $staffId = (int)($_GET['staff_id'] ?? 0);
    if (!empty($_GET['mine']) && $myStaffId) {
        $staffId = $myStaffId;
    }
    if (hasRole(['Teacher', 'Staff']) && $myStaffId) {
        $staffId = $myStaffId;
    }

    $events = HrDashboardService::getLeaveCalendarEvents($startDate, $endDate, [
        'branch_id' => $branchId,
        'is_super_admin' => $isSuperAdmin,
        'department' => sanitize($_GET['department'] ?? ''),
        'staff_id' => $staffId,
        'show_leaves' => !isset($_GET['show_leaves']) || $_GET['show_leaves'] !== '0',
        'show_holidays' => !isset($_GET['show_holidays']) || $_GET['show_holidays'] !== '0',
    ]);

    $stats = HrDashboardService::getLeaveCalendarStats($events);

    usort($events, function ($a, $b) {
        return strcmp($a['start'] ?? '', $b['start'] ?? '');
    });

    $upcoming = array_values(array_filter($events, function ($e) {
        return ($e['start'] ?? '') >= date('Y-m-d');
    }));
    $upcoming = array_slice($upcoming, 0, 8);

    jsonResponse(true, 'OK', [
        'events' => $events,
        'stats' => $stats,
        'upcoming' => $upcoming,
    ]);
} catch (Throwable $e) {
    error_log('get-leave-calendar.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
