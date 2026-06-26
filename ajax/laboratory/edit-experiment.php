<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Teacher'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$data        = $_POST;
$id          = (int)($data['id'] ?? 0);
$title       = sanitize($data['experiment_title'] ?? '');
$code        = sanitize($data['experiment_code'] ?? '');
$category    = sanitize($data['category'] ?? '');
$sectionId   = !empty($data['section_id']) ? (int)$data['section_id'] : null;
$instructorId = !empty($data['instructor_id']) ? (int)$data['instructor_id'] : null;
$description = sanitize($data['description'] ?? '');
$objectives  = sanitize($data['objectives'] ?? '');
$instructions = sanitize($data['instructions'] ?? '');
$safety      = sanitize($data['safety_guidelines'] ?? '');
$materials   = sanitize($data['required_materials'] ?? '');
$equipment   = sanitize($data['required_equipment'] ?? '');
$duration    = !empty($data['duration_hours']) ? (float)$data['duration_hours'] : null;
$difficulty  = sanitize($data['difficulty_level'] ?? 'beginner');
$status      = sanitize($data['status'] ?? 'draft');

if (!$id || empty($title) || empty($code)) {
    jsonResponse(false, 'Experiment ID, title, and code are required');
}

if ($instructorId && !validateLabTeacherId($instructorId)) {
    jsonResponse(false, 'Please select a valid teacher as instructor');
}

$existing = fetchOne(executeQuery("SELECT id FROM lab_experiments WHERE id = ?", 'i', [$id]));
if (!$existing) {
    jsonResponse(false, 'Experiment not found');
}

$duplicate = fetchOne(executeQuery(
    "SELECT id FROM lab_experiments WHERE experiment_code = ? AND id != ?",
    'si',
    [$code, $id]
));
if ($duplicate) {
    jsonResponse(false, 'Experiment code already in use');
}

$sql = "UPDATE lab_experiments SET experiment_code = ?, experiment_title = ?, category = ?, section_id = ?,
        instructor_id = ?, description = ?, objectives = ?, instructions = ?, safety_guidelines = ?,
        required_materials = ?, required_equipment = ?, duration_hours = ?, difficulty_level = ?,
        status = ?, updated_at = NOW() WHERE id = ?";
$stmt = executeQuery($sql, 'sssiiissssssdsi', [
    $code, $title, $category, $sectionId, $instructorId,
    $description, $objectives, $instructions, $safety, $materials, $equipment,
    $duration, $difficulty, $status, $id,
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Edit Experiment', 'Laboratory', "Updated experiment: $title (ID: $id)");
    jsonResponse(true, 'Experiment updated successfully!');
}

jsonResponse(false, 'Failed to update experiment');
