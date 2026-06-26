<?php
/**
 * AJAX: Add Vehicle
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

$vehicleNumber = sanitize($data['vehicle_number'] ?? '');
$vehicleType = sanitize($data['vehicle_type'] ?? 'Bus');
$make = sanitize($data['make'] ?? '');
$model = sanitize($data['model'] ?? '');
$year = isset($data['year']) ? (int)$data['year'] : null;
$color = sanitize($data['color'] ?? '');
$capacity = (int)($data['capacity'] ?? 0);
$driverName = sanitize($data['driver_name'] ?? '');
$driverPhone = sanitize($data['driver_phone'] ?? '');
$driverLicense = sanitize($data['driver_license'] ?? '');
$routeId = isset($data['route_id']) ? (int)$data['route_id'] : null;

if (empty($vehicleNumber) || $capacity <= 0) {
    jsonResponse(false, 'Vehicle number and capacity are required');
}

// Check if vehicle number already exists
$checkSql = "SELECT id FROM transport_vehicles WHERE vehicle_number = ?";
$checkStmt = executeQuery($checkSql, 's', [$vehicleNumber]);
if (fetchOne($checkStmt)) {
    jsonResponse(false, 'Vehicle number already exists');
}

$branchId = $currentUser['branch_id'] ?? null;

$sql = "INSERT INTO transport_vehicles (vehicle_number, vehicle_type, make, model, year, color, capacity, driver_name, driver_phone, driver_license, route_id, branch_id, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

$stmt = executeQuery($sql, 'ssssisissisi', [
    $vehicleNumber, $vehicleType, $make, $model, $year, $color, $capacity,
    $driverName, $driverPhone, $driverLicense, $routeId, $branchId
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Vehicle', 'Facilities', "Added vehicle: $vehicleNumber");
    jsonResponse(true, 'Vehicle added successfully!');
} else {
    jsonResponse(false, 'Failed to add vehicle');
}
