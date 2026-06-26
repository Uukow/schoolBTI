<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])) jsonResponse(false, 'Permission denied');

$id     = (int)($_POST['id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');
if (!$id || !in_array($status, ['scheduled','in_progress','completed','cancelled'])) jsonResponse(false, 'Invalid data');

$currentUser = getCurrentUser();
$stmt = executeQuery("UPDATE lab_experiment_sessions SET status=? WHERE id=?", 'si', [$status, $id]);

if ($stmt) {
    logActivity($currentUser['id'], 'Update Session', 'Laboratory', "Session $id status changed to $status");
    jsonResponse(true, 'Session updated to ' . str_replace('_', ' ', $status));
} else {
    jsonResponse(false, 'Failed to update session');
}
