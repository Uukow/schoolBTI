<?php
/**
 * AJAX: Delete Section
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$sectionId = $_POST['id'] ?? 0;

if (empty($sectionId)) jsonResponse(false, 'Invalid section ID');

// Check if section has students
$checkSql = "SELECT COUNT(*) as count FROM students WHERE current_section_id = ?";
$stmt = executeQuery($checkSql, 'i', [$sectionId]);
$result = fetchOne($stmt);

if ($result['count'] > 0) {
    jsonResponse(false, 'Cannot delete section with active students');
}

$sql = "DELETE FROM sections WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$sectionId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Section', 'Academics', "Deleted section ID: $sectionId");
    jsonResponse(true, 'Section deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete section');
}

