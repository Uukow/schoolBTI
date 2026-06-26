<?php
/**
 * AJAX: Delete Vehicle Maintenance
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$maintenanceId = (int)($_POST['id'] ?? 0);

if (empty($maintenanceId)) jsonResponse(false, 'Invalid maintenance ID');

$sql = "DELETE FROM vehicle_maintenance WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$maintenanceId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Maintenance', 'Facilities', "Deleted maintenance ID: $maintenanceId");
    jsonResponse(true, 'Maintenance record deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete maintenance record');
}

