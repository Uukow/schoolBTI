<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Safety Officer'])) jsonResponse(false, 'Permission denied');

$currentUser   = getCurrentUser();
$id            = (int)($_POST['id'] ?? 0);
$status        = sanitize($_POST['status'] ?? '');
$corrective    = sanitize($_POST['corrective_action'] ?? '');

if (!$id || !in_array($status, ['reported','under_investigation','resolved','closed'])) jsonResponse(false, 'Invalid data');

$stmt = executeQuery(
    "UPDATE lab_safety_incidents SET status=?, corrective_action=?, updated_at=NOW() WHERE id=?",
    'ssi', [$status, $corrective, $id]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Update Incident', 'Laboratory', "Incident $id updated to $status");
    jsonResponse(true, 'Incident updated successfully!');
} else {
    jsonResponse(false, 'Failed to update incident');
}
