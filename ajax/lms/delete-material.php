<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$materialId = $_POST['id'] ?? 0;

if (empty($materialId)) jsonResponse(false, 'Invalid material ID');

// Get material details
$sql = "SELECT * FROM study_materials WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$materialId]);
$material = fetchOne($stmt);

if (!$material) jsonResponse(false, 'Material not found');

// Check permission
if (!hasRole(['Super Admin', 'Admin']) && $material['uploaded_by'] != getCurrentUser()['id']) {
    jsonResponse(false, 'You can only delete your own materials');
}

// Delete file
$filePath = ABSPATH . $material['file_path'];
if (file_exists($filePath)) {
    deleteFile($filePath);
}

// Delete record
$deleteSql = "DELETE FROM study_materials WHERE id = ?";
$stmt = executeQuery($deleteSql, 'i', [$materialId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Study Material', 'LMS', "Deleted: {$material['title']}");
    jsonResponse(true, 'Study material deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete material');
}

