<?php
/**
 * AJAX: Delete Syllabus
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$syllabusId = $_POST['id'] ?? 0;

if (empty($syllabusId)) jsonResponse(false, 'Invalid syllabus ID');

// Get syllabus details
$sql = "SELECT * FROM curriculum WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$syllabusId]);
$syllabus = fetchOne($stmt);

if (!$syllabus) jsonResponse(false, 'Syllabus not found');

// Delete file if exists
if ($syllabus['file_path'] && file_exists(ABSPATH . $syllabus['file_path'])) {
    deleteFile(ABSPATH . $syllabus['file_path']);
}

// Delete record
$deleteSql = "DELETE FROM curriculum WHERE id = ?";
$stmt = executeQuery($deleteSql, 'i', [$syllabusId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Syllabus', 'Academics', "Deleted syllabus ID: $syllabusId");
    jsonResponse(true, 'Syllabus deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete syllabus');
}

