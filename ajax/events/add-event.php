<?php
/**
 * AJAX: Add Event
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
    
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $startDate = $data['start_date'] ?? null;
    $endDate = $data['end_date'] ?? null;
    $startTime = $data['start_time'] ?? null;
    $endTime = $data['end_time'] ?? null;
    $eventType = $data['event_type'] ?? 'Other';
    $location = $data['location'] ?? null;
    $color = $data['color'] ?? null;
    $isAllDay = isset($data['is_all_day']) && ($data['is_all_day'] == 1 || $data['is_all_day'] == true);
    $isRecurring = isset($data['is_recurring']) && ($data['is_recurring'] == 1 || $data['is_recurring'] == true);
    $recurrencePattern = $data['recurrence_pattern'] ?? null;
    $recurrenceInterval = $data['recurrence_interval'] ?? null;
    $recurrenceEndDate = $data['recurrence_end_date'] ?? null;
    $targetAudience = $data['target_audience'] ?? 'All';
    $classId = $data['class_id'] ?? null;
    $status = $data['status'] ?? 'Scheduled';
    
    if (empty($title) || empty($description)) {
        jsonResponse(false, 'Title and description are required');
    }
    
    if (!$startDate || !$endDate) {
        jsonResponse(false, 'Start date and end date are required');
    }
    
    $branchId = $currentUser['branch_id'] ?? null;
    $createdBy = $currentUser['id'] ?? null;
    
    // If all day, set times to null
    if ($isAllDay) {
        $startTime = null;
        $endTime = null;
    }
    
    // Map to database schema: event_title, event_description, event_date, venue
    // Use start_date as event_date, and combine start_date/end_date if needed
    $eventDate = $startDate; // Use start date as primary event date
    
    $sql = "INSERT INTO events 
            (event_title, event_description, event_type, event_date, start_time, end_time, 
             venue, branch_id, target_audience, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $params = [
        $title,
        $description,
        $eventType,
        $eventDate,
        $startTime,
        $endTime,
        $location,
        $branchId,
        $targetAudience,
        $createdBy,
    ];
    
    $types = 'ssssssssss';
    
    executeQuery($sql, $types, $params);
    
    logActivity($createdBy, 'Create Event', 'Events', "Created event: $title");
    
    jsonResponse(true, 'Event added successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to add event: ' . $e->getMessage());
}
