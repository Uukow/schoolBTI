<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician'])) jsonResponse(false, 'Permission denied');

$currentUser   = getCurrentUser();
$id            = (int)($_POST['id'] ?? 0);
$status        = sanitize($_POST['status'] ?? '');
$assignedTo    = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
$resNotes      = sanitize($_POST['resolution_notes'] ?? '');

if (!$id) jsonResponse(false, 'Invalid ID');
$valid = ['open','in_progress','escalated','resolved','closed'];
if (!in_array($status, $valid)) jsonResponse(false, 'Invalid status');

$resolvedAt = in_array($status, ['resolved','closed']) ? 'NOW()' : 'NULL';
$sql = "UPDATE lab_issues SET status=?, assigned_to=?, resolution_notes=?, resolved_at=" . ($status === 'resolved' || $status === 'closed' ? 'NOW()' : 'NULL') . ", updated_at=NOW() WHERE id=?";
$stmt = executeQuery($sql, 'sisi', [$status, $assignedTo, $resNotes, $id]);

if ($stmt) {
    logActivity($currentUser['id'], 'Update Issue', 'Laboratory', "Updated issue $id to $status");
    jsonResponse(true, 'Issue updated successfully!');
} else {
    jsonResponse(false, 'Failed to update issue');
}
