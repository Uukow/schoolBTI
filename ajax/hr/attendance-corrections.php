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
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            handleList($currentUser);
            break;
        case 'submit':
            handleSubmit($currentUser, $data ?: $_POST);
            break;
        case 'approve':
            handleApprove($currentUser, $data ?: $_POST);
            break;
        default:
            jsonResponse(false, 'Invalid action');
    }
} catch (Throwable $e) {
    error_log('attendance-corrections.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}

function resolveStaffId($currentUser): int
{
    $row = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    return (int)($row['id'] ?? $currentUser['staff_id'] ?? 0);
}

function handleList($currentUser): void
{
    $status = sanitize($_GET['status'] ?? '');
    $staffId = (int)($_GET['staff_id'] ?? 0);
    $year = (int)($_GET['year'] ?? 0);
    $month = sanitize($_GET['month'] ?? '');
    $department = sanitize($_GET['department'] ?? '');
    $search = sanitize($_GET['q'] ?? '');
    $mine = !empty($_GET['mine']);

    $myStaffId = resolveStaffId($currentUser);
    $isStaffRole = hasRole(['Teacher', 'Staff']);
    $canViewAllBranches = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_attendance', 'view'));

    $sql = "SELECT ac.*, s.first_name, s.last_name, s.staff_id AS staff_code, s.designation, s.department,
            b.branch_name,
            TRIM(CONCAT(COALESCE(sa.first_name,''), ' ', COALESCE(sa.last_name,''))) AS original_marked_by,
            att.status AS current_status, att.check_in AS current_check_in, att.check_out AS current_check_out
            FROM hr_attendance_corrections ac
            INNER JOIN staff s ON ac.staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            LEFT JOIN staff_attendance att ON ac.attendance_id = att.id
            LEFT JOIN users u ON ac.submitted_by = u.id
            LEFT JOIN staff sa ON sa.user_id = u.id
            WHERE 1=1";
    $params = [];
    $types = '';

    if ($isStaffRole || $mine) {
        $sql .= " AND ac.staff_id = ?";
        $params[] = $myStaffId;
        $types .= 'i';
    } elseif (!$canViewAllBranches && !empty($currentUser['branch_id'])) {
        $sql .= " AND s.branch_id = ?";
        $params[] = (int)$currentUser['branch_id'];
        $types .= 'i';
    }

    if ($staffId && !$isStaffRole) {
        $sql .= " AND ac.staff_id = ?";
        $params[] = $staffId;
        $types .= 'i';
    }
    if ($status) {
        $sql .= " AND ac.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    if ($year > 0) {
        $sql .= " AND YEAR(ac.attendance_date) = ?";
        $params[] = $year;
        $types .= 'i';
    }
    if ($month) {
        $sql .= " AND DATE_FORMAT(ac.attendance_date, '%Y-%m') = ?";
        $params[] = $month;
        $types .= 's';
    }
    if ($department) {
        $sql .= " AND s.department = ?";
        $params[] = $department;
        $types .= 's';
    }
    if ($search) {
        $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.staff_id LIKE ? OR ac.reason LIKE ?)";
        $like = '%' . $search . '%';
        $params = array_merge($params, [$like, $like, $like, $like]);
        $types .= 'ssss';
    }

    $sql .= " ORDER BY ac.created_at DESC";
    $corrections = fetchAll(executeQuery($sql, $types, $params));

    $statsSql = "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN ac.status = 'Submitted' THEN 1 ELSE 0 END) AS submitted,
        SUM(CASE WHEN ac.status = 'Manager_Approved' THEN 1 ELSE 0 END) AS manager_approved,
        SUM(CASE WHEN ac.status = 'HR_Approved' THEN 1 ELSE 0 END) AS hr_approved,
        SUM(CASE WHEN ac.status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
        FROM hr_attendance_corrections ac
        INNER JOIN staff s ON ac.staff_id = s.id WHERE 1=1";
    $statsParams = [];
    $statsTypes = '';
    if ($isStaffRole || $mine) {
        $statsSql .= " AND ac.staff_id = ?";
        $statsParams[] = $myStaffId;
        $statsTypes .= 'i';
    } elseif (!$canViewAllBranches && !empty($currentUser['branch_id'])) {
        $statsSql .= " AND s.branch_id = ?";
        $statsParams[] = (int)$currentUser['branch_id'];
        $statsTypes .= 'i';
    }
    $stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

    jsonResponse(true, 'OK', ['corrections' => $corrections, 'stats' => $stats]);
}

function handleSubmit($currentUser, $data): void
{
    $canSubmitForOthers = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_attendance', 'create'));

    $myStaffId = resolveStaffId($currentUser);
    $staffId = (int)($data['staff_id'] ?? 0);
    if (!$staffId) {
        $staffId = $myStaffId;
    }

    if (!$canSubmitForOthers && $staffId !== $myStaffId) {
        jsonResponse(false, 'You can only submit corrections for yourself');
    }

    $attendanceDate = sanitize($data['attendance_date'] ?? '');
    $requestedStatus = sanitize($data['requested_status'] ?? 'Present');
    $reason = sanitize($data['reason'] ?? '');
    $checkIn = !empty($data['requested_check_in']) ? sanitize($data['requested_check_in']) : null;
    $checkOut = !empty($data['requested_check_out']) ? sanitize($data['requested_check_out']) : null;

    $allowedStatus = ['Present', 'Absent', 'Late', 'Half Day', 'Leave'];
    if (!in_array($requestedStatus, $allowedStatus, true)) {
        $requestedStatus = 'Present';
    }

    if (!$staffId || !$attendanceDate || $reason === '') {
        jsonResponse(false, 'Employee, date, and reason are required');
    }

    if (strtotime($attendanceDate) > strtotime('today')) {
        jsonResponse(false, 'Cannot request correction for a future date');
    }

    $pending = fetchOne(executeQuery(
        "SELECT id FROM hr_attendance_corrections
         WHERE staff_id = ? AND attendance_date = ? AND status IN ('Submitted','Manager_Approved')",
        'is',
        [$staffId, $attendanceDate]
    ));
    if ($pending) {
        jsonResponse(false, 'A pending correction already exists for this date');
    }

    $attRow = fetchOne(executeQuery(
        "SELECT id, status, check_in, check_out FROM staff_attendance WHERE staff_id = ? AND attendance_date = ?",
        'is',
        [$staffId, $attendanceDate]
    ));

    $attendanceId = $attRow['id'] ?? null;
    $stmt = executeQuery(
        "INSERT INTO hr_attendance_corrections
         (staff_id, attendance_date, attendance_id, requested_check_in, requested_check_out,
          requested_status, reason, status, submitted_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Submitted', ?)",
        'isissssi',
        [$staffId, $attendanceDate, $attendanceId, $checkIn, $checkOut, $requestedStatus, $reason, $currentUser['id']]
    );

    if (!$stmt) {
        global $conn;
        jsonResponse(false, 'Failed to submit correction: ' . ($conn->error ?? ''));
    }

    $staff = fetchOne(executeQuery("SELECT branch_id, first_name, last_name FROM staff WHERE id = ?", 'i', [$staffId]));
    if (class_exists('NotificationService')) {
        NotificationService::notifyHrAdmins(
            $staff['branch_id'] ?? null,
            'Attendance Correction Request',
            ($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '') . ' requested an attendance correction.',
            'hr_attendance'
        );
    }
    logActivity($currentUser['id'], 'Submit Attendance Correction', 'HR', "Staff ID: $staffId, Date: $attendanceDate");
    jsonResponse(true, 'Correction request submitted successfully');
}

function handleApprove($currentUser, $data): void
{
    $canApprove = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_attendance', 'approve'));

    if (!$canApprove) {
        jsonResponse(false, 'Permission denied');
    }

    $id = (int)($data['id'] ?? 0);
    $decision = sanitize($data['decision'] ?? '');
    $rejectionReason = sanitize($data['rejection_reason'] ?? '');

    if (!$id || !in_array($decision, ['manager_approve', 'hr_approve', 'reject'], true)) {
        jsonResponse(false, 'Invalid approval request');
    }

    $correction = fetchOne(executeQuery("SELECT * FROM hr_attendance_corrections WHERE id = ?", 'i', [$id]));
    if (!$correction) {
        jsonResponse(false, 'Correction not found');
    }

    if ($decision === 'manager_approve') {
        if ($correction['status'] !== 'Submitted') {
            jsonResponse(false, 'Correction is not pending manager approval');
        }
        executeQuery(
            "UPDATE hr_attendance_corrections SET status='Manager_Approved',
             manager_approved_by=?, manager_approved_at=NOW() WHERE id=?",
            'ii',
            [$currentUser['id'], $id]
        );
        logActivity($currentUser['id'], 'Manager Approve Correction', 'HR', "Correction ID: $id");
        jsonResponse(true, 'Manager approval recorded — pending HR final approval');
    }

    if ($decision === 'reject') {
        if ($rejectionReason === '') {
            jsonResponse(false, 'Rejection reason is required');
        }
        if (!in_array($correction['status'], ['Submitted', 'Manager_Approved'], true)) {
            jsonResponse(false, 'Correction cannot be rejected in current status');
        }
        executeQuery(
            "UPDATE hr_attendance_corrections SET status='Rejected', rejection_reason=? WHERE id=?",
            'si',
            [$rejectionReason, $id]
        );
        logActivity($currentUser['id'], 'Reject Correction', 'HR', "Correction ID: $id");
        jsonResponse(true, 'Correction rejected');
    }

    if ($decision === 'hr_approve') {
        if ($correction['status'] !== 'Manager_Approved' && $correction['status'] !== 'Submitted') {
            jsonResponse(false, 'Correction is not ready for HR approval');
        }

        executeQuery(
            "UPDATE hr_attendance_corrections SET status='HR_Approved', hr_approved_by=?, hr_approved_at=NOW() WHERE id=?",
            'ii',
            [$currentUser['id'], $id]
        );

        $attId = $correction['attendance_id'];
        if ($attId) {
            executeQuery(
                "UPDATE staff_attendance SET check_in=?, check_out=?, status=?, marked_by=? WHERE id=?",
                'sssii',
                [
                    $correction['requested_check_in'],
                    $correction['requested_check_out'],
                    $correction['requested_status'],
                    $currentUser['id'],
                    $attId,
                ]
            );
            if (class_exists('AttendanceCalculationService')) {
                AttendanceCalculationService::applyToRecord(
                    $attId,
                    $correction['staff_id'],
                    $correction['attendance_date'],
                    $correction['requested_check_in'],
                    $correction['requested_check_out'],
                    $correction['requested_status']
                );
            }
        } else {
            executeQuery(
                "INSERT INTO staff_attendance (staff_id, attendance_date, check_in, check_out, status, marked_by)
                 VALUES (?, ?, ?, ?, ?, ?)",
                'issssi',
                [
                    $correction['staff_id'],
                    $correction['attendance_date'],
                    $correction['requested_check_in'],
                    $correction['requested_check_out'],
                    $correction['requested_status'],
                    $currentUser['id'],
                ]
            );
        }

        if (class_exists('HrAuditService')) {
            HrAuditService::log('HR_Approve Correction', 'attendance_correction', $id, $correction, ['status' => 'HR_Approved']);
        }
        logActivity($currentUser['id'], 'HR Approve Correction', 'HR', "Correction ID: $id");
        jsonResponse(true, 'Correction approved and attendance record updated');
    }
}
