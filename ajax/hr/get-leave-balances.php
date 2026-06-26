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

    $currentUser = getCurrentUser();
    $staffId = (int)($_GET['staff_id'] ?? 0);
    $year = (int)($_GET['year'] ?? date('Y'));

    $staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    $myStaffId = (int)($staffRow['id'] ?? 0);

    if (!$staffId) {
        $staffId = $myStaffId;
    }

    if (!$staffId) {
        jsonResponse(false, 'Staff ID is required');
    }

    $isStaffRole = hasRole(['Teacher', 'Staff']);
    $canViewOthers = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_leave', 'view'));

    if ($isStaffRole && $staffId !== $myStaffId) {
        jsonResponse(false, 'Permission denied');
    }

    if (!$canViewOthers && !$isStaffRole && $staffId !== $myStaffId) {
        jsonResponse(false, 'Permission denied');
    }

    $balances = LeaveBalanceService::getAllBalancesForStaff($staffId, $year);

    $totalAllocated = 0;
    $totalUsed = 0;
    $totalRemaining = 0;
    foreach ($balances as $b) {
        $totalAllocated += (float)$b['allocated_days'] + (float)$b['carried_forward'];
        $totalUsed += (float)$b['used_days'];
        $totalRemaining += (float)$b['remaining_days'];
    }

    $staff = fetchOne(executeQuery(
        "SELECT staff_id, first_name, last_name, department FROM staff WHERE id = ?",
        'i',
        [$staffId]
    ));

    jsonResponse(true, 'OK', [
        'balances' => $balances,
        'staff' => $staff,
        'year' => $year,
        'summary' => [
            'allocated' => round($totalAllocated, 1),
            'used' => round($totalUsed, 1),
            'remaining' => round($totalRemaining, 1),
        ],
    ]);
} catch (Throwable $e) {
    error_log('get-leave-balances.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
