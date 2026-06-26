<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director'])) jsonResponse(false, 'Permission denied');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$stmt = executeQuery(
    "UPDATE lab_procurement SET status='approved', approved_by=?, approved_at=NOW() WHERE id=? AND status='pending'",
    'ii', [$currentUser['id'], $id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Approve Purchase', 'Laboratory', "Approved purchase ID: $id");
    jsonResponse(true, 'Purchase approved!');
} else {
    jsonResponse(false, 'Failed to approve or already approved');
}
