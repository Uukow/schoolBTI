<?php
/**
 * AJAX: Get Transport Assignments
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
    $routeId = $_GET['route_id'] ?? null;
    $vehicleId = $_GET['vehicle_id'] ?? null;
    $studentId = $_GET['student_id'] ?? null;
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT ta.*,
            s.first_name, s.last_name, s.admission_no,
            tr.route_name,
            tv.vehicle_number
            FROM transport_assignments ta
            LEFT JOIN students s ON ta.student_id = s.id
            LEFT JOIN transport_routes tr ON ta.route_id = tr.id
            LEFT JOIN transport_vehicles tv ON ta.vehicle_id = tv.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($routeId) {
        $sql .= " AND ta.route_id = ?";
        $params[] = $routeId;
        $types .= 'i';
    }
    
    if ($vehicleId) {
        $sql .= " AND ta.vehicle_id = ?";
        $params[] = $vehicleId;
        $types .= 'i';
    }
    
    if ($studentId) {
        $sql .= " AND ta.student_id = ?";
        $params[] = $studentId;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND ta.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND (tr.branch_id = ? OR tv.branch_id = ?)";
        $params[] = $currentUser['branch_id'];
        $params[] = $currentUser['branch_id'];
        $types .= 'ii';
    }
    
    $sql .= " ORDER BY ta.assignment_date DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $assignments = fetchAll($stmt);
    
    $formatted = [];
    foreach ($assignments as $assign) {
        $formatted[] = [
            'id' => $assign['id'],
            'student_id' => $assign['student_id'],
            'student_name' => trim(($assign['first_name'] ?? '') . ' ' . ($assign['last_name'] ?? '')),
            'route_id' => $assign['route_id'],
            'route_name' => $assign['route_name'],
            'vehicle_id' => $assign['vehicle_id'],
            'vehicle_number' => $assign['vehicle_number'],
            'assignment_date' => $assign['assignment_date'],
            'end_date' => null, // Not in schema
            'status' => $assign['status'],
            'monthly_fee' => null, // Not in schema
            'pickup_point' => $assign['pickup_point'],
            'drop_point' => $assign['drop_point'],
        ];
    }
    
    jsonResponse(true, 'Assignments loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load assignments: ' . $e->getMessage());
}

