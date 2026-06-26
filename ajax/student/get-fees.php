<?php
/**
 * Get Student Fees
 * 
 * Returns fee records for the logged-in student
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

    // Get fee records
    // Note: Use fee_invoices and fee_invoice_items tables, not student_fees (which doesn't exist)
    $currentSession = getCurrentSession();
    $sessionId = $currentSession ? $currentSession['id'] : null;
    
    if (!$sessionId) {
        jsonResponse(false, 'No active academic session found');
        exit;
    }
    
    // Get fee invoices with their items
    $feesSql = "SELECT 
        fi.id,
        fi.invoice_no,
        fi.total_amount as amount,
        fi.paid_amount,
        fi.discount,
        fi.due_date,
        fi.status,
        fi.created_at,
        GROUP_CONCAT(
            CONCAT(ft.fee_name, ' (', fii.amount, ')')
            SEPARATOR ', '
        ) as fee_types
        FROM fee_invoices fi
        LEFT JOIN fee_invoice_items fii ON fi.id = fii.invoice_id
        LEFT JOIN fee_types ft ON fii.fee_type_id = ft.id
        WHERE fi.student_id = ? AND fi.session_id = ?
        GROUP BY fi.id, fi.invoice_no, fi.total_amount, fi.paid_amount, fi.discount, fi.due_date, fi.status, fi.created_at
        ORDER BY fi.due_date DESC, fi.created_at DESC";
    $stmt = executeQuery($feesSql, 'ii', [$studentId, $sessionId]);
    $fees = fetchAll($stmt);

    $response = [];
    foreach ($fees as $fee) {
        // Calculate discount amount
        $discountAmount = $fee['discount'] ?? 0.0;
        
        $response[] = [
            'id' => $fee['id'],
            'fee_type' => $fee['fee_types'] ?? 'Various Fees',
            'amount' => floatval($fee['amount']),
            'paid_amount' => $fee['paid_amount'] !== null ? floatval($fee['paid_amount']) : null,
            'discount_amount' => $discountAmount > 0 ? floatval($discountAmount) : null,
            'due_date' => $fee['due_date'],
            'status' => $fee['status'],
            'paid_date' => null, // fee_invoices doesn't have paid_date, would need to get from fee_payments
        ];
    }

    jsonResponse(true, 'Fees retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

