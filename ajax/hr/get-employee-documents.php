<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$staffId = (int)($_GET['staff_id'] ?? 0);
$sql = "SELECT d.*, s.staff_id as staff_code, u.username as verified_by_name FROM hr_employee_documents d
        INNER JOIN staff s ON d.staff_id = s.id
        LEFT JOIN users u ON d.verified_by = u.id WHERE 1=1";
$params = []; $types = '';
if ($staffId) { $sql .= " AND d.staff_id = ?"; $params[] = $staffId; $types .= 'i'; }
$sql .= " ORDER BY d.created_at DESC";
jsonResponse(true, 'OK', fetchAll(executeQuery($sql, $types, $params)));
