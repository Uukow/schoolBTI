<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$data = $_POST;

$title        = sanitize($data['experiment_title'] ?? '');
$code         = sanitize($data['experiment_code']  ?? '');
$category     = sanitize($data['category'] ?? '');
$sectionId    = !empty($data['section_id'])    ? (int)$data['section_id']    : null;
$instructorId = !empty($data['instructor_id']) ? (int)$data['instructor_id'] : null;
$description  = sanitize($data['description'] ?? '');
$objectives   = sanitize($data['objectives']  ?? '');
$instructions = sanitize($data['instructions'] ?? '');
$safety       = sanitize($data['safety_guidelines'] ?? '');
$materials    = sanitize($data['required_materials'] ?? '');
$equipment    = sanitize($data['required_equipment'] ?? '');
$duration     = !empty($data['duration_hours']) ? (float)$data['duration_hours'] : null;
$difficulty   = sanitize($data['difficulty_level'] ?? 'beginner');
$status       = sanitize($data['status'] ?? 'draft');

if (empty($title)) jsonResponse(false, 'Experiment title is required');

if ($instructorId && !validateLabTeacherId($instructorId)) {
    jsonResponse(false, 'Please select a valid teacher as instructor');
}

if (empty($code)) {
    $cnt = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_experiments"))['c'] ?? 0;
    $code = 'EXP-' . date('Y') . '-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);
}

$sql = "INSERT INTO lab_experiments (experiment_code, experiment_title, category, section_id, instructor_id,
        description, objectives, instructions, safety_guidelines, required_materials, required_equipment,
        duration_hours, difficulty_level, status, branch_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'sssiiisssssssdsi', [
    $code, $title, $category, $sectionId, $instructorId,
    $description, $objectives, $instructions, $safety, $materials, $equipment,
    $duration, $difficulty, $status, $currentUser['branch_id'], $currentUser['id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Experiment', 'Laboratory', "Added experiment: $title");
    jsonResponse(true, 'Experiment added successfully!');
} else {
    jsonResponse(false, 'Failed to add experiment');
}
