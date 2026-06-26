<?php
/**
 * AJAX: Delete Timetable Period
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$periodId = $_POST['id'] ?? 0;

if (empty($periodId)) jsonResponse(false, 'Invalid period ID');

// Get timetable period to check class graduation status
$getSql = "SELECT t.*, c.graduation_status 
           FROM timetable t
           INNER JOIN classes c ON t.class_id = c.id
           WHERE t.id = ?";
$period = fetchOne(executeQuery($getSql, 'i', [$periodId]));

if (!$period) {
    jsonResponse(false, 'Timetable period not found');
}

// Check if class is graduated
if (isset($period['graduation_status']) && $period['graduation_status'] === 'Graduated') {
    jsonResponse(false, 'Cannot delete timetable period for a graduated class. All academic operations are disabled for graduated classes.');
}

$sql = "DELETE FROM timetable WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$periodId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Timetable Period', 'Academics', "Deleted period ID: $periodId");
    jsonResponse(true, 'Period removed from timetable');
} else {
    jsonResponse(false, 'Failed to delete period');
}

