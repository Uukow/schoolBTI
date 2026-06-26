<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$staffId = (int)($_GET['staff_id'] ?? 0);
$status = sanitize($_GET['status'] ?? '');
$contractType = sanitize($_GET['contract_type'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$sql = "SELECT c.*, s.first_name, s.last_name, s.staff_id as staff_code, s.designation, s.department,
        DATEDIFF(c.end_date, CURDATE()) as days_to_expiry
        FROM hr_contracts c
        INNER JOIN staff s ON c.staff_id = s.id WHERE 1=1";
$params = [];
$types = '';

if ($staffId) {
    $sql .= " AND c.staff_id = ?";
    $params[] = $staffId;
    $types .= 'i';
}
if ($status) {
    $sql .= " AND c.status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($contractType) {
    $sql .= " AND c.contract_type = ?";
    $params[] = $contractType;
    $types .= 's';
}
if ($search) {
    $sql .= " AND (c.contract_no LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.staff_id LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}

$sql .= " ORDER BY c.created_at DESC";
$rows = fetchAll(executeQuery($sql, $types, $params));

$stats = [
    'total' => count($rows),
    'active' => 0,
    'expiring_soon' => 0,
    'expired' => 0,
];
foreach ($rows as $row) {
    if ($row['status'] === 'Active') {
        $stats['active']++;
        if ($row['end_date'] && (int)$row['days_to_expiry'] >= 0 && (int)$row['days_to_expiry'] <= 30) {
            $stats['expiring_soon']++;
        }
        if ($row['end_date'] && (int)$row['days_to_expiry'] < 0) {
            $stats['expired']++;
        }
    }
}

jsonResponse(true, 'OK', ['contracts' => $rows, 'stats' => $stats]);
