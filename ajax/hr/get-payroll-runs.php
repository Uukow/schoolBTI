<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$sql = "SELECT pr.*, b.branch_name, u.username as processed_by_name
        FROM hr_payroll_runs pr
        LEFT JOIN branches b ON pr.branch_id = b.id
        LEFT JOIN users u ON pr.processed_by = u.id
        ORDER BY pr.created_at DESC";
jsonResponse(true, 'OK', fetchAll(executeQuery($sql)));
