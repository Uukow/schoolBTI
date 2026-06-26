<?php
/**
 * AJAX: Delete Hostel
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$hostelId = (int)($_POST['id'] ?? 0);

if (empty($hostelId)) jsonResponse(false, 'Invalid hostel ID');

$sql = "DELETE FROM hostels WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$hostelId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Hostel', 'Facilities', "Deleted hostel ID: $hostelId");
    jsonResponse(true, 'Hostel deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete hostel');
}

