<?php
/**
 * AJAX: Delete Transport Route
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$routeId = (int)($_POST['id'] ?? 0);

if (empty($routeId)) jsonResponse(false, 'Invalid route ID');

$sql = "DELETE FROM transport_routes WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$routeId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Route', 'Facilities', "Deleted route ID: $routeId");
    jsonResponse(true, 'Route deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete route');
}

