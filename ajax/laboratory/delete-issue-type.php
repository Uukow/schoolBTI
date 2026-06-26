<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin'])) jsonResponse(false, 'Permission denied');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$stmt = executeQuery("DELETE FROM lab_issue_types WHERE id=?", 'i', [$id]);
if ($stmt) {
    logActivity($currentUser['id'], 'Delete Issue Type', 'Laboratory', "Deleted issue type ID: $id");
    jsonResponse(true, 'Issue type deleted');
} else {
    jsonResponse(false, 'Failed to delete issue type');
}
