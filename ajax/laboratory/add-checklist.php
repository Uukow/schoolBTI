<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Safety Officer'])) jsonResponse(false, 'Permission denied');

$currentUser  = getCurrentUser();
$name         = sanitize($_POST['checklist_name'] ?? '');
$sectionId    = !empty($_POST['section_id'])    ? (int)$_POST['section_id']    : null;
$date         = sanitize($_POST['inspection_date'] ?? date('Y-m-d'));
$inspectorId  = !empty($_POST['inspector_id'])  ? (int)$_POST['inspector_id']  : null;
$overall      = sanitize($_POST['overall_status'] ?? 'passed');
$remarks      = sanitize($_POST['remarks'] ?? '');
$checkItems   = $_POST['check_items']   ?? [];
$checkResults = $_POST['check_results'] ?? [];

if (empty($name)) jsonResponse(false, 'Checklist name is required');

if ($inspectorId && !validateLabInspectorId($inspectorId)) {
    jsonResponse(false, 'Please select a valid inspector (Teacher or Admin only)');
}

// Encode checklist items as JSON
$itemsJson = json_encode(array_map(null, $checkItems, $checkResults));

$sql = "INSERT INTO lab_safety_checklists (checklist_name, section_id, inspection_date, inspector_id, items_checked, overall_status, remarks, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'sisisssi', [
    $name, $sectionId, $date, $inspectorId, $itemsJson, $overall, $remarks, $currentUser['branch_id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Safety Checklist', 'Laboratory', "Checklist: $name on $date");
    jsonResponse(true, 'Safety checklist saved successfully!');
} else {
    jsonResponse(false, 'Failed to save checklist');
}
