<?php
/**
 * AJAX: Delete Fee Structure
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$structureId = (int)($_POST['id'] ?? 0);

if (empty($structureId)) jsonResponse(false, 'Invalid structure ID');

// Check if invoices exist for this structure
$checkSql = "SELECT COUNT(*) as count FROM fee_invoice_items 
             WHERE fee_type_id = (SELECT fee_type_id FROM fee_structures WHERE id = ?)";
$stmt = executeQuery($checkSql, 'i', [$structureId]);
$result = fetchOne($stmt);

if ($result['count'] > 0) {
    jsonResponse(false, 'Cannot delete - invoices have been generated using this structure');
}

$sql = "DELETE FROM fee_structures WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$structureId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Fee Structure', 'Fees', "Deleted fee structure ID: $structureId");
    jsonResponse(true, 'Fee structure deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete fee structure');
}

