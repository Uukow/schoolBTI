<?php
/**
 * AJAX: Get Salary Payments
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
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

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
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    $staffId = $_GET['staff_id'] ?? null;
    $month = $_GET['month'] ?? null;
    
    $sql = "SELECT sp.*, s.first_name, s.last_name
            FROM salary_payments sp
            LEFT JOIN staff s ON sp.staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($staffId) {
        $sql .= " AND sp.staff_id = ?";
        $params[] = $staffId;
        $types .= 'i';
    }
    
    if ($month) {
        $sql .= " AND DATE_FORMAT(sp.payment_month, '%Y-%m') = ?";
        $params[] = $month;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND s.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY sp.payment_month DESC, sp.created_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $payments = fetchAll($stmt);
    
    $formatted = [];
    foreach ($payments as $payment) {
        $formatted[] = [
            'id' => $payment['id'],
            'staff_id' => $payment['staff_id'],
            'staff_name' => trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')),
            'payment_month' => $payment['payment_month'],
            'basic_salary' => $payment['basic_salary'],
            'allowances' => $payment['allowances'] ?? 0,
            'deductions' => $payment['deductions'] ?? 0,
            'net_salary' => $payment['net_salary'],
            'payment_date' => $payment['payment_date'],
            'payment_method' => $payment['payment_method'],
            'remarks' => $payment['remarks'],
            'payslip_path' => $payment['payslip_path'],
            'created_at' => $payment['created_at'],
        ];
    }
    
    jsonResponse(true, 'Salary payments loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load salary payments: ' . $e->getMessage());
}

