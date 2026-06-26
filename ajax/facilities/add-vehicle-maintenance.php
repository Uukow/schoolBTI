<?php
/**
 * AJAX: Add Vehicle Maintenance
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$currentUser = null;
$userId = $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
    
    $allowedRoles = ['Super Admin', 'Admin'];
    if (!in_array($currentUser['role_name'], $allowedRoles)) {
        jsonResponse(false, 'Permission denied');
    }
} else {
    if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
    if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');
    $currentUser = getCurrentUser();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

$vehicleId = (int)($data['vehicle_id'] ?? 0);
$maintenanceType = sanitize($data['maintenance_type'] ?? 'Regular Service');
$maintenanceDate = sanitize($data['maintenance_date'] ?? date('Y-m-d'));
$cost = (float)($data['cost'] ?? 0);
$description = sanitize($data['description'] ?? '');
$serviceProvider = sanitize($data['service_provider'] ?? '');
$nextMaintenanceDate = sanitize($data['next_maintenance_date'] ?? null);

if (empty($vehicleId) || $cost <= 0) {
    jsonResponse(false, 'Vehicle ID and cost are required');
}

// Check if vehicle exists
$checkSql = "SELECT id FROM transport_vehicles WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$vehicleId]);
if (!fetchOne($checkStmt)) {
    jsonResponse(false, 'Vehicle not found');
}

$sql = "INSERT INTO vehicle_maintenance (vehicle_id, maintenance_type, maintenance_date, cost, description, performed_by, next_maintenance_date, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'issdsssi', [
    $vehicleId, $maintenanceType, $maintenanceDate, $cost, $description,
    $serviceProvider, $nextMaintenanceDate, $currentUser['id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Vehicle Maintenance', 'Facilities', "Added maintenance for vehicle ID: $vehicleId");
    jsonResponse(true, 'Maintenance record added successfully!');
} else {
    jsonResponse(false, 'Failed to add maintenance record');
}

