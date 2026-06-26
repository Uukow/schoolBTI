<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser = getCurrentUser();
$sql = "SELECT r.*, s.first_name, s.last_name, s.staff_id as staff_code
        FROM hr_item_requests r INNER JOIN staff s ON r.staff_id = s.id WHERE 1=1";
$params = []; $types = '';
if (!hasRole(['Super Admin', 'Admin'])) {
    if (hasRole(['Teacher', 'Staff'])) {
        $sql .= " AND r.staff_id = ?";
        $params[] = (int)($currentUser['staff_id'] ?? 0);
    } else {
        $sql .= " AND s.branch_id = ?";
        $params[] = $currentUser['branch_id'];
    }
    $types .= 'i';
}
$sql .= " ORDER BY r.created_at DESC";
jsonResponse(true, 'OK', fetchAll(executeQuery($sql, $types, $params)));
