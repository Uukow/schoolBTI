<?php
/**
 * AJAX: Delete Event
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
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? null;

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

// Check permissions
if (!hasRole(['Super Admin', 'Admin', 'Teacher'])) {
    jsonResponse(false, 'Permission denied');
}

try {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $eventId = $data['event_id'] ?? null;
    
    if (!$eventId) {
        jsonResponse(false, 'Event ID is required');
    }
    
    // Get event title for logging
    $eventSql = "SELECT event_title FROM events WHERE id = ?";
    $eventStmt = executeQuery($eventSql, 'i', [$eventId]);
    $event = fetchOne($eventStmt);
    
    if (!$event) {
        jsonResponse(false, 'Event not found');
    }
    
    $sql = "DELETE FROM events WHERE id = ?";
    executeQuery($sql, 'i', [$eventId]);
    
    logActivity($currentUser['id'], 'Delete Event', 'Events', "Deleted event: {$event['event_title']}");
    
    jsonResponse(true, 'Event deleted successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to delete event: ' . $e->getMessage());
}
