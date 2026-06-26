<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$data = $_POST;

$sectionName  = sanitize($data['section_name'] ?? '');
$sectionCode  = sanitize(strtoupper($data['section_code'] ?? ''));
$supervisorId = !empty($data['supervisor_id']) ? (int)$data['supervisor_id'] : null;
$capacity     = (int)($data['capacity'] ?? 30);
$status       = sanitize($data['status'] ?? 'active');
$location     = sanitize($data['location'] ?? '');
$description  = sanitize($data['description'] ?? '');

if (empty($sectionName) || empty($sectionCode)) jsonResponse(false, 'Section name and code are required');
if (!in_array($status, ['active', 'inactive', 'under_maintenance'])) $status = 'active';

$check = fetchOne(executeQuery("SELECT id FROM lab_sections WHERE section_code = ?", 's', [$sectionCode]));
if ($check) jsonResponse(false, 'Section code already exists');

$branchId = $currentUser['branch_id'];

$sql = "INSERT INTO lab_sections (section_name, section_code, description, supervisor_id, capacity, location, status, branch_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'sssiiisii', [
    $sectionName, $sectionCode, $description, $supervisorId, $capacity,
    $location, $status, $branchId, $currentUser['id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Lab Section', 'Laboratory', "Added section: $sectionName ($sectionCode)");
    jsonResponse(true, 'Laboratory section added successfully!');
} else {
    jsonResponse(false, 'Failed to add laboratory section');
}
