<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$id = $_POST['id'] ?? 0;

if (empty($id)) jsonResponse(false, 'Invalid announcement ID');

$sql = "DELETE FROM announcements WHERE id = ? AND created_by = ?";
$stmt = executeQuery($sql, 'ii', [$id, getCurrentUser()['id']]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Announcement', 'Communication', "Deleted announcement ID: $id");
    jsonResponse(true, 'Announcement deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete announcement');
}

