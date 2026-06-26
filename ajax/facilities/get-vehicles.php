<?php
/**
 * AJAX: Get Vehicles
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
    
    $sql = "SELECT tv.*, tr.route_name
            FROM transport_vehicles tv
            LEFT JOIN transport_routes tr ON tv.route_id = tr.id
            LEFT JOIN branches b ON tv.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($routeId) {
        $sql .= " AND tv.route_id = ?";
        $params[] = $routeId;
        $types .= 'i';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND tv.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY tv.vehicle_number";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $vehicles = fetchAll($stmt);
    
    $formatted = [];
    foreach ($vehicles as $vehicle) {
        $formatted[] = [
            'id' => $vehicle['id'],
            'vehicle_number' => $vehicle['vehicle_number'],
            'vehicle_type' => $vehicle['vehicle_type'],
            'make' => $vehicle['make'],
            'model' => $vehicle['model'],
            'year' => $vehicle['year'],
            'color' => $vehicle['color'],
            'capacity' => $vehicle['capacity'],
            'driver_name' => $vehicle['driver_name'],
            'driver_phone' => $vehicle['driver_phone'],
            'driver_license' => $vehicle['driver_license'],
            'route_id' => $vehicle['route_id'],
            'route_name' => $vehicle['route_name'],
            'status' => ($vehicle['is_active'] ?? 1) ? 'Active' : 'Inactive',
        ];
    }
    
    jsonResponse(true, 'Vehicles loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load vehicles: ' . $e->getMessage());
}

