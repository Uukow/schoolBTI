<?php
/**
 * AJAX: Add Vehicle Maintenance
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$vehicleId = (int)($_POST['vehicle_id'] ?? 0);
$maintenanceType = sanitize($_POST['maintenance_type'] ?? '');
$maintenanceDate = $_POST['maintenance_date'] ?? date('Y-m-d');
$cost = !empty($_POST['cost']) ? (float)$_POST['cost'] : null;
$performedBy = sanitize($_POST['performed_by'] ?? '');
$nextMaintenanceDate = $_POST['next_maintenance_date'] ?? null;
$description = sanitize($_POST['description'] ?? '');

if (empty($vehicleId) || empty($maintenanceType)) {
    jsonResponse(false, 'Vehicle and maintenance type are required');
}

$sql = "INSERT INTO vehicle_maintenance (vehicle_id, maintenance_type, maintenance_date, cost, performed_by, next_maintenance_date, description, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'issdsssi', [
    $vehicleId, $maintenanceType, $maintenanceDate, $cost, $performedBy, 
    $nextMaintenanceDate, $description, getCurrentUser()['id']
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Maintenance', 'Facilities', "Added maintenance for vehicle ID: $vehicleId");
    jsonResponse(true, 'Maintenance record added successfully!');
} else {
    jsonResponse(false, 'Failed to add maintenance record');
}

