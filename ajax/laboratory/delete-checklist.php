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

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');

$currentUser = getCurrentUser();
$row = fetchOne(executeQuery("SELECT id, checklist_name FROM lab_safety_checklists WHERE id = ?", 'i', [$id]));
if (!$row) jsonResponse(false, 'Checklist not found');

$stmt = executeQuery("DELETE FROM lab_safety_checklists WHERE id = ?", 'i', [$id]);
if ($stmt) {
    logActivity($currentUser['id'], 'Delete Safety Checklist', 'Laboratory', "Deleted checklist: {$row['checklist_name']} (ID: $id)");
    jsonResponse(true, 'Safety checklist deleted successfully');
}

jsonResponse(false, 'Failed to delete checklist');
