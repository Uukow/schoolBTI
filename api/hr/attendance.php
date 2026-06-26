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
    $staffId = (int)($_GET['staff_id'] ?? $auth['staff_id'] ?? 0);
    $date = sanitize($_GET['date'] ?? date('Y-m-d'));
    $month = sanitize($_GET['month'] ?? date('Y-m'));

    if (!$staffId && !in_array($auth['role'], ['Super Admin', 'Admin', 'Accountant'])) {
        sendApiResponse(false, 'Staff ID required', null, 400);
    }

    $bf = hrApiBranchFilter($auth);
    if ($staffId) {
        $sql = "SELECT sa.*, s.staff_id as staff_code, s.first_name, s.last_name
                FROM staff_attendance sa INNER JOIN staff s ON sa.staff_id = s.id
                WHERE sa.staff_id = ? AND sa.deleted_at IS NULL" . $bf['sql'];
        $params = array_merge([$staffId], $bf['params']);
        $types = 'i' . $bf['types'];
        if (!empty($_GET['date'])) {
            $sql .= " AND sa.attendance_date = ?";
            $params[] = $date;
            $types .= 's';
        } else {
            $sql .= " AND DATE_FORMAT(sa.attendance_date,'%Y-%m') = ?";
            $params[] = $month;
            $types .= 's';
        }
        $sql .= " ORDER BY sa.attendance_date DESC";
    } else {
        $sql = "SELECT sa.*, s.staff_id as staff_code, s.first_name, s.last_name
                FROM staff_attendance sa INNER JOIN staff s ON sa.staff_id = s.id
                WHERE sa.attendance_date = ? AND sa.deleted_at IS NULL" . $bf['sql'] . " ORDER BY s.first_name";
        $params = array_merge([$date], $bf['params']);
        $types = 's' . $bf['types'];
    }
    $rows = fetchAll(executeQuery($sql, $types, $params));
    sendApiResponse(true, 'Attendance records', $rows);
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}
