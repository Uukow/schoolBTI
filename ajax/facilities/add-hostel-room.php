<?php
/**
 * AJAX: Add Hostel Room
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

$hostelId = (int)($data['hostel_id'] ?? 0);
$roomNumber = sanitize($data['room_number'] ?? '');
$roomType = sanitize($data['room_type'] ?? 'Standard');
$capacity = (int)($data['capacity'] ?? 2);

if (empty($hostelId) || empty($roomNumber) || $capacity <= 0) {
    jsonResponse(false, 'Hostel ID, room number, and capacity are required');
}

// Check if room number already exists in this hostel
$checkSql = "SELECT id FROM hostel_rooms WHERE hostel_id = ? AND room_number = ?";
$checkStmt = executeQuery($checkSql, 'is', [$hostelId, $roomNumber]);
if (fetchOne($checkStmt)) {
    jsonResponse(false, 'Room number already exists in this hostel');
}

$sql = "INSERT INTO hostel_rooms (hostel_id, room_number, room_type, capacity, occupied, is_active)
        VALUES (?, ?, ?, ?, 0, 1)";

$stmt = executeQuery($sql, 'issi', [$hostelId, $roomNumber, $roomType, $capacity]);

if ($stmt) {
    logActivity($currentUser['id'], 'Add Hostel Room', 'Facilities', "Added room: $roomNumber");
    jsonResponse(true, 'Room added successfully!');
} else {
    jsonResponse(false, 'Failed to add room');
}

