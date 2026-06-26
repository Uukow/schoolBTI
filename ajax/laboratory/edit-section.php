<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$id          = (int)($_POST['id'] ?? 0);
$sectionName = sanitize($_POST['section_name'] ?? '');
$sectionCode = sanitize(strtoupper($_POST['section_code'] ?? ''));
$supervisorId = !empty($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : null;
$capacity    = (int)($_POST['capacity'] ?? 30);
$status      = sanitize($_POST['status'] ?? 'active');
$location    = sanitize($_POST['location'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if (!$id || empty($sectionName) || empty($sectionCode)) jsonResponse(false, 'Required fields missing');

$check = fetchOne(executeQuery("SELECT id FROM lab_sections WHERE section_code = ? AND id != ?", 'si', [$sectionCode, $id]));
if ($check) jsonResponse(false, 'Section code already in use by another section');

$sql = "UPDATE lab_sections SET section_name=?, section_code=?, description=?, supervisor_id=?, capacity=?, location=?, status=?, updated_at=NOW() WHERE id=?";
$stmt = executeQuery($sql, 'sssiiisi', [
    $sectionName, $sectionCode, $description, $supervisorId, $capacity, $location, $status, $id
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Edit Lab Section', 'Laboratory', "Updated section ID: $id");
    jsonResponse(true, 'Laboratory section updated successfully!');
} else {
    jsonResponse(false, 'Failed to update laboratory section');
}
