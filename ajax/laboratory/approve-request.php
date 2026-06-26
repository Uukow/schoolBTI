<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])) jsonResponse(false, 'Permission denied');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$req = fetchOne(executeQuery("SELECT * FROM lab_material_requests WHERE id=?", 'i', [$id]));
if (!$req) jsonResponse(false, 'Request not found');
if ($req['status'] !== 'pending') jsonResponse(false, 'Only pending requests can be approved');

$stmt = executeQuery(
    "UPDATE lab_material_requests SET status='approved', approved_by=?, approved_at=NOW() WHERE id=?",
    'ii', [$currentUser['id'], $id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Approve Request', 'Laboratory', "Approved request: " . $req['request_number']);
    jsonResponse(true, 'Request approved successfully!');
} else {
    jsonResponse(false, 'Failed to approve request');
}
