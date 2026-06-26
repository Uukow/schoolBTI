<?php
/**
 * AJAX: Delete Attendance Rule
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = (int) ($data['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(false, 'Invalid rule ID');
}

$stmt = executeQuery("DELETE FROM hr_attendance_rules WHERE id = ?", 'i', [$id]);
if ($stmt) {
    logActivity($currentUser['id'], 'Delete Attendance Rule', 'HR', "Rule ID: $id");
    jsonResponse(true, 'Attendance rule deleted');
}

jsonResponse(false, 'Failed to delete rule');
