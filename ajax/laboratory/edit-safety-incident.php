<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Safety Officer'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$data        = $_POST;
$id          = (int)($data['id'] ?? 0);
$type        = sanitize($data['incident_type'] ?? 'accident');
$date        = sanitize($data['incident_date'] ?? '');
$time        = sanitize($data['incident_time'] ?? '');
$description = sanitize($data['description'] ?? '');
$sectionId   = !empty($data['section_id']) ? (int)$data['section_id'] : null;
$location    = sanitize($data['location'] ?? '');
$injured     = sanitize($data['injured_person'] ?? '');
$severity    = sanitize($data['severity'] ?? 'minor');
$treatment   = sanitize($data['treatment_given'] ?? '');
$corrective  = sanitize($data['corrective_action'] ?? '');
$status      = sanitize($data['status'] ?? 'reported');

if (!$id || empty($description) || empty($date)) {
    jsonResponse(false, 'Incident ID, date, and description are required');
}

if (!in_array($status, ['reported', 'under_investigation', 'resolved', 'closed'], true)) {
    jsonResponse(false, 'Invalid status');
}

$existing = fetchOne(executeQuery("SELECT id FROM lab_safety_incidents WHERE id = ?", 'i', [$id]));
if (!$existing) {
    jsonResponse(false, 'Incident not found');
}

$sql = "UPDATE lab_safety_incidents SET incident_type = ?, incident_date = ?, incident_time = ?,
        description = ?, section_id = ?, location = ?, injured_person = ?, severity = ?,
        treatment_given = ?, corrective_action = ?, status = ?, updated_at = NOW() WHERE id = ?";
$stmt = executeQuery($sql, 'ssssissssssi', [
    $type, $date, $time ?: null, $description, $sectionId, $location,
    $injured, $severity, $treatment, $corrective, $status, $id,
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Edit Safety Incident', 'Laboratory', "Updated incident ID: $id to $status");
    jsonResponse(true, 'Safety incident updated successfully!');
}

jsonResponse(false, 'Failed to update incident');
