<?php
/**
 * AJAX: Record Payment
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$paymentId = (int)($_POST['id'] ?? 0);
$paymentDate = $_POST['payment_date'] ?? '';
$paymentMethod = sanitize($_POST['payment_method'] ?? '');

if (empty($paymentId) || empty($paymentDate) || empty($paymentMethod)) {
    jsonResponse(false, 'Payment ID, date, and method are required');
}

// Get payment details including staff and branch information
$paymentSql = "SELECT sp.*, s.first_name, s.last_name, s.staff_id, s.branch_id, b.branch_name
               FROM salary_payments sp
               INNER JOIN staff s ON sp.staff_id = s.id
               LEFT JOIN branches b ON s.branch_id = b.id
               WHERE sp.id = ?";
$paymentStmt = executeQuery($paymentSql, 'i', [$paymentId]);
$payment = fetchOne($paymentStmt);

if (!$payment) {
    jsonResponse(false, 'Payment record not found');
}

// Check if payment was already recorded (to avoid duplicate expense entries)
if (!empty($payment['payment_date'])) {
    jsonResponse(false, 'Payment has already been recorded');
}

// Update payment record
$sql = "UPDATE salary_payments SET payment_date = ?, payment_method = ? WHERE id = ?";
$stmt = executeQuery($sql, 'ssi', [$paymentDate, $paymentMethod, $paymentId]);

if ($stmt) {
    // Automatically create expense entry
    $currentUser = getCurrentUser();
    $branchId = $payment['branch_id'];
    
    // If not super admin, use user's branch
    if (!hasRole(['Super Admin']) && empty($branchId)) {
        $branchId = $currentUser['branch_id'] ?? null;
    }
    
    $expenseCategory = 'Staff Salaries';
    $expenseAmount = (float)$payment['net_salary'];
    $expenseDescription = "Salary payment for " . htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) . 
                         " (Staff ID: " . htmlspecialchars($payment['staff_id']) . ") - " . 
                         date('F Y', strtotime($payment['payment_month']));
    $referenceNo = 'PAY-' . $paymentId;
    
    // Check if expense already exists for this payment
    $checkExpenseSql = "SELECT id FROM expenses WHERE reference_no = ?";
    $checkExpenseStmt = executeQuery($checkExpenseSql, 's', [$referenceNo]);
    $existingExpense = fetchOne($checkExpenseStmt);
    
    if (!$existingExpense) {
        // Create expense entry
        $expenseSql = "INSERT INTO expenses (branch_id, expense_category, amount, expense_date, payment_method, reference_no, description, recorded_by)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $expenseStmt = executeQuery($expenseSql, 'isdssssi', [
            $branchId, 
            $expenseCategory, 
            $expenseAmount, 
            $paymentDate, 
            $paymentMethod, 
            $referenceNo, 
            $expenseDescription, 
            $currentUser['id']
        ]);
        
        if ($expenseStmt) {
            logActivity($currentUser['id'], 'Record Payment & Expense', 'HR', 
                       "Recorded payment and expense for payroll ID: $paymentId - " . formatCurrency($expenseAmount));
        }
    }
    
    logActivity($currentUser['id'], 'Record Payment', 'HR', "Recorded payment for payroll ID: $paymentId");
    jsonResponse(true, 'Payment recorded successfully! Expense entry created automatically.');
} else {
    jsonResponse(false, 'Failed to record payment');
}

