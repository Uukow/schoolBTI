<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])) jsonResponse(false, 'Permission denied');

$id     = (int)($_POST['id'] ?? 0);
$reason = sanitize($_POST['rejection_reason'] ?? '');
if (!$id) jsonResponse(false, 'Invalid ID');
if (empty($reason)) jsonResponse(false, 'Rejection reason is required');
$currentUser = getCurrentUser();

$req = fetchOne(executeQuery("SELECT * FROM lab_material_requests WHERE id=?", 'i', [$id]));
if (!$req || $req['status'] !== 'pending') jsonResponse(false, 'Request not found or not pending');

$stmt = executeQuery(
    "UPDATE lab_material_requests SET status='rejected', approved_by=?, approved_at=NOW(), rejection_reason=? WHERE id=?",
    'isi', [$currentUser['id'], $reason, $id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Reject Request', 'Laboratory', "Rejected request: " . $req['request_number']);
    jsonResponse(true, 'Request rejected');
} else {
    jsonResponse(false, 'Failed to reject request');
}
