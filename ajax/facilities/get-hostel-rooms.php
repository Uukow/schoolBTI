<?php
/**
 * AJAX: Get Hostel Rooms
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
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

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
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    $hostelId = $_GET['hostel_id'] ?? null;
    
    $sql = "SELECT hr.*, h.hostel_name,
            (SELECT COUNT(*) FROM hostel_allocations ha WHERE ha.room_id = hr.id AND ha.status = 'Active') as occupied,
            (hr.capacity - (SELECT COUNT(*) FROM hostel_allocations ha WHERE ha.room_id = hr.id AND ha.status = 'Active')) as available
            FROM hostel_rooms hr
            LEFT JOIN hostels h ON hr.hostel_id = h.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($hostelId) {
        $sql .= " AND hr.hostel_id = ?";
        $params[] = $hostelId;
        $types .= 'i';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND h.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY h.hostel_name, hr.room_number";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $rooms = fetchAll($stmt);
    
    $formatted = [];
    foreach ($rooms as $room) {
        $formatted[] = [
            'id' => $room['id'],
            'hostel_id' => $room['hostel_id'],
            'hostel_name' => $room['hostel_name'],
            'room_number' => $room['room_number'],
            'room_type' => $room['room_type'] ?? 'Standard',
            'capacity' => $room['capacity'] ?? 2,
            'occupied' => $room['occupied'] ?? 0,
            'available' => $room['available'] ?? $room['capacity'],
            'status' => ($room['is_active'] ?? 1) ? 'Available' : 'Unavailable',
            'rent_per_month' => null, // Not in schema
            'facilities' => null, // Not in schema
        ];
    }
    
    jsonResponse(true, 'Rooms loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load rooms: ' . $e->getMessage());
}

