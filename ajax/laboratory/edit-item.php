<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician','Procurement Officer'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$id       = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');

$itemTitle   = sanitize($_POST['item_title']   ?? '');
$itemCode    = sanitize($_POST['item_code']    ?? '');
$barcode     = sanitize($_POST['barcode']      ?? '');
$categoryId  = !empty($_POST['category_id'])   ? (int)$_POST['category_id']  : null;
$sectionId   = !empty($_POST['section_id'])    ? (int)$_POST['section_id']    : null;
$description = sanitize($_POST['description']  ?? '');
$brand       = sanitize($_POST['brand']        ?? '');
$modelNumber = sanitize($_POST['model_number'] ?? '');
$purchaseDate = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
$warrantyExp  = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
$quantity    = max(1, (int)($_POST['quantity'] ?? 1));
$unitCost    = (float)($_POST['unit_cost'] ?? 0);
$totalCost   = $quantity * $unitCost;
$status      = sanitize($_POST['status']     ?? 'available');
$condition   = sanitize($_POST['condition']  ?? 'good');
$location    = sanitize($_POST['location']   ?? '');
$notes       = sanitize($_POST['notes']      ?? '');

if (empty($itemTitle)) jsonResponse(false, 'Item title is required');

$check = fetchOne(executeQuery("SELECT id FROM lab_inventory_items WHERE item_code=? AND id!=?", 'si', [$itemCode, $id]));
if ($check) jsonResponse(false, 'Item code already used by another item');

$sql = "UPDATE lab_inventory_items SET item_title=?, item_code=?, barcode=?, category_id=?, section_id=?,
        description=?, brand=?, model_number=?, purchase_date=?, warranty_expiry=?, quantity=?,
        unit_cost=?, total_cost=?, status=?, `condition`=?, location=?, notes=?, updated_at=NOW()
        WHERE id=?";
$stmt = executeQuery($sql, 'sssiiissssiddsssi', [
    $itemTitle, $itemCode, $barcode, $categoryId, $sectionId,
    $description, $brand, $modelNumber, $purchaseDate, $warrantyExp,
    $quantity, $unitCost, $totalCost, $status, $condition, $location, $notes, $id
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Edit Lab Item', 'Laboratory', "Updated item ID: $id ($itemTitle)");
    jsonResponse(true, 'Item updated successfully!');
} else {
    jsonResponse(false, 'Failed to update item');
}
