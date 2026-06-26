<?php
/**
 * AJAX: Process Salary Payment
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$currentUser = null;
$userId = $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
    
    $allowedRoles = ['Super Admin', 'Admin'];
    if (!in_array($currentUser['role_name'], $allowedRoles)) {
        jsonResponse(false, 'Permission denied');
    }
} else {
    if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
    if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');
    $currentUser = getCurrentUser();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

$staffId = (int)($data['staff_id'] ?? 0);
$paymentMonth = sanitize($data['payment_month'] ?? '');
$basicSalary = (float)($data['basic_salary'] ?? 0);
$allowances = (float)($data['allowances'] ?? 0);
$deductions = (float)($data['deductions'] ?? 0);
$netSalary = (float)($data['net_salary'] ?? 0);
$paymentDate = sanitize($data['payment_date'] ?? null);
$paymentMethod = sanitize($data['payment_method'] ?? 'Bank Transfer');
$remarks = sanitize($data['remarks'] ?? '');

if (empty($staffId) || empty($paymentMonth) || $netSalary <= 0) {
    jsonResponse(false, 'Staff ID, payment month, and net salary are required');
}

// Check if payment already exists for this month
$checkSql = "SELECT id FROM salary_payments WHERE staff_id = ? AND DATE_FORMAT(payment_month, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')";
$checkStmt = executeQuery($checkSql, 'is', [$staffId, $paymentMonth]);
if (fetchOne($checkStmt)) {
    jsonResponse(false, 'Salary payment already processed for this month');
}

$sql = "INSERT INTO salary_payments (staff_id, payment_month, basic_salary, allowances, deductions, net_salary, payment_date, payment_method, remarks, processed_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'isddddsssi', [
    $staffId, $paymentMonth, $basicSalary, $allowances, $deductions, $netSalary,
    $paymentDate, $paymentMethod, $remarks, $currentUser['id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Process Salary Payment', 'HR', "Processed salary for staff ID: $staffId, Month: $paymentMonth");
    jsonResponse(true, 'Salary payment processed successfully!');
} else {
    jsonResponse(false, 'Failed to process salary payment');
}

