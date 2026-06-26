<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Procurement Officer'])) jsonResponse(false, 'Permission denied');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$stmt = executeQuery(
    "UPDATE lab_procurement SET status='received', actual_delivery=CURDATE() WHERE id=? AND status='approved'",
    'i', [$id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Receive Purchase', 'Laboratory', "Marked purchase $id as received");
    jsonResponse(true, 'Purchase marked as received!');
} else {
    jsonResponse(false, 'Failed to mark as received or not approved');
}
