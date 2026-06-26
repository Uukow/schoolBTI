<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser   = getCurrentUser();
$requesterKey  = sanitize($_POST['requester_key'] ?? '');
$sectionId     = !empty($_POST['section_id'])    ? (int)$_POST['section_id']    : null;
$experimentId  = !empty($_POST['experiment_id']) ? (int)$_POST['experiment_id'] : null;
$requesterType = sanitize($_POST['requester_type'] ?? 'student');
$purpose       = sanitize($_POST['purpose']       ?? '');
$requestDate   = sanitize($_POST['request_date']  ?? date('Y-m-d'));
$requiredDate  = !empty($_POST['required_date'])  ? sanitize($_POST['required_date']) : null;
$notes         = sanitize($_POST['notes']         ?? '');
$itemIds       = $_POST['item_ids']   ?? [];
$quantities    = $_POST['quantities'] ?? [];

$requester = resolveLabRequester($requesterKey);
if (!$requester || empty($requester['requester_name'])) {
    jsonResponse(false, 'Please select a valid requester');
}

if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Lab Technician'])) {
    $allowedKey = getLabRequesterDefaultKey($currentUser);
    if ($requesterKey !== $allowedKey) {
        jsonResponse(false, 'You can only submit requests for yourself');
    }
}

// Generate request number
$countRow = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_material_requests"));
$reqNum   = 'MR-' . date('Ymd') . '-' . str_pad(($countRow['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

$sql = "INSERT INTO lab_material_requests (request_number, requester_id, requester_name, requester_type, section_id, experiment_id, purpose, request_date, required_date, status, notes, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
$stmt = executeQuery($sql, 'sissiiisssi', [
    $reqNum,
    $requester['requester_id'],
    $requester['requester_name'],
    $requesterType,
    $sectionId,
    $experimentId,
    $purpose,
    $requestDate,
    $requiredDate,
    $notes,
    $currentUser['branch_id'],
]);

if (!$stmt) jsonResponse(false, 'Failed to create request');

$requestId = (int)(getDBConnection()->insert_id ?? 0);

if (!$requestId) {
    $row = fetchOne(executeQuery("SELECT id FROM lab_material_requests WHERE request_number=?", 's', [$reqNum]));
    $requestId = $row['id'] ?? 0;
}

foreach ($itemIds as $i => $itemId) {
    if (empty($itemId)) continue;
    $qty = max(1, (int)($quantities[$i] ?? 1));

    $item = fetchOne(executeQuery("SELECT item_title FROM lab_inventory_items WHERE id=?", 'i', [(int)$itemId]));
    $itemName = $item['item_title'] ?? 'Unknown Item';

    executeQuery(
        "INSERT INTO lab_request_items (request_id, item_id, item_name, quantity_requested) VALUES (?, ?, ?, ?)",
        'iisi',
        [$requestId, (int)$itemId, $itemName, $qty]
    );
}

logActivity($currentUser['id'], 'Submit Material Request', 'Laboratory', "Request $reqNum submitted for {$requester['requester_name']}");
jsonResponse(true, "Material request $reqNum submitted successfully!");
