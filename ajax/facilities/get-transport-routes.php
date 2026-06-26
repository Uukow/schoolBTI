<?php
/**
 * AJAX: Get Transport Routes
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
    $sql = "SELECT tr.*, b.branch_name
            FROM transport_routes tr
            LEFT JOIN branches b ON tr.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND tr.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY tr.route_name";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $routes = fetchAll($stmt);
    
    $formatted = [];
    foreach ($routes as $route) {
        $formatted[] = [
            'id' => $route['id'],
            'route_name' => $route['route_name'],
            'route_code' => $route['route_code'],
            'start_location' => $route['start_point'],
            'end_location' => $route['end_point'],
            'distance' => $route['distance'],
            'fare' => $route['fare'],
            'status' => 'Active', // Default
            'description' => $route['stops'],
        ];
    }
    
    jsonResponse(true, 'Routes loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load routes: ' . $e->getMessage());
}

