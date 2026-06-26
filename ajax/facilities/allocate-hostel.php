<?php
/**
 * AJAX: Allocate Hostel
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
$roomId = (int)($data['room_id'] ?? 0);
$studentId = (int)($data['student_id'] ?? 0);
$allocationDate = sanitize($data['allocation_date'] ?? date('Y-m-d'));
$monthlyRent = isset($data['monthly_rent']) ? (float)$data['monthly_rent'] : null;

if (empty($hostelId) || empty($roomId) || empty($studentId)) {
    jsonResponse(false, 'Hostel ID, room ID, and student ID are required');
}

// Check if student already has active allocation
$checkSql = "SELECT id FROM hostel_allocations WHERE student_id = ? AND status = 'Active'";
$checkStmt = executeQuery($checkSql, 'i', [$studentId]);
if (fetchOne($checkStmt)) {
    jsonResponse(false, 'Student already has an active hostel allocation');
}

// Check room capacity
$roomSql = "SELECT capacity, (SELECT COUNT(*) FROM hostel_allocations WHERE room_id = ? AND status = 'Active') as occupied FROM hostel_rooms WHERE id = ?";
$roomStmt = executeQuery($roomSql, 'ii', [$roomId, $roomId]);
$room = fetchOne($roomStmt);

if (!$room) {
    jsonResponse(false, 'Room not found');
}

if ($room['occupied'] >= $room['capacity']) {
    jsonResponse(false, 'Room is full');
}

// Insert allocation
$sql = "INSERT INTO hostel_allocations (student_id, hostel_id, room_id, allocation_date, status, fee_amount)
        VALUES (?, ?, ?, ?, 'Active', ?)";

$stmt = executeQuery($sql, 'iiiss', [$studentId, $hostelId, $roomId, $allocationDate, $monthlyRent]);

if ($stmt) {
    // Update room occupied count
    $updateSql = "UPDATE hostel_rooms SET occupied = occupied + 1 WHERE id = ?";
    executeQuery($updateSql, 'i', [$roomId]);
    
    logActivity($currentUser['id'], 'Allocate Hostel', 'Facilities', "Allocated student ID: $studentId to room ID: $roomId");
    jsonResponse(true, 'Hostel allocated successfully!');
} else {
    jsonResponse(false, 'Failed to allocate hostel');
}
