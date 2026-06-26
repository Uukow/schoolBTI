<?php
/**
 * API Student Fees Endpoint
 * 
 * Retrieves fee information for a student
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get user from request
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    sendApiResponse(false, 'User ID is required', null, 400);
}

// Fetch user
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);
$user = fetchOne($stmt);

if (!$user) {
    sendApiResponse(false, 'User not found', null, 404);
}

if ($user['role_name'] !== 'Student') {
    sendApiResponse(false, 'Access denied. Student role required.', null, 403);
}

// Get student record
$student = getStudentByUserId($userId);
if (!$student) {
    sendApiResponse(false, 'Student record not found', null, 404);
}

$studentId = $student['id'];

// Get fee summary
$summaryData = [];

// Total fees from invoices
$sql = "SELECT 
        COALESCE(SUM(total_amount), 0) as total_fees,
        COALESCE(SUM(paid_amount), 0) as paid_amount,
        COALESCE(SUM(due_amount), 0) as due_amount,
        COALESCE(SUM(discount), 0) as discount_amount,
        COUNT(*) as total_invoices,
        SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid_invoices,
        SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue_invoices
        FROM fee_invoices 
        WHERE student_id = ?";
$stmt = executeQuery($sql, 'i', [$studentId]);
$summary = fetchOne($stmt);

$summaryData = [
    'total_fees' => $summary['total_fees'] ?? 0,
    'paid_amount' => $summary['paid_amount'] ?? 0,
    'due_amount' => $summary['due_amount'] ?? 0,
    'discount_amount' => $summary['discount_amount'] ?? 0,
    'total_invoices' => $summary['total_invoices'] ?? 0,
    'paid_invoices' => $summary['paid_invoices'] ?? 0,
    'overdue_invoices' => $summary['overdue_invoices'] ?? 0,
];

// Get recent invoices
$sql = "SELECT * FROM fee_invoices 
        WHERE student_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10";
$stmt = executeQuery($sql, 'i', [$studentId]);
$summaryData['recent_invoices'] = fetchAll($stmt);

// Get recent payments
$sql = "SELECT fp.*, fi.invoice_no 
        FROM fee_payments fp
        LEFT JOIN fee_invoices fi ON fp.invoice_id = fi.id
        WHERE fp.student_id = ? 
        ORDER BY fp.payment_date DESC 
        LIMIT 10";
$stmt = executeQuery($sql, 'i', [$studentId]);
$summaryData['recent_payments'] = fetchAll($stmt);

sendApiResponse(true, 'Fee information retrieved successfully', $summaryData);














