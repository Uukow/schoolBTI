<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser   = getCurrentUser();
$data          = $_POST;
$type          = sanitize($data['incident_type'] ?? 'accident');
$date          = sanitize($data['incident_date'] ?? date('Y-m-d'));
$time          = sanitize($data['incident_time'] ?? '');
$description   = sanitize($data['description'] ?? '');
$sectionId     = !empty($data['section_id'])    ? (int)$data['section_id']    : null;
$location      = sanitize($data['location']     ?? '');
$injured       = sanitize($data['injured_person'] ?? '');
$severity      = sanitize($data['severity']     ?? 'minor');
$treatment     = sanitize($data['treatment_given']   ?? '');
$corrective    = sanitize($data['corrective_action'] ?? '');

if (empty($description)) jsonResponse(false, 'Description is required');

$cnt = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_safety_incidents"))['c'] ?? 0;
$incNum = 'INC-' . date('Ymd') . '-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);

$sql = "INSERT INTO lab_safety_incidents (incident_number, incident_type, incident_date, incident_time,
        description, section_id, location, reported_by, injured_person, severity,
        treatment_given, corrective_action, status, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'reported', ?)";
$stmt = executeQuery($sql, 'ssssisisssssi', [
    $incNum, $type, $date, $time ?: null,
    $description, $sectionId, $location, $currentUser['id'], $injured, $severity,
    $treatment, $corrective, $currentUser['branch_id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Report Safety Incident', 'Laboratory', "Incident reported: $incNum");
    jsonResponse(true, "Safety incident $incNum reported. Please follow up immediately.");
} else {
    jsonResponse(false, 'Failed to report incident');
}
