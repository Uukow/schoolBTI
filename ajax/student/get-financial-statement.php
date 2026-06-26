<?php
/**
 * Get Student Financial Statement
 * 
 * Returns financial statement with outstanding fees, summary, and ledger entries
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

try {
    // Support both session and user_id parameter authentication
    $userId = null;
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        $_SESSION['user_id'] = $userId;
    } elseif (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } else {
        jsonResponse(false, 'User not logged in');
        exit;
    }

    // Verify user is a student
    $userCheckSql = "SELECT u.id, r.role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($userCheckSql, 'i', [$userId]);
    $user = fetchOne($stmt);

    if (!$user || $user['role_name'] !== 'Student') {
        jsonResponse(false, 'Unauthorized: Student access only');
        exit;
    }

    // Get student ID
    $studentSql = "SELECT id FROM students WHERE user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student) {
        jsonResponse(false, 'Student record not found');
        exit;
    }

    $studentId = $student['id'];

    // Get filters
    $sessionId = $_GET['session_id'] ?? null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;

    // Get academic sessions for dropdown
    $sessionsSql = "SELECT id, session_name, start_date, end_date, is_active 
                     FROM academic_sessions 
                     ORDER BY start_date DESC";
    $stmt = executeQuery($sessionsSql);
    $sessions = fetchAll($stmt);
    
    $sessionsList = [];
    foreach ($sessions as $session) {
        $sessionsList[] = [
            'id' => $session['id'],
            'session_name' => $session['session_name'],
            'start_date' => $session['start_date'],
            'end_date' => $session['end_date'],
            'is_active' => (bool)$session['is_active'],
        ];
    }

    // Always use fee_invoices and fee_payments tables (more reliable)
    // Build WHERE clause for fee_invoices/fee_payments tables
    $ledgerEntries = [];
    $financialSummary = [
        'opening_balance' => 0.0,
        'total_charges' => 0.0,
        'total_receipts' => 0.0,
        'closing_balance' => 0.0,
    ];

    // Calculate from fee_invoices and fee_payments
    // Get outstanding fees (unpaid invoices)
        $outstandingSql = "SELECT 
            fi.id,
            fi.invoice_no,
            fi.total_amount,
            fi.paid_amount,
            fi.discount,
            fi.due_date,
            fi.status,
            fi.created_at,
            sess.session_name,
            sess.id as session_id,
            GROUP_CONCAT(
                CONCAT(ft.fee_name, ' (', fii.amount, ')')
                SEPARATOR ', '
            ) as fee_types
            FROM fee_invoices fi
            LEFT JOIN fee_invoice_items fii ON fi.id = fii.invoice_id
            LEFT JOIN fee_types ft ON fii.fee_type_id = ft.id
            INNER JOIN academic_sessions sess ON fi.session_id = sess.id
            WHERE fi.student_id = ? AND fi.status IN ('Unpaid', 'Partially Paid', 'Overdue')";
        
        $outstandingParams = [$studentId];
        $outstandingTypes = 'i';

        if (!empty($sessionId)) {
            $outstandingSql .= " AND fi.session_id = ?";
            $outstandingParams[] = $sessionId;
            $outstandingTypes .= 'i';
        }

        if (!empty($dateFrom)) {
            $outstandingSql .= " AND DATE(fi.created_at) >= ?";
            $outstandingParams[] = $dateFrom;
            $outstandingTypes .= 's';
        }

        if (!empty($dateTo)) {
            $outstandingSql .= " AND DATE(fi.created_at) <= ?";
            $outstandingParams[] = $dateTo;
            $outstandingTypes .= 's';
        }

        $outstandingSql .= " GROUP BY fi.id, fi.invoice_no, fi.total_amount, fi.paid_amount, fi.discount, fi.due_date, fi.status, fi.created_at, sess.session_name, sess.id
                             ORDER BY fi.due_date ASC";

        $stmt = executeQuery($outstandingSql, $outstandingTypes, $outstandingParams);
        $outstandingFees = fetchAll($stmt);

        // Build WHERE clause for invoices
        $invoiceWhere = ["fi.student_id = ?"];
        $invoiceParams = [$studentId];
        $invoiceTypes = 'i';

        if (!empty($sessionId)) {
            $invoiceWhere[] = "fi.session_id = ?";
            $invoiceParams[] = $sessionId;
            $invoiceTypes .= 'i';
        }

        if (!empty($dateFrom)) {
            $invoiceWhere[] = "DATE(fi.created_at) >= ?";
            $invoiceParams[] = $dateFrom;
            $invoiceTypes .= 's';
        }

        if (!empty($dateTo)) {
            $invoiceWhere[] = "DATE(fi.created_at) <= ?";
            $invoiceParams[] = $dateTo;
            $invoiceTypes .= 's';
        }

        $invoiceWhereClause = implode(' AND ', $invoiceWhere);

        // Build WHERE clause for payments
        $paymentWhere = ["fp.student_id = ?"];
        $paymentParams = [$studentId];
        $paymentTypes = 'i';

        if (!empty($sessionId)) {
            $paymentWhere[] = "fi.session_id = ?";
            $paymentParams[] = $sessionId;
            $paymentTypes .= 'i';
        }

        if (!empty($dateFrom)) {
            $paymentWhere[] = "DATE(fp.payment_date) >= ?";
            $paymentParams[] = $dateFrom;
            $paymentTypes .= 's';
        }

        if (!empty($dateTo)) {
            $paymentWhere[] = "DATE(fp.payment_date) <= ?";
            $paymentParams[] = $dateTo;
            $paymentTypes .= 's';
        }

        $paymentWhereClause = implode(' AND ', $paymentWhere);

        // Get all invoices and payments for ledger
        $allInvoicesSql = "SELECT 
            fi.id,
            fi.created_at as transaction_date,
            'Charge' as transaction_type,
            fi.total_amount as charge,
            0.0 as receipt,
            (fi.total_amount - COALESCE(fi.paid_amount, 0)) as balance,
            CONCAT('Invoice #', fi.invoice_no) as description,
            'invoice' as reference_type,
            fi.id as reference_id,
            sess.session_name,
            sess.id as session_id
            FROM fee_invoices fi
            INNER JOIN academic_sessions sess ON fi.session_id = sess.id
            WHERE $invoiceWhereClause";

        // Get payments
        $paymentsSql = "SELECT 
            fp.id,
            fp.payment_date as transaction_date,
            'Receipt' as transaction_type,
            0.0 as charge,
            fp.amount as receipt,
            NULL as balance,
            CONCAT('Payment #', fp.receipt_no) as description,
            'payment' as reference_type,
            fp.id as reference_id,
            sess.session_name,
            sess.id as session_id
            FROM fee_payments fp
            INNER JOIN fee_invoices fi ON fp.invoice_id = fi.id
            INNER JOIN academic_sessions sess ON fi.session_id = sess.id
            WHERE $paymentWhereClause";

        $stmt = executeQuery($allInvoicesSql, $invoiceTypes, $invoiceParams);
        $invoices = fetchAll($stmt);

        $stmt = executeQuery($paymentsSql, $paymentTypes, $paymentParams);
        $payments = fetchAll($stmt);

        // Combine and sort by date
        $allTransactions = array_merge($invoices, $payments);
        usort($allTransactions, function($a, $b) {
            return strtotime($a['transaction_date']) <=> strtotime($b['transaction_date']);
        });

        // Calculate running balance
        $runningBalance = 0.0;
        foreach ($allTransactions as &$transaction) {
            $runningBalance += floatval($transaction['charge'] ?? 0) - floatval($transaction['receipt'] ?? 0);
            $transaction['balance'] = $runningBalance;
        }
        unset($transaction);

        $ledgerEntries = $allTransactions;

        // Calculate summary
        if (!empty($ledgerEntries)) {
            $firstEntry = $ledgerEntries[0];
            $lastEntry = end($ledgerEntries);
            
            // Opening balance (balance before first transaction)
            $financialSummary['opening_balance'] = floatval($firstEntry['balance'] ?? 0) - floatval($firstEntry['charge'] ?? 0) + floatval($firstEntry['receipt'] ?? 0);

            $totalCharges = 0.0;
            $totalReceipts = 0.0;
            foreach ($ledgerEntries as $entry) {
                $totalCharges += floatval($entry['charge'] ?? 0);
                $totalReceipts += floatval($entry['receipt'] ?? 0);
            }
            
            $financialSummary['total_charges'] = $totalCharges;
            $financialSummary['total_receipts'] = $totalReceipts;
            $financialSummary['closing_balance'] = floatval($lastEntry['balance'] ?? 0);
        }

        // Format outstanding fees
        $outstandingFeesFormatted = [];
        foreach ($outstandingFees as $fee) {
            $outstandingFeesFormatted[] = [
                'id' => $fee['id'],
                'invoice_no' => $fee['invoice_no'],
                'fee_types' => $fee['fee_types'] ?? 'Various Fees',
                'total_amount' => floatval($fee['total_amount']),
                'paid_amount' => floatval($fee['paid_amount'] ?? 0),
                'discount' => floatval($fee['discount'] ?? 0),
                'due_amount' => floatval($fee['total_amount']) - floatval($fee['paid_amount'] ?? 0) - floatval($fee['discount'] ?? 0),
                'due_date' => $fee['due_date'],
                'status' => $fee['status'],
                'created_at' => $fee['created_at'],
                'session_name' => $fee['session_name'],
            ];
        }

    // Format ledger entries
    $ledgerFormatted = [];
    foreach ($ledgerEntries as $entry) {
        $ledgerFormatted[] = [
            'id' => $entry['id'],
            'transaction_date' => $entry['transaction_date'],
            'transaction_type' => $entry['transaction_type'],
            'charge' => floatval($entry['charge'] ?? 0),
            'receipt' => floatval($entry['receipt'] ?? 0),
            'balance' => isset($entry['balance']) ? floatval($entry['balance']) : null,
            'description' => $entry['description'],
            'reference_type' => $entry['reference_type'] ?? null,
            'reference_id' => $entry['reference_id'] ?? null,
            'session_name' => $entry['session_name'] ?? null,
        ];
    }

    $response = [
        'sessions' => $sessionsList,
        'outstanding_fees' => isset($outstandingFeesFormatted) ? $outstandingFeesFormatted : [],
        'financial_summary' => $financialSummary,
        'financial_statement' => $ledgerFormatted,
    ];

    jsonResponse(true, 'Financial statement retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

