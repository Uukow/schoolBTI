<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = (int)($data['id'] ?? 0);
$action = sanitize($data['action'] ?? '');

if ($id > 0 && $action === 'approve') {
    if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');
    executeQuery(
        "UPDATE hr_item_requests SET status='L1_Approved', approved_by=?, approved_at=NOW() WHERE id=?",
        'ii', [$currentUser['id'], $id]
    );
    jsonResponse(true, 'Request approved');
}

if ($id > 0 && $action === 'fulfill') {
    if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');
    executeQuery("UPDATE hr_item_requests SET status='Fulfilled', fulfilled_at=NOW() WHERE id=?", 'i', [$id]);
    jsonResponse(true, 'Request fulfilled');
}

$staffId = (int)($data['staff_id'] ?? $currentUser['staff_id'] ?? 0);
$purpose = sanitize($data['purpose'] ?? '');
$itemDescription = sanitize($data['item_description'] ?? '');
$quantity = (int)($data['quantity_requested'] ?? 1);
$priority = sanitize($data['priority'] ?? 'Normal');
$inventoryItemId = !empty($data['inventory_item_id']) ? (int)$data['inventory_item_id'] : null;

if (!$staffId || empty($purpose) || empty($itemDescription)) jsonResponse(false, 'Required fields missing');

$requestNo = HrNumberService::next('REQ-', 'hr_item_requests', 'request_no');
executeQuery(
    "INSERT INTO hr_item_requests (request_no, staff_id, branch_id, purpose, priority) VALUES (?, ?, ?, ?, ?)",
    'siiss', [$requestNo, $staffId, $currentUser['branch_id'] ?? null, $purpose, $priority]
);
$requestId = getLastInsertId();
executeQuery(
    "INSERT INTO hr_item_request_lines (request_id, inventory_item_id, item_description, quantity_requested)
     VALUES (?, ?, ?, ?)",
    'iisi', [$requestId, $inventoryItemId, $itemDescription, $quantity]
);

NotificationService::notifyHrAdmins($currentUser['branch_id'] ?? null, 'Item Request', "Request $requestNo submitted", 'hr_items');
jsonResponse(true, 'Item request submitted');
