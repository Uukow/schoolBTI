<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Procurement Officer'])) jsonResponse(false, 'Permission denied');

$currentUser  = getCurrentUser();
$data         = $_POST;
$supplier     = sanitize($data['supplier_name']    ?? '');
$contact      = sanitize($data['supplier_contact'] ?? '');
$email        = sanitize($data['supplier_email']   ?? '');
$itemDesc     = sanitize($data['item_description'] ?? '');
$categoryId   = !empty($data['category_id'])  ? (int)$data['category_id']  : null;
$sectionId    = !empty($data['section_id'])    ? (int)$data['section_id']    : null;
$quantity     = max(1, (int)($data['quantity'] ?? 1));
$unitPrice    = (float)($data['unit_price'] ?? 0);
$totalPrice   = (float)($data['total_price'] ?? $quantity * $unitPrice);
$purchaseDate = sanitize($data['purchase_date'] ?? date('Y-m-d'));
$expDelivery  = !empty($data['expected_delivery']) ? $data['expected_delivery'] : null;
$warrantyPeriod = !empty($data['warranty_period']) ? (int)$data['warranty_period'] : null;
$warrantyExpiry = !empty($data['warranty_expiry']) ? $data['warranty_expiry'] : null;
$invoiceNum   = sanitize($data['invoice_number'] ?? '');
$status       = sanitize($data['status'] ?? 'pending');
$notes        = sanitize($data['notes'] ?? '');

if (empty($supplier) || empty($itemDesc)) jsonResponse(false, 'Supplier name and item description are required');

$cnt = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_procurement"))['c'] ?? 0;
$poNum = 'PO-' . date('Ymd') . '-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);

$sql = "INSERT INTO lab_procurement (purchase_number, supplier_name, supplier_contact, supplier_email, item_description,
        category_id, section_id, quantity, unit_price, total_price, purchase_date, expected_delivery,
        warranty_period, warranty_expiry, invoice_number, status, notes, branch_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'sssssiiiddsssisssii', [
    $poNum, $supplier, $contact, $email, $itemDesc,
    $categoryId, $sectionId, $quantity, $unitPrice, $totalPrice,
    $purchaseDate, $expDelivery, $warrantyPeriod, $warrantyExpiry,
    $invoiceNum, $status, $notes, $currentUser['branch_id'], $currentUser['id']
]);

if ($stmt) {
    if ($status === 'received') {
        executeQuery("UPDATE lab_procurement SET approved_by=?, approved_at=NOW(), actual_delivery=? WHERE purchase_number=?",
            'iss', [$currentUser['id'], $purchaseDate, $poNum]);
    }
    logActivity($currentUser['id'], 'Add Procurement', 'Laboratory', "Purchase $poNum from $supplier");
    jsonResponse(true, "Purchase record $poNum added successfully!");
} else {
    jsonResponse(false, 'Failed to save purchase record');
}
