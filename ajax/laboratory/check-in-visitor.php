<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');
$currentUser = getCurrentUser();

$stmt = executeQuery("UPDATE lab_visitors SET status='checked_in', entry_time=NOW() WHERE id=?", 'i', [$id]);
if ($stmt) {
    logActivity($currentUser['id'], 'Check In Visitor', 'Laboratory', "Visitor ID $id checked in");
    jsonResponse(true, 'Visitor checked in successfully');
} else {
    jsonResponse(false, 'Failed to check in visitor');
}
