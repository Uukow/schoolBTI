<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])) jsonResponse(false, 'Permission denied');

$id       = (int)($_POST['id'] ?? 0);
$isActive = (int)($_POST['is_active'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$stmt = executeQuery("UPDATE lab_issue_types SET is_active=? WHERE id=?", 'ii', [$isActive ? 1 : 0, $id]);
if ($stmt) {
    jsonResponse(true, 'Issue type ' . ($isActive ? 'activated' : 'deactivated'));
} else {
    jsonResponse(false, 'Failed to update issue type');
}
