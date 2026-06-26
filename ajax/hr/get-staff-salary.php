<?php
/**
 * AJAX: Get Staff Salary
 * 
 * Get staff basic salary from payroll structure
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$staffId = (int)($_POST['staff_id'] ?? 0);

if (empty($staffId)) {
    jsonResponse(false, 'Staff ID is required');
}

// Get latest payroll structure for this staff
$sql = "SELECT basic_salary, house_allowance, transport_allowance, medical_allowance, other_allowances,
        tax_deduction, other_deductions
        FROM payroll_structures 
        WHERE staff_id = ? 
        ORDER BY effective_from DESC 
        LIMIT 1";

$stmt = executeQuery($sql, 'i', [$staffId]);
$structure = fetchOne($stmt);

if ($structure) {
    jsonResponse(true, 'Salary found', [
        'basic_salary' => (float)$structure['basic_salary'],
        'house_allowance' => (float)$structure['house_allowance'],
        'transport_allowance' => (float)$structure['transport_allowance'],
        'medical_allowance' => (float)$structure['medical_allowance'],
        'other_allowances' => (float)$structure['other_allowances'],
        'tax_deduction' => (float)$structure['tax_deduction'],
        'other_deductions' => (float)$structure['other_deductions']
    ]);
} else {
    jsonResponse(false, 'No payroll structure found for this staff member');
}

