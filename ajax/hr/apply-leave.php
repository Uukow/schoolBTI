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
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $staffId = (int)($data['staff_id'] ?? 0);
    $leaveTypeId = (int)($data['leave_type_id'] ?? 0);
    $startDate = sanitize($data['start_date'] ?? '');
    $endDate = sanitize($data['end_date'] ?? '');
    $totalDays = (float)($data['total_days'] ?? 0);
    $reason = sanitize($data['reason'] ?? '');

    $canCreateForOthers = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_leave', 'create'));

    $staffRow = fetchOne(executeQuery("SELECT id, branch_id, first_name, last_name FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    $myStaffId = (int)($staffRow['id'] ?? 0);

    if (!$staffId) {
        $staffId = $myStaffId;
    }

    if (!$staffId) {
        jsonResponse(false, 'Staff record not found');
    }

    if (!$canCreateForOthers && $staffId !== $myStaffId) {
        jsonResponse(false, 'You can only apply leave for yourself');
    }

    if (!$leaveTypeId || !$startDate || !$endDate || $totalDays <= 0 || $reason === '') {
        jsonResponse(false, 'All fields are required');
    }

    if (strtotime($startDate) > strtotime($endDate)) {
        jsonResponse(false, 'End date must be on or after start date');
    }

    $overlap = fetchOne(executeQuery(
        "SELECT id FROM leave_applications
         WHERE staff_id = ? AND approval_stage IN ('Pending','Manager_Approved','Approved')
         AND status NOT IN ('Rejected','Cancelled')
         AND start_date <= ? AND end_date >= ?",
        'iss',
        [$staffId, $endDate, $startDate]
    ));
    if ($overlap) {
        jsonResponse(false, 'Leave dates overlap with an existing application');
    }

    $leaveYear = (int)date('Y', strtotime($startDate));
    $balanceCheck = LeaveBalanceService::canApplyLeave($staffId, $leaveTypeId, $totalDays, $leaveYear);
    if (!$balanceCheck['allowed']) {
        jsonResponse(false, $balanceCheck['message'], ['remaining' => $balanceCheck['remaining']]);
    }

    $stmt = executeQuery(
        "INSERT INTO leave_applications (staff_id, leave_type_id, start_date, end_date, total_days, reason, status, approval_stage)
         VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Pending')",
        'iissds',
        [$staffId, $leaveTypeId, $startDate, $endDate, $totalDays, $reason]
    );

    if (!$stmt) {
        global $conn;
        jsonResponse(false, 'Failed to submit leave application: ' . ($conn->error ?? ''));
    }

    $staff = $staffRow ?: fetchOne(executeQuery("SELECT branch_id, first_name, last_name FROM staff WHERE id = ?", 'i', [$staffId]));
    if (class_exists('NotificationService')) {
        NotificationService::notifyHrAdmins(
            $staff['branch_id'] ?? null,
            'New Leave Application',
            ($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '') . ' applied for ' . $totalDays . ' day(s) leave.',
            'hr_leave'
        );
    }

    logActivity($currentUser['id'], 'Apply Leave', 'HR', "Staff ID: $staffId, days: $totalDays");
    jsonResponse(true, 'Leave application submitted successfully', [
        'remaining' => max(0, $balanceCheck['remaining'] - $totalDays),
    ]);
} catch (Throwable $e) {
    error_log('apply-leave.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
