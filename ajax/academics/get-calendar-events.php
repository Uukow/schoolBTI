<?php
/**
 * AJAX: Get Academic Calendar Events
 * 
 * Fetch academic calendar events
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Start output buffering to catch any unwanted output
ob_start();

// Suppress error display for clean JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../config/config.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header early
header('Content-Type: application/json; charset=utf-8');

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$currentUser = null;
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
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Build query
$sql = "SELECT ac.*, 
        ac.id,
        ac.event_title as title,
        ac.event_type,
        ac.start_date,
        ac.end_date,
        ac.description,
        ac.branch_id,
        ac.session_id,
        CASE WHEN ac.event_type = 'Holiday' THEN 1 ELSE 0 END as is_holiday,
        NULL as color,
        b.branch_name
        FROM academic_calendar ac
        LEFT JOIN branches b ON ac.branch_id = b.id
        WHERE ac.session_id = ?";

$params = [$sessionId];
$types = 'i';

// Apply branch filter for non-super-admin users
if ($currentUser && ($currentUser['role_name'] ?? '') !== 'Super Admin') {
    $branchId = $currentUser['branch_id'] ?? null;
    if ($branchId) {
        $sql .= " AND (ac.branch_id = ? OR ac.branch_id IS NULL)";
        $params[] = $branchId;
        $types .= 'i';
    }
}

// Apply date filters
if ($startDate) {
    $sql .= " AND ac.end_date >= ?";
    $params[] = $startDate;
    $types .= 's';
}

if ($endDate) {
    $sql .= " AND ac.start_date <= ?";
    $params[] = $endDate;
    $types .= 's';
}

$sql .= " ORDER BY ac.start_date ASC";

$stmt = executeQuery($sql, $types, $params);
$events = fetchAll($stmt);

jsonResponse(true, 'Calendar events loaded', $events);

