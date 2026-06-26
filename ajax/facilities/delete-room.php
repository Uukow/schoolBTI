<?php
/**
 * AJAX: Delete Hostel Room
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$roomId = (int)($_POST['id'] ?? 0);

if (empty($roomId)) jsonResponse(false, 'Invalid room ID');

$sql = "DELETE FROM hostel_rooms WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$roomId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Room', 'Facilities', "Deleted room ID: $roomId");
    jsonResponse(true, 'Room deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete room');
}

