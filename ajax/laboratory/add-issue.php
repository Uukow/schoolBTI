<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser  = getCurrentUser();
$data         = $_POST;
$title        = sanitize($data['title'] ?? '');
$description  = sanitize($data['description'] ?? '');
$priority     = sanitize($data['priority'] ?? 'medium');
$issueTypeId  = !empty($data['issue_type_id']) ? (int)$data['issue_type_id'] : null;
$sectionId    = !empty($data['section_id'])    ? (int)$data['section_id']    : null;
$itemId       = !empty($data['item_id'])       ? (int)$data['item_id']       : null;
$assignedTo   = !empty($data['assigned_to'])   ? (int)$data['assigned_to']   : null;

if (empty($title) || empty($description)) jsonResponse(false, 'Title and description are required');

$cnt = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_issues"))['c'] ?? 0;
$issueNum = 'ISS-' . date('Ymd') . '-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);

$sql = "INSERT INTO lab_issues (issue_number, issue_type_id, title, description, priority, section_id, item_id, reported_by, assigned_to, status, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'open', ?)";
$stmt = executeQuery($sql, 'sisssiiiii', [
    $issueNum, $issueTypeId, $title, $description, $priority,
    $sectionId, $itemId, $currentUser['id'], $assignedTo, $currentUser['branch_id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Report Issue', 'Laboratory', "Reported issue: $issueNum - $title");
    jsonResponse(true, "Issue $issueNum reported successfully!");
} else {
    jsonResponse(false, 'Failed to submit issue');
}
