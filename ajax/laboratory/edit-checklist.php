<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Safety Officer'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser  = getCurrentUser();
$id           = (int)($_POST['id'] ?? 0);
$name         = sanitize($_POST['checklist_name'] ?? '');
$sectionId    = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;
$date         = sanitize($_POST['inspection_date'] ?? '');
$inspectorId  = !empty($_POST['inspector_id']) ? (int)$_POST['inspector_id'] : null;
$overall      = sanitize($_POST['overall_status'] ?? 'passed');
$remarks      = sanitize($_POST['remarks'] ?? '');
$checkItems   = $_POST['check_items'] ?? [];
$checkResults = $_POST['check_results'] ?? [];

if (!$id || empty($name) || empty($date)) {
    jsonResponse(false, 'Checklist ID, name, and date are required');
}

if ($inspectorId && !validateLabInspectorId($inspectorId)) {
    jsonResponse(false, 'Please select a valid inspector (Teacher or Admin only)');
}

$existing = fetchOne(executeQuery("SELECT id FROM lab_safety_checklists WHERE id = ?", 'i', [$id]));
if (!$existing) {
    jsonResponse(false, 'Checklist not found');
}

$itemsJson = json_encode(array_map(null, $checkItems, $checkResults));

$sql = "UPDATE lab_safety_checklists SET checklist_name = ?, section_id = ?, inspection_date = ?,
        inspector_id = ?, items_checked = ?, overall_status = ?, remarks = ? WHERE id = ?";
$stmt = executeQuery($sql, 'sisisssi', [
    $name, $sectionId, $date, $inspectorId, $itemsJson, $overall, $remarks, $id,
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Edit Safety Checklist', 'Laboratory', "Updated checklist: $name (ID: $id)");
    jsonResponse(true, 'Safety checklist updated successfully!');
}

jsonResponse(false, 'Failed to update checklist');
