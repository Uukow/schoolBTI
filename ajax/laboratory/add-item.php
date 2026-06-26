<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Lab Technician', 'Procurement Officer'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$data = $_POST;

$itemTitle    = sanitize($data['item_title'] ?? '');
$itemCode     = sanitize($data['item_code'] ?? '');
$barcode      = sanitize($data['barcode'] ?? '');
$categoryId   = !empty($data['category_id'])  ? (int)$data['category_id']  : null;
$sectionId    = !empty($data['section_id'])    ? (int)$data['section_id']    : null;
$description  = sanitize($data['description'] ?? '');
$brand        = sanitize($data['brand'] ?? '');
$modelNumber  = sanitize($data['model_number'] ?? '');
$supplier     = sanitize($data['supplier'] ?? '');
$purchaseDate = !empty($data['purchase_date']) ? $data['purchase_date'] : null;
$warrantyExp  = !empty($data['warranty_expiry']) ? $data['warranty_expiry'] : null;
$warrantyInfo = sanitize($data['warranty_info'] ?? '');
$quantity     = max(1, (int)($data['quantity'] ?? 1));
$unitCost     = (float)($data['unit_cost'] ?? 0);
$totalCost    = $quantity * $unitCost;
$status       = sanitize($data['status'] ?? 'available');
$condition    = sanitize($data['condition'] ?? 'new');
$location     = sanitize($data['location'] ?? '');
$notes        = sanitize($data['notes'] ?? '');

if (empty($itemTitle)) jsonResponse(false, 'Item title is required');

if (empty($itemCode)) {
    $itemCode = 'ITEM-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/','',$itemTitle), 0, 4)) . '-' . date('Ymd') . '-' . rand(100, 999);
}

$check = fetchOne(executeQuery("SELECT id FROM lab_inventory_items WHERE item_code=?", 's', [$itemCode]));
if ($check) {
    $itemCode .= '-' . rand(10, 99);
}

$sql = "INSERT INTO lab_inventory_items (item_code, barcode, item_title, category_id, section_id, description, brand,
        model_number, supplier, purchase_date, warranty_expiry, warranty_info, quantity, available_qty,
        unit_cost, total_cost, status, `condition`, location, notes, branch_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'sssiiissssssiiddssssii', [
    $itemCode, $barcode, $itemTitle, $categoryId, $sectionId, $description, $brand,
    $modelNumber, $supplier, $purchaseDate, $warrantyExp, $warrantyInfo,
    $quantity, $quantity, $unitCost, $totalCost, $status, $condition,
    $location, $notes, $currentUser['branch_id'], $currentUser['id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Lab Item', 'Laboratory', "Added item: $itemTitle ($itemCode)");
    jsonResponse(true, 'Inventory item added successfully!');
} else {
    jsonResponse(false, 'Failed to add inventory item');
}
