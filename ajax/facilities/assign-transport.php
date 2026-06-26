<?php
/**
 * AJAX: Assign Transport
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

$routeId = (int)($data['route_id'] ?? 0);
$vehicleId = (int)($data['vehicle_id'] ?? 0);
$studentId = (int)($data['student_id'] ?? 0);
$assignmentDate = sanitize($data['assignment_date'] ?? date('Y-m-d'));
$pickupPoint = sanitize($data['pickup_point'] ?? '');
$dropPoint = sanitize($data['drop_point'] ?? '');

if (empty($routeId) || empty($vehicleId) || empty($studentId)) {
    jsonResponse(false, 'Route ID, vehicle ID, and student ID are required');
}

// Check if student already has active assignment
$checkSql = "SELECT id FROM transport_assignments WHERE student_id = ? AND status = 'Active'";
$checkStmt = executeQuery($checkSql, 'i', [$studentId]);
if (fetchOne($checkStmt)) {
    jsonResponse(false, 'Student already has an active transport assignment');
}

// Check vehicle capacity
$vehicleSql = "SELECT capacity, (SELECT COUNT(*) FROM transport_assignments WHERE vehicle_id = ? AND status = 'Active') as assigned FROM transport_vehicles WHERE id = ?";
$vehicleStmt = executeQuery($vehicleSql, 'ii', [$vehicleId, $vehicleId]);
$vehicle = fetchOne($vehicleStmt);

if (!$vehicle) {
    jsonResponse(false, 'Vehicle not found');
}

if ($vehicle['assigned'] >= $vehicle['capacity']) {
    jsonResponse(false, 'Vehicle is full');
}

// Insert assignment
$sql = "INSERT INTO transport_assignments (student_id, route_id, vehicle_id, assignment_date, status, pickup_point, drop_point)
        VALUES (?, ?, ?, ?, 'Active', ?, ?)";

$stmt = executeQuery($sql, 'iiisss', [
    $studentId, $routeId, $vehicleId, $assignmentDate, $pickupPoint, $dropPoint
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Assign Transport', 'Facilities', "Assigned student ID: $studentId to vehicle ID: $vehicleId");
    jsonResponse(true, 'Transport assigned successfully!');
} else {
    jsonResponse(false, 'Failed to assign transport');
}
