<?php
/**
 * AJAX: Get Financial Report
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
    $reportType = $_GET['report_type'] ?? 'Fee Collection';
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    $summary = [];
    $transactions = [];
    $totalIncome = 0;
    $totalExpenses = 0;
    
    try {
        if ($reportType == 'Fee Collection') {
            // Get fee payments
            $sql = "SELECT fp.*, s.first_name, s.last_name, s.admission_number
                    FROM fee_payments fp
                    LEFT JOIN students s ON fp.student_id = s.id
                    WHERE fp.payment_date BETWEEN ? AND ?
                    ORDER BY fp.payment_date DESC";
            $stmt = executeQuery($sql, 'ss', [$startDate, $endDate]);
            $payments = fetchAll($stmt) ?: [];
            
            foreach ($payments as $payment) {
                $totalIncome += $payment['amount'] ?? 0;
                $transactions[] = [
                    'description' => 'Fee Payment - ' . ($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''),
                    'date' => $payment['payment_date'] ?? '',
                    'amount' => $payment['amount'] ?? 0,
                    'type' => 'income',
                ];
            }
            
            $summary = [
                'total_payments' => count($payments),
                'total_amount' => $totalIncome,
            ];
        } elseif ($reportType == 'Income') {
            $sql = "SELECT * FROM income 
                    WHERE income_date BETWEEN ? AND ?
                    ORDER BY income_date DESC";
            $stmt = executeQuery($sql, 'ss', [$startDate, $endDate]);
            $incomes = fetchAll($stmt) ?: [];
            
            foreach ($incomes as $income) {
                $totalIncome += $income['amount'] ?? 0;
                $transactions[] = [
                    'description' => $income['description'] ?? '',
                    'date' => $income['income_date'] ?? '',
                    'amount' => $income['amount'] ?? 0,
                    'type' => 'income',
                ];
            }
        } elseif ($reportType == 'Expenses') {
            $sql = "SELECT * FROM expenses 
                    WHERE expense_date BETWEEN ? AND ?
                    ORDER BY expense_date DESC";
            $stmt = executeQuery($sql, 'ss', [$startDate, $endDate]);
            $expenses = fetchAll($stmt) ?: [];
            
            foreach ($expenses as $expense) {
                $totalExpenses += $expense['amount'] ?? 0;
                $transactions[] = [
                    'description' => $expense['description'] ?? '',
                    'date' => $expense['expense_date'] ?? '',
                    'amount' => $expense['amount'] ?? 0,
                    'type' => 'expense',
                ];
            }
        }
    } catch (Exception $e) {
        // Handle errors gracefully
        $summary = ['message' => 'No financial data available for selected period'];
        $transactions = [];
    }
    
    $balance = $totalIncome - $totalExpenses;
    
    $formatted = [
        'report_type' => $reportType,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'summary' => $summary,
        'transactions' => $transactions,
        'total_income' => $totalIncome,
        'total_expenses' => $totalExpenses,
        'balance' => $balance,
    ];
    
    jsonResponse(true, 'Financial report loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load financial report: ' . $e->getMessage());
}

