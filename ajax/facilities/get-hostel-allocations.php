<?php
/**
 * AJAX: Get Hostel Allocations
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
    $studentId = $_GET['student_id'] ?? null;
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT ha.*, 
            s.first_name, s.last_name, s.admission_no,
            h.hostel_name,
            hr.room_number
            FROM hostel_allocations ha
            LEFT JOIN students s ON ha.student_id = s.id
            LEFT JOIN hostels h ON ha.hostel_id = h.id
            LEFT JOIN hostel_rooms hr ON ha.room_id = hr.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($hostelId) {
        $sql .= " AND ha.hostel_id = ?";
        $params[] = $hostelId;
        $types .= 'i';
    }
    
    if ($studentId) {
        $sql .= " AND ha.student_id = ?";
        $params[] = $studentId;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND ha.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND h.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY ha.allocation_date DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $allocations = fetchAll($stmt);
    
    $formatted = [];
    foreach ($allocations as $alloc) {
        $formatted[] = [
            'id' => $alloc['id'],
            'student_id' => $alloc['student_id'],
            'student_name' => trim(($alloc['first_name'] ?? '') . ' ' . ($alloc['last_name'] ?? '')),
            'hostel_id' => $alloc['hostel_id'],
            'hostel_name' => $alloc['hostel_name'],
            'room_id' => $alloc['room_id'],
            'room_number' => $alloc['room_number'],
            'allocation_date' => $alloc['allocation_date'],
            'deallocation_date' => $alloc['vacation_date'],
            'status' => $alloc['status'],
            'monthly_rent' => $alloc['fee_amount'],
        ];
    }
    
    jsonResponse(true, 'Allocations loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load allocations: ' . $e->getMessage());
}

