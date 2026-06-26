<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician','Maintenance Officer'])) jsonResponse(false, 'Permission denied');

$currentUser  = getCurrentUser();
$id           = (int)($_POST['id'] ?? 0);
$status       = sanitize($_POST['status'] ?? '');
$cost         = (float)($_POST['cost'] ?? 0);
$completedDate= !empty($_POST['completed_date']) ? $_POST['completed_date'] : null;
$resNotes     = sanitize($_POST['resolution_notes'] ?? '');

if (!$id) jsonResponse(false, 'Invalid ID');
$valid = ['reported','assigned','in_progress','completed','closed','cancelled'];
if (!in_array($status, $valid)) jsonResponse(false, 'Invalid status');

$stmt = executeQuery(
    "UPDATE lab_maintenance_records SET status=?, cost=?, completed_date=?, resolution_notes=?, updated_at=NOW() WHERE id=?",
    'sdssi', [$status, $cost, $completedDate, $resNotes, $id]
);

if ($stmt) {
    if ($status === 'completed' || $status === 'closed') {
        $rec = fetchOne(executeQuery("SELECT item_id FROM lab_maintenance_records WHERE id=?", 'i', [$id]));
        if (!empty($rec['item_id'])) {
            executeQuery("UPDATE lab_inventory_items SET status='available' WHERE id=? AND status='under_maintenance'", 'i', [$rec['item_id']]);
        }
    }
    logActivity($currentUser['id'], 'Update Maintenance', 'Laboratory', "Updated maintenance ID: $id to $status");
    jsonResponse(true, 'Maintenance record updated!');
} else {
    jsonResponse(false, 'Failed to update maintenance record');
}
