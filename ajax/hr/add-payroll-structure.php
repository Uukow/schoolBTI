<?php
/**
 * AJAX: Add Payroll Structure
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

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$currentUser = null;
$userId = $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    if ($stmt === false) {
        jsonResponse(false, 'Database error: Failed to retrieve user information');
    }
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID or user not found');
    }
    
    // Check permissions for mobile app
    $allowedRoles = ['Super Admin', 'Admin'];
    $userRole = $currentUser['role_name'] ?? '';
    if (!in_array($userRole, $allowedRoles)) {
        jsonResponse(false, 'Permission denied. Only Super Admin and Admin can add payroll structures.');
    }
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'User not logged in');
    }
    if (!hasRole(['Super Admin', 'Admin'])) {
        jsonResponse(false, 'Permission denied');
    }
    $currentUser = getCurrentUser();
    if (!$currentUser) {
        jsonResponse(false, 'Unable to retrieve user information');
    }
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

$staffId = (int)($data['staff_id'] ?? 0);
$basicSalary = (float)($data['basic_salary'] ?? 0);
$houseAllowance = (float)($data['house_allowance'] ?? 0);
$transportAllowance = (float)($data['transport_allowance'] ?? 0);
$medicalAllowance = (float)($data['medical_allowance'] ?? 0);
$otherAllowances = (float)($data['other_allowances'] ?? 0);
$taxDeduction = (float)($data['tax_deduction'] ?? 0);
$otherDeductions = (float)($data['other_deductions'] ?? 0);
$effectiveFrom = sanitize($data['effective_from'] ?? date('Y-m-d'));

if (empty($staffId) || $basicSalary <= 0) {
    jsonResponse(false, 'Staff ID and basic salary are required');
}

// Check if staff exists
$checkSql = "SELECT id FROM staff WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$staffId]);
if (!fetchOne($checkStmt)) {
    jsonResponse(false, 'Staff not found');
}

$sql = "INSERT INTO payroll_structures (staff_id, basic_salary, house_allowance, transport_allowance, medical_allowance, other_allowances, tax_deduction, other_deductions, effective_from)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Fix: Type string should be 'iddddddds' (9 characters: i for integer, 8 d's for doubles, s for string date)
$stmt = executeQuery($sql, 'iddddddds', [
    $staffId, $basicSalary, $houseAllowance, $transportAllowance, $medicalAllowance,
    $otherAllowances, $taxDeduction, $otherDeductions, $effectiveFrom
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Payroll Structure', 'HR', "Added payroll structure for staff ID: $staffId");
    jsonResponse(true, 'Payroll structure added successfully!');
} else {
    // Get database error for better debugging
    global $conn;
    $errorMsg = $conn ? $conn->error : 'Unknown database error';
    error_log("Failed to add payroll structure. Database error: " . $errorMsg);
    jsonResponse(false, 'Failed to add payroll structure. Please check all fields are valid.');
}

