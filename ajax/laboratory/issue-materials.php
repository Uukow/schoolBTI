<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician'])) jsonResponse(false, 'Permission denied');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$req = fetchOne(executeQuery("SELECT * FROM lab_material_requests WHERE id=?", 'i', [$id]));
if (!$req) jsonResponse(false, 'Request not found');
if ($req['status'] !== 'approved') jsonResponse(false, 'Only approved requests can be issued');

// Get request items and update inventory
$items = fetchAll(executeQuery("SELECT * FROM lab_request_items WHERE request_id=?", 'i', [$id]));
foreach ($items as $ri) {
    if (empty($ri['item_id'])) continue;
    $qty = (int)$ri['quantity_requested'];
    executeQuery(
        "UPDATE lab_inventory_items SET available_qty = GREATEST(0, available_qty - ?), issued_qty = issued_qty + ? WHERE id=?",
        'iii', [$qty, $qty, $ri['item_id']]
    );
    executeQuery("UPDATE lab_request_items SET quantity_issued=? WHERE id=?", 'ii', [$qty, $ri['id']]);
}

$stmt = executeQuery(
    "UPDATE lab_material_requests SET status='issued', issued_by=?, issued_at=NOW() WHERE id=?",
    'ii', [$currentUser['id'], $id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Issue Materials', 'Laboratory', "Issued materials for request: " . $req['request_number']);
    jsonResponse(true, 'Materials issued successfully!');
} else {
    jsonResponse(false, 'Failed to issue materials');
}
