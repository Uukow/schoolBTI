<?php
/**
 * AJAX: Add Transport Route
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$routeCode = sanitize($_POST['route_code'] ?? '');
$routeName = sanitize($_POST['route_name'] ?? '');
$startPoint = sanitize($_POST['start_point'] ?? '');
$endPoint = sanitize($_POST['end_point'] ?? '');
$stops = sanitize($_POST['stops'] ?? '');
$distance = !empty($_POST['distance']) ? (float)$_POST['distance'] : null;
$fare = !empty($_POST['fare']) ? (float)$_POST['fare'] : null;
$branchId = !empty($_POST['branch_id']) ? (int)$_POST['branch_id'] : null;

if (empty($routeCode) || empty($routeName) || empty($startPoint) || empty($endPoint)) {
    jsonResponse(false, 'Route code, name, start point, and end point are required');
}

// If not super admin, use user's branch
if (!hasRole(['Super Admin']) && empty($branchId)) {
    $branchId = getCurrentUser()['branch_id'] ?? null;
}

if (empty($branchId)) {
    jsonResponse(false, 'Branch is required');
}

// Check if route code exists
$checkSql = "SELECT id FROM transport_routes WHERE route_code = ?";
$stmt = executeQuery($checkSql, 's', [$routeCode]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Route code already exists');
}

$sql = "INSERT INTO transport_routes (route_code, route_name, start_point, end_point, stops, distance, fare, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'sssssddi', [
    $routeCode, $routeName, $startPoint, $endPoint, $stops, $distance, $fare, $branchId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Route', 'Facilities', "Added route: $routeName ($routeCode)");
    jsonResponse(true, 'Route added successfully!');
} else {
    jsonResponse(false, 'Failed to add route');
}

