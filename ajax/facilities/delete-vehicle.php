<?php
/**
 * AJAX: Delete Transport Vehicle
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$vehicleId = (int)($_POST['id'] ?? 0);

if (empty($vehicleId)) jsonResponse(false, 'Invalid vehicle ID');

$sql = "DELETE FROM transport_vehicles WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$vehicleId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Vehicle', 'Facilities', "Deleted vehicle ID: $vehicleId");
    jsonResponse(true, 'Vehicle deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete vehicle');
}

