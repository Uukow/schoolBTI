<?php
/**
 * AJAX: Get Single Event
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
    $eventId = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
    
    if (!$eventId) {
        jsonResponse(false, 'Event ID is required');
    }
    
    $sql = "SELECT e.*, 
            u.username as created_by_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?";
    
    $stmt = executeQuery($sql, 'i', [$eventId]);
    $event = fetchOne($stmt);
    
    if (!$event) {
        jsonResponse(false, 'Event not found');
    }
    
    $eventDate = $event['event_date'] ?? null;
    $formatted = [
        'id' => $event['id'],
        'title' => $event['event_title'] ?? '',
        'description' => $event['event_description'] ?? '',
        'start_date' => $eventDate,
        'end_date' => $eventDate,
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
    
    jsonResponse(true, 'Event loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load event: ' . $e->getMessage());
}

