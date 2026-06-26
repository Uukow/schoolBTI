<?php
/**
 * AJAX: Get Vehicle Maintenance
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
    $vehicleId = $_GET['vehicle_id'] ?? null;
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT vm.*, tv.vehicle_number, tv.vehicle_type
            FROM vehicle_maintenance vm
            LEFT JOIN transport_vehicles tv ON vm.vehicle_id = tv.id
            LEFT JOIN branches b ON tv.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($vehicleId) {
        $sql .= " AND vm.vehicle_id = ?";
        $params[] = $vehicleId;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND vm.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND tv.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY vm.maintenance_date DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $maintenance = fetchAll($stmt);
    
    $formatted = [];
    foreach ($maintenance as $m) {
        $formatted[] = [
            'id' => $m['id'],
            'vehicle_id' => $m['vehicle_id'],
            'vehicle_number' => $m['vehicle_number'],
            'maintenance_type' => $m['maintenance_type'],
            'maintenance_date' => $m['maintenance_date'],
            'cost' => $m['cost'],
            'description' => $m['description'],
            'service_provider' => $m['performed_by'], // Using performed_by column
            'odometer_reading' => null, // Not in schema
            'next_maintenance_date' => $m['next_maintenance_date'],
            'status' => 'Completed', // Default status
        ];
    }
    
    jsonResponse(true, 'Maintenance records loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load maintenance records: ' . $e->getMessage());
}

