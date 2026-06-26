<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$typeName    = sanitize($_POST['type_name'] ?? '');
$typeCode    = sanitize(strtoupper($_POST['type_code'] ?? ''));
$priority    = sanitize($_POST['priority_level'] ?? 'medium');
$description = sanitize($_POST['description'] ?? '');

if (empty($typeName)) jsonResponse(false, 'Type name is required');

if (empty($typeCode)) {
    $typeCode = 'ISS-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/','',$typeName), 0, 6)) . '-' . rand(100, 999);
}

$check = fetchOne(executeQuery("SELECT id FROM lab_issue_types WHERE type_code=?", 's', [$typeCode]));
if ($check) $typeCode .= '-' . rand(10, 99);

$stmt = executeQuery(
    "INSERT INTO lab_issue_types (type_name, type_code, description, priority_level, is_active, branch_id) VALUES (?, ?, ?, ?, 1, ?)",
    'ssssi', [$typeName, $typeCode, $description, $priority, $currentUser['branch_id']]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Issue Type', 'Laboratory', "Issue type: $typeName");
    jsonResponse(true, 'Issue type added!');
} else {
    jsonResponse(false, 'Failed to add issue type');
}
