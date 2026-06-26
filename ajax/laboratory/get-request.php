<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$id = (int)($_GET['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');

$row = fetchOne(executeQuery(
    "SELECT r.*, COALESCE(r.requester_name, u.username) as requester_name, s.section_name, a.username as approver_name
     FROM lab_material_requests r
     LEFT JOIN users u ON r.requester_id = u.id
     LEFT JOIN lab_sections s ON r.section_id = s.id
     LEFT JOIN users a ON r.approved_by = a.id
     WHERE r.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Request not found');

$items = fetchAll(executeQuery(
    "SELECT ri.*, i.item_title FROM lab_request_items ri
     LEFT JOIN lab_inventory_items i ON ri.item_id = i.id
     WHERE ri.request_id = ?", 'i', [$id]
));

echo json_encode(['success' => true, 'data' => $row, 'items' => $items]);
