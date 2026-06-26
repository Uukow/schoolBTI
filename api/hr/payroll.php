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
    $staffId = (int)($_GET['staff_id'] ?? 0);
    $month = sanitize($_GET['month'] ?? '');

    if ($staffId && $auth['staff_id'] && $staffId !== $auth['staff_id']
        && !in_array($auth['role'], ['Super Admin', 'Admin', 'Accountant'])) {
        sendApiResponse(false, 'Access denied', null, 403);
    }
    if (!$staffId && $auth['staff_id'] && !in_array($auth['role'], ['Super Admin', 'Admin', 'Accountant'])) {
        $staffId = $auth['staff_id'];
    }

    $bf = hrApiBranchFilter($auth);
    $sql = "SELECT sp.*, s.staff_id as staff_code, s.first_name, s.last_name
            FROM salary_payments sp INNER JOIN staff s ON sp.staff_id = s.id WHERE 1=1" . $bf['sql'];
    $params = $bf['params'];
    $types = $bf['types'];
    if ($staffId) {
        $sql .= " AND sp.staff_id = ?";
        $params[] = $staffId;
        $types .= 'i';
    }
    if ($month) {
        $sql .= " AND DATE_FORMAT(sp.payment_month,'%Y-%m') = ?";
        $params[] = $month;
        $types .= 's';
    }
    $sql .= " ORDER BY sp.payment_month DESC";
    $rows = fetchAll(executeQuery($sql, $types, $params));
    sendApiResponse(true, 'Salary payments', $rows);
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}
