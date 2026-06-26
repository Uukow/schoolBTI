<?php
/**
 * AJAX: Get Attendance Rules
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
if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$branchId = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : null;

$sql = "SELECT r.*, b.branch_name, u.username as created_by_name
        FROM hr_attendance_rules r
        LEFT JOIN branches b ON r.branch_id = b.id
        LEFT JOIN users u ON r.created_by = u.id
        WHERE 1=1";
$params = [];
$types = '';

if ($branchId) {
    $sql .= " AND (r.branch_id = ? OR r.branch_id IS NULL)";
    $params[] = $branchId;
    $types .= 'i';
} elseif (!hasRole(['Super Admin'])) {
    $sql .= " AND (r.branch_id = ? OR r.branch_id IS NULL)";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY r.branch_id IS NULL, r.rule_name";

$rules = fetchAll(executeQuery($sql, $types, $params));
jsonResponse(true, 'Attendance rules loaded', $rules);
