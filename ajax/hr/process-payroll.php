<?php
/**
 * AJAX: Process Payroll
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$processType = $_POST['process_type'] ?? 'specific';
$paymentMonth = $_POST['payment_month'] ?? '';
$remarks = sanitize($_POST['remarks'] ?? '');

if (empty($paymentMonth)) {
    jsonResponse(false, 'Payment month is required');
}

$currentUser = getCurrentUser();
$processedBy = $currentUser['id'];

if ($processType === 'all') {
    // Process payroll for all active staff
    $staffSql = "SELECT s.* FROM staff s WHERE s.status = 'Active'";
    $params = [];
    $types = '';
    
    // Branch filter for non-super admins
    if (!hasRole(['Super Admin'])) {
        $staffSql .= " AND s.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $staffSql .= " ORDER BY s.first_name";
    
    $stmt = !empty($params) ? executeQuery($staffSql, $types, $params) : executeQuery($staffSql);
    $allStaff = fetchAll($stmt);
    
    if (empty($allStaff)) {
        jsonResponse(false, 'No active staff found');
    }
    
    $successCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($allStaff as $staff) {
        $staffId = $staff['id'];
        
        // Check if payroll already exists for this month
        $checkSql = "SELECT id FROM salary_payments WHERE staff_id = ? AND DATE_FORMAT(payment_month, '%Y-%m') = ?";
        $checkStmt = executeQuery($checkSql, 'is', [$staffId, $paymentMonth]);
        if (fetchOne($checkStmt)) {
            $skippedCount++;
            continue;
        }
        
        // Get payroll structure for this staff
        $structureSql = "SELECT * FROM payroll_structures 
                        WHERE staff_id = ? 
                        AND effective_from <= ?
                        ORDER BY effective_from DESC 
                        LIMIT 1";
        $structureStmt = executeQuery($structureSql, 'is', [$staffId, $paymentMonth . '-01']);
        $structure = fetchOne($structureStmt);
        
        if (!$structure) {
            $skippedCount++;
            continue;
        }
        
        // Calculate salary components
        $basicSalary = (float)$structure['basic_salary'];
        $allowances = (float)($structure['house_allowance'] + $structure['transport_allowance'] + 
                             $structure['medical_allowance'] + $structure['other_allowances']);
        $deductions = (float)($structure['tax_deduction'] + $structure['other_deductions']);
        $netSalary = $basicSalary + $allowances - $deductions;
        
        // Insert payroll
        $sql = "INSERT INTO salary_payments (staff_id, payment_month, basic_salary, allowances, deductions, net_salary, remarks, processed_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = executeQuery($sql, 'isddddsi', [
            $staffId, $paymentMonth . '-01', $basicSalary, $allowances, $deductions, $netSalary, $remarks, $processedBy
        ]);
        
        if ($insertStmt) {
            $successCount++;
        } else {
            $errorCount++;
            $errors[] = $staff['first_name'] . ' ' . $staff['last_name'];
        }
    }
    
    $message = "Processed payroll for {$successCount} staff member(s).";
    if ($skippedCount > 0) {
        $message .= " {$skippedCount} skipped (already processed or no payroll structure).";
    }
    if ($errorCount > 0) {
        $message .= " {$errorCount} failed: " . implode(', ', array_slice($errors, 0, 5));
    }
    
    logActivity($processedBy, 'Process Payroll (All Staff)', 'HR', "Processed payroll for {$successCount} staff members for {$paymentMonth}");
    jsonResponse(true, $message);
    
} else {
    // Process payroll for specific staff
    $staffId = (int)($_POST['staff_id'] ?? 0);
    $allowances = (float)($_POST['allowances'] ?? 0);
    $deductions = (float)($_POST['deductions'] ?? 0);
    
    if (empty($staffId)) {
        jsonResponse(false, 'Staff is required');
    }
    
    // Get basic salary from payroll structure
    $structureSql = "SELECT basic_salary FROM payroll_structures 
                    WHERE staff_id = ? 
                    ORDER BY effective_from DESC 
                    LIMIT 1";
    $structureStmt = executeQuery($structureSql, 'i', [$staffId]);
    $structure = fetchOne($structureStmt);
    
    if (!$structure) {
        jsonResponse(false, 'Staff does not have a payroll structure. Please add salary first.');
    }
    
    $basicSalary = (float)$structure['basic_salary'];
    $netSalary = $basicSalary + $allowances - $deductions;
    
    // Check if payroll already exists for this month
    $checkSql = "SELECT id FROM salary_payments WHERE staff_id = ? AND DATE_FORMAT(payment_month, '%Y-%m') = ?";
    $stmt = executeQuery($checkSql, 'is', [$staffId, $paymentMonth]);
    if (fetchOne($stmt)) {
        jsonResponse(false, 'Payroll already processed for this month');
    }
    
    $sql = "INSERT INTO salary_payments (staff_id, payment_month, basic_salary, allowances, deductions, net_salary, remarks, processed_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = executeQuery($sql, 'isddddsi', [
        $staffId, $paymentMonth . '-01', $basicSalary, $allowances, $deductions, $netSalary, $remarks, $processedBy
    ]);
    
    if ($stmt) {
        logActivity($processedBy, 'Process Payroll', 'HR', "Processed payroll for staff ID: $staffId");
        jsonResponse(true, 'Payroll processed successfully!');
    } else {
        jsonResponse(false, 'Failed to process payroll');
    }
}

