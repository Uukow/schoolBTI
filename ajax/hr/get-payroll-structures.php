<?php
/**
 * AJAX: Get Payroll Structures
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
    
    $sql = "SELECT ps.*, s.first_name, s.last_name
            FROM payroll_structures ps
            LEFT JOIN staff s ON ps.staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($staffId) {
        $sql .= " AND ps.staff_id = ?";
        $params[] = $staffId;
        $types .= 'i';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND s.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY ps.effective_from DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $structures = fetchAll($stmt);
    
    $formatted = [];
    foreach ($structures as $struct) {
        $formatted[] = [
            'id' => $struct['id'],
            'staff_id' => $struct['staff_id'],
            'staff_name' => trim(($struct['first_name'] ?? '') . ' ' . ($struct['last_name'] ?? '')),
            'basic_salary' => $struct['basic_salary'],
            'house_allowance' => $struct['house_allowance'] ?? 0,
            'transport_allowance' => $struct['transport_allowance'] ?? 0,
            'medical_allowance' => $struct['medical_allowance'] ?? 0,
            'other_allowances' => $struct['other_allowances'] ?? 0,
            'tax_deduction' => $struct['tax_deduction'] ?? 0,
            'other_deductions' => $struct['other_deductions'] ?? 0,
            'effective_from' => $struct['effective_from'],
            'created_at' => $struct['created_at'],
        ];
    }
    
    jsonResponse(true, 'Payroll structures loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load payroll structures: ' . $e->getMessage());
}

