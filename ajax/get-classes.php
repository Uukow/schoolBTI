<?php
/**
 * AJAX: Get All Active Classes
 * 
 * Fetch all active classes (excluding graduated classes)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Start output buffering to catch any unwanted output
ob_start();

// Suppress error display for clean JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/config.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header early
header('Content-Type: application/json; charset=utf-8');

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$currentUser = null;
$isSuperAdmin = false;
$branchFilter = '';

// Check if user_id is provided (for Flutter/mobile apps)
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication - get user by ID
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
    
    $isSuperAdmin = ($currentUser['role_name'] ?? '') === 'Super Admin';
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    
    $currentUser = getCurrentUser();
    $isSuperAdmin = hasRole(['Super Admin']);
}

// Apply branch filter for non-super-admin users
if (!$isSuperAdmin) {
    $branchId = $currentUser['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
}

// Get all active classes (excluding graduated classes)
$sql = "SELECT c.id, c.class_name, c.class_code, c.class_order, c.branch_id, 
        b.branch_name,
        (SELECT COUNT(*) FROM sections s WHERE s.class_id = c.id AND s.is_active = 1) as section_count
        FROM classes c 
        LEFT JOIN branches b ON c.branch_id = b.id 
        WHERE c.is_active = 1 
        AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
        $branchFilter
        ORDER BY c.class_order, c.class_name";

$stmt = executeQuery($sql);
$classes = fetchAll($stmt);

jsonResponse(true, 'Classes loaded', $classes);

