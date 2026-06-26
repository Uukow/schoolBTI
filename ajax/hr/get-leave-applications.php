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
    $isStaffRole = hasRole(['Teacher', 'Staff']);
    $staffRow = fetchOne(executeQuery("SELECT id, branch_id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    $myStaffId = (int)($staffRow['id'] ?? $currentUser['staff_id'] ?? 0);

    $status = sanitize($_GET['status'] ?? '');
    $stage = sanitize($_GET['approval_stage'] ?? '');
    $staffId = (int)($_GET['staff_id'] ?? 0);
    $leaveTypeId = (int)($_GET['leave_type_id'] ?? 0);
    $department = sanitize($_GET['department'] ?? '');
    $year = (int)($_GET['year'] ?? 0);
    $month = sanitize($_GET['month'] ?? '');
    $search = sanitize($_GET['q'] ?? '');
    $mine = !empty($_GET['mine']);

    $sql = "SELECT la.*, s.first_name, s.last_name, s.staff_id AS employee_code, s.designation,
            s.department, s.branch_id, b.branch_name,
            lt.leave_name, lt.leave_code, lt.days_allowed,
            u.username AS approved_by_username,
            TRIM(CONCAT(COALESCE(sa.first_name,''), ' ', COALESCE(sa.last_name,''))) AS approved_by_name,
            mu.username AS manager_username,
            TRIM(CONCAT(COALESCE(ms.first_name,''), ' ', COALESCE(ms.last_name,''))) AS manager_name
            FROM leave_applications la
            INNER JOIN staff s ON la.staff_id = s.id
            INNER JOIN leave_types lt ON la.leave_type_id = lt.id
            LEFT JOIN branches b ON s.branch_id = b.id
            LEFT JOIN users u ON la.approved_by = u.id
            LEFT JOIN staff sa ON sa.user_id = u.id
            LEFT JOIN users mu ON la.manager_approved_by = mu.id
            LEFT JOIN staff ms ON ms.user_id = mu.id
            WHERE 1=1";

    $params = [];
    $types = '';

    if ($isStaffRole || $mine) {
        $sql .= " AND la.staff_id = ?";
        $params[] = $myStaffId;
        $types .= 'i';
    } elseif (!$isSuperAdmin && !empty($currentUser['branch_id'])) {
        $sql .= " AND s.branch_id = ?";
        $params[] = (int)$currentUser['branch_id'];
        $types .= 'i';
    }

    if ($staffId && !$isStaffRole) {
        $sql .= " AND la.staff_id = ?";
        $params[] = $staffId;
        $types .= 'i';
    }
    if ($leaveTypeId) {
        $sql .= " AND la.leave_type_id = ?";
        $params[] = $leaveTypeId;
        $types .= 'i';
    }
    if ($department) {
        $sql .= " AND s.department = ?";
        $params[] = $department;
        $types .= 's';
    }
    if ($status) {
        $sql .= " AND la.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    if ($stage) {
        $sql .= " AND la.approval_stage = ?";
        $params[] = $stage;
        $types .= 's';
    }
    if ($year > 0) {
        $sql .= " AND YEAR(la.start_date) = ?";
        $params[] = $year;
        $types .= 'i';
    }
    if ($month) {
        $sql .= " AND DATE_FORMAT(la.start_date, '%Y-%m') = ?";
        $params[] = $month;
        $types .= 's';
    }
    if ($search) {
        $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.staff_id LIKE ? OR la.reason LIKE ?)";
        $like = '%' . $search . '%';
        $params = array_merge($params, [$like, $like, $like, $like]);
        $types .= 'ssss';
    }

    $sql .= " ORDER BY la.applied_at DESC";

    $applications = fetchAll(executeQuery($sql, $types, $params));

    foreach ($applications as &$app) {
        $app['display_status'] = $app['approval_stage'] ?? $app['status'];
        $yearApp = (int)date('Y', strtotime($app['start_date']));
        $app['balance_remaining'] = LeaveBalanceService::getRemainingDays(
            (int)$app['staff_id'],
            (int)$app['leave_type_id'],
            $yearApp
        );
    }
    unset($app);

    $statsSql = "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN la.approval_stage = 'Pending' AND la.status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN la.approval_stage = 'Manager_Approved' THEN 1 ELSE 0 END) AS manager_approved,
        SUM(CASE WHEN la.approval_stage = 'Approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN la.approval_stage = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN la.approval_stage = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled,
        COALESCE(SUM(CASE WHEN la.approval_stage = 'Approved' THEN la.total_days ELSE 0 END), 0) AS days_approved
        FROM leave_applications la
        INNER JOIN staff s ON la.staff_id = s.id WHERE 1=1";

    $statsParams = [];
    $statsTypes = '';
    if ($isStaffRole || $mine) {
        $statsSql .= " AND la.staff_id = ?";
        $statsParams[] = $myStaffId;
        $statsTypes .= 'i';
    } elseif (!$isSuperAdmin && !empty($currentUser['branch_id'])) {
        $statsSql .= " AND s.branch_id = ?";
        $statsParams[] = (int)$currentUser['branch_id'];
        $statsTypes .= 'i';
    }

    $stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

    jsonResponse(true, 'OK', ['applications' => $applications, 'stats' => $stats]);
} catch (Throwable $e) {
    error_log('get-leave-applications.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
