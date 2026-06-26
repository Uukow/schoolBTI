<?php
/**
 * AJAX: Get Events
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
    // Authenticate via user_id parameter (for Flutter app)
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID', null, 401);
        exit;
    }
    
    // Set session for compatibility
    $_SESSION['user_id'] = $currentUser['id'];
    $_SESSION['role_name'] = $currentUser['role_name'];
} else {
    // Session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized', null, 401);
        exit;
    }
    $currentUser = getCurrentUser();
}

try {
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    $eventType = $_GET['event_type'] ?? null;
    $status = $_GET['status'] ?? null;
    $targetAudience = $_GET['target_audience'] ?? null;
    
    $sql = "SELECT e.*, 
            u.username as created_by_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($startDate) {
        $sql .= " AND e.event_date >= ?";
        $params[] = $startDate;
        $types .= 's';
    }
    
    if ($endDate) {
        $sql .= " AND e.event_date <= ?";
        $params[] = $endDate;
        $types .= 's';
    }
    
    if ($eventType) {
        $sql .= " AND e.event_type = ?";
        $params[] = $eventType;
        $types .= 's';
    }
    
    // Note: events table doesn't have status column, so skip status filter
    // if ($status) {
    //     $sql .= " AND e.status = ?";
    //     $params[] = $status;
    //     $types .= 's';
    // }
    
    if ($targetAudience) {
        $sql .= " AND (e.target_audience = ? OR e.target_audience = 'All')";
        $params[] = $targetAudience;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND (e.branch_id IS NULL OR e.branch_id = ?)";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY e.event_date ASC, e.start_time ASC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $events = fetchAll($stmt);
    
    $formatted = [];
    foreach ($events as $event) {
        // Map database columns to API response
        $eventDate = $event['event_date'] ?? null;
        $formatted[] = [
            'id' => $event['id'],
            'title' => $event['event_title'] ?? '',
            'description' => $event['event_description'] ?? '',
            'start_date' => $eventDate,
            'end_date' => $eventDate, // Use same date for end_date if not separate
            'start_time' => $event['start_time'] ?? null,
            'end_time' => $event['end_time'] ?? null,
            'event_type' => $event['event_type'] ?? 'Other',
            'location' => $event['venue'] ?? null,
            'color' => null,
            'is_all_day' => empty($event['start_time']) && empty($event['end_time']),
            'is_recurring' => false,
            'recurrence_pattern' => null,
            'recurrence_interval' => null,
            'recurrence_end_date' => null,
            'target_audience' => $event['target_audience'] ?? 'All',
            'class_id' => null,
            'class_name' => null,
            'status' => 'Scheduled',
            'created_by' => $event['created_by_name'] ?? '',
            'created_at' => $event['created_at'],
            'updated_at' => null,
        ];
    }
    
    jsonResponse(true, 'Events loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load events: ' . $e->getMessage());
}

