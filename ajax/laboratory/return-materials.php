<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$req = fetchOne(executeQuery("SELECT * FROM lab_material_requests WHERE id=?", 'i', [$id]));
if (!$req || $req['status'] !== 'issued') jsonResponse(false, 'Only issued requests can be returned');

// Return items to inventory
$items = fetchAll(executeQuery("SELECT * FROM lab_request_items WHERE request_id=?", 'i', [$id]));
foreach ($items as $ri) {
    if (empty($ri['item_id']) || !$ri['quantity_issued']) continue;
    $qty = (int)$ri['quantity_issued'];
    executeQuery(
        "UPDATE lab_inventory_items SET available_qty = available_qty + ?, issued_qty = GREATEST(0, issued_qty - ?) WHERE id=?",
        'iii', [$qty, $qty, $ri['item_id']]
    );
    executeQuery("UPDATE lab_request_items SET quantity_returned=? WHERE id=?", 'ii', [$qty, $ri['id']]);
}

$stmt = executeQuery(
    "UPDATE lab_material_requests SET status='returned', returned_at=NOW() WHERE id=?",
    'i', [$id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Return Materials', 'Laboratory', "Materials returned for request: " . $req['request_number']);
    jsonResponse(true, 'Materials returned successfully!');
} else {
    jsonResponse(false, 'Failed to process return');
}
