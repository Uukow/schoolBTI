<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician','Maintenance Officer'])) jsonResponse(false, 'Permission denied');

$currentUser  = getCurrentUser();
$data         = $_POST;
$itemId       = !empty($data['item_id'])    ? (int)$data['item_id']    : null;
$sectionId    = !empty($data['section_id']) ? (int)$data['section_id'] : null;
$type         = sanitize($data['maintenance_type'] ?? 'repair');
$damagecat    = sanitize($data['damage_category'] ?? '');
$severity     = sanitize($data['severity'] ?? 'medium');
$description  = sanitize($data['description'] ?? '');
$responsible  = sanitize($data['responsible_user'] ?? '');
$investigation= sanitize($data['investigation_notes'] ?? '');
$techId       = !empty($data['assigned_technician']) ? (int)$data['assigned_technician'] : null;
$provider     = sanitize($data['service_provider'] ?? '');
$cost         = (float)($data['cost'] ?? 0);
$scheduled    = !empty($data['scheduled_date']) ? $data['scheduled_date'] : null;
$status       = sanitize($data['status'] ?? 'reported');

if (empty($description)) jsonResponse(false, 'Description is required');

$cnt = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_maintenance_records"))['c'] ?? 0;
$refNum = 'MNT-' . date('Ymd') . '-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);

$sql = "INSERT INTO lab_maintenance_records (maintenance_number, item_id, section_id, maintenance_type, damage_category,
        severity, description, responsible_user, investigation_notes, assigned_technician, service_provider,
        cost, scheduled_date, status, reported_by, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'siiisssssssdssii', [
    $refNum, $itemId, $sectionId, $type, $damagecat,
    $severity, $description, $responsible, $investigation, $techId, $provider,
    $cost, $scheduled, $status, $currentUser['id'], $currentUser['branch_id']
]);

if ($stmt) {
    if ($itemId && in_array($status, ['reported','assigned','in_progress'])) {
        executeQuery("UPDATE lab_inventory_items SET status='under_maintenance' WHERE id=?", 'i', [$itemId]);
    }
    logActivity($currentUser['id'], 'Log Maintenance', 'Laboratory', "Logged maintenance: $refNum");
    jsonResponse(true, 'Maintenance record logged successfully!');
} else {
    jsonResponse(false, 'Failed to log maintenance');
}
