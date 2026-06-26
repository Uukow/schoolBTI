<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser = getCurrentUser();
$sql = "SELECT a.*, s.first_name, s.last_name, s.staff_id as staff_code
        FROM hr_salary_advances a INNER JOIN staff s ON a.staff_id = s.id WHERE 1=1";
$params = []; $types = '';

if (hasRole(['Teacher', 'Staff']) && !empty($currentUser['staff_id'])) {
    $sql .= " AND a.staff_id = ?";
    $params[] = (int)$currentUser['staff_id'];
    $types .= 'i';
} elseif (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}
$sql .= " ORDER BY a.created_at DESC";
jsonResponse(true, 'OK', fetchAll(executeQuery($sql, $types, $params)));
