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
    if (!in_array($auth['role'], ['Super Admin', 'Admin', 'Accountant', 'Teacher', 'Staff'])) {
        sendApiResponse(false, 'Permission denied', null, 403);
    }
    $bf = hrApiBranchFilter($auth);
    $status = sanitize($_GET['status'] ?? '');
    $sql = "SELECT s.id, s.staff_id, s.first_name, s.last_name, s.designation, s.department, s.status, s.phone, s.email
            FROM staff s WHERE s.deleted_at IS NULL" . $bf['sql'];
    $params = $bf['params'];
    $types = $bf['types'];
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    $sql .= " ORDER BY s.first_name";
    $rows = fetchAll(executeQuery($sql, $types, $params));
    sendApiResponse(true, 'Staff list', $rows);
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}
