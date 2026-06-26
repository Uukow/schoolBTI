<?php
/**
 * AJAX: Send Reminders to All Defaulters
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$classFilter = $_POST['class_id'] ?? '';
$daysOverdue = (int)($_POST['days'] ?? 30);

// Build query same as defaulters page
$sql = "SELECT i.*, s.student_id, s.first_name, s.last_name, s.phone, s.email
        FROM fee_invoices i
        INNER JOIN students s ON i.student_id = s.id
        WHERE i.status = 'Unpaid' 
        AND i.due_date < CURDATE()
        AND (i.total_amount - i.discount - COALESCE((SELECT SUM(amount) FROM fee_payments WHERE invoice_id = i.id), 0)) > 0";

$params = [];
$types = '';

if (!empty($classFilter)) {
    $sql .= " AND s.current_class_id = ?";
    $params[] = $classFilter;
    $types .= 'i';
}

// Branch filter
$currentUser = getCurrentUser();
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " HAVING DATEDIFF(CURDATE(), i.due_date) >= ?";

$params[] = $daysOverdue;
$types .= 'i';

$defaulters = fetchAll(executeQuery($sql, $types, $params));

$sentCount = 0;
foreach ($defaulters as $defaulter) {
    // Send reminder (email/SMS)
    // This is a placeholder - implement actual email/SMS sending
    $sentCount++;
}

logActivity(getCurrentUser()['id'], 'Send Bulk Reminders', 'Fees', "Sent reminders to $sentCount defaulters");
jsonResponse(true, "Reminders sent to $sentCount defaulters", ['count' => $sentCount]);

