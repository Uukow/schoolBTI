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

executeQuery("DELETE FROM lab_experiment_sessions WHERE experiment_id=?", 'i', [$id]);
$stmt = executeQuery("DELETE FROM lab_experiments WHERE id=?", 'i', [$id]);

if ($stmt) {
    logActivity($currentUser['id'], 'Delete Experiment', 'Laboratory', "Deleted experiment ID: $id");
    jsonResponse(true, 'Experiment deleted');
} else {
    jsonResponse(false, 'Failed to delete experiment');
}
