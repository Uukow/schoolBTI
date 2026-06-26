<?php
/**
 * AJAX: Add Hostel Room
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$hostelId = (int)($_POST['hostel_id'] ?? 0);
$roomNumber = sanitize($_POST['room_number'] ?? '');
$roomType = sanitize($_POST['room_type'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 2);

if (empty($hostelId) || empty($roomNumber) || $capacity <= 0) {
    jsonResponse(false, 'Hostel, room number, and capacity are required');
}

// Check if room number already exists in this hostel
$checkSql = "SELECT id FROM hostel_rooms WHERE hostel_id = ? AND room_number = ?";
$stmt = executeQuery($checkSql, 'is', [$hostelId, $roomNumber]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Room number already exists in this hostel');
}

$sql = "INSERT INTO hostel_rooms (hostel_id, room_number, room_type, capacity, occupied)
        VALUES (?, ?, ?, ?, 0)";

$stmt = executeQuery($sql, 'issi', [$hostelId, $roomNumber, $roomType, $capacity]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Room', 'Facilities', "Added room: $roomNumber");
    jsonResponse(true, 'Room added successfully!');
} else {
    jsonResponse(false, 'Failed to add room');
}

