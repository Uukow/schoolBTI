<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$invoiceId = $_POST['invoice_id'] ?? 0;

if (empty($invoiceId)) jsonResponse(false, 'Invalid invoice ID');

// Get invoice and student details
$sql = "SELECT i.*, s.first_name, s.last_name, s.student_id, 
        p.phone as parent_phone, p.email as parent_email
        FROM fee_invoices i
        INNER JOIN students s ON i.student_id = s.id
        LEFT JOIN student_parents sp ON s.id = sp.student_id AND sp.is_primary = 1
        LEFT JOIN parents p ON sp.parent_id = p.id
        WHERE i.id = ?";

$stmt = executeQuery($sql, 'i', [$invoiceId]);
$data = fetchOne($stmt);

if (!$data) jsonResponse(false, 'Invoice not found');

$message = "Dear Parent, This is a reminder that fee payment of " . formatCurrency($data['due_amount']) . 
           " for " . $data['first_name'] . " " . $data['last_name'] . " (ID: " . $data['student_id'] . 
           ") is pending. Invoice No: " . $data['invoice_no'] . ". Please pay at your earliest convenience. Thank you.";

// Log the reminder (actual SMS/Email sending would be implemented here)
$logSql = "INSERT INTO communication_logs (communication_type, recipient, message, status, sent_by)
           VALUES ('SMS', ?, ?, 'Sent', ?)";
executeQuery($logSql, 'ssi', [$data['parent_phone'] ?? 'N/A', $message, getCurrentUser()['id']]);

logActivity(getCurrentUser()['id'], 'Send Fee Reminder', 'Fees', "Sent reminder for invoice: {$data['invoice_no']}");

jsonResponse(true, 'Reminder sent successfully!');

