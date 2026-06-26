<?php
/**
 * AJAX: Add Transport Route
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

$routeName = sanitize($data['route_name'] ?? '');
$routeCode = sanitize($data['route_code'] ?? '');
$startLocation = sanitize($data['start_location'] ?? '');
$endLocation = sanitize($data['end_location'] ?? '');
$distance = isset($data['distance']) ? (float)$data['distance'] : null;
$fare = isset($data['fare']) ? (float)$data['fare'] : null;
$description = sanitize($data['description'] ?? '');

if (empty($routeName) || empty($startLocation) || empty($endLocation)) {
    jsonResponse(false, 'Route name, start location, and end location are required');
}

// Generate route code if not provided
if (empty($routeCode)) {
    $routeCode = strtoupper(substr($routeName, 0, 3)) . rand(100, 999);
}

// Check if route code already exists
$checkSql = "SELECT id FROM transport_routes WHERE route_code = ?";
$checkStmt = executeQuery($checkSql, 's', [$routeCode]);
if (fetchOne($checkStmt)) {
    $routeCode = $routeCode . rand(10, 99); // Append random number
}

$branchId = $currentUser['branch_id'] ?? null;

$sql = "INSERT INTO transport_routes (route_name, route_code, start_point, end_point, stops, distance, fare, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'sssssddi', [
    $routeName, $routeCode, $startLocation, $endLocation, $description, $distance, $fare, $branchId
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Transport Route', 'Facilities', "Added route: $routeName");
    jsonResponse(true, 'Route added successfully!');
} else {
    jsonResponse(false, 'Failed to add route');
}

