<?php
/**
 * AJAX: Add Academic Calendar Event
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$eventTitle = sanitize($_POST['event_title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$eventType = $_POST['event_type'] ?? 'Other';
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';

if (empty($eventTitle) || empty($startDate) || empty($endDate)) {
    jsonResponse(false, 'Event title and dates are required');
}

// Get current session
$session = getCurrentSession();
$sessionId = $session['id'] ?? 1;

$branchId = getCurrentUser()['branch_id'] ?? null;

$sql = "INSERT INTO academic_calendar (session_id, event_title, event_type, start_date, end_date, description, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = executeQuery($sql, 'isssssi', [
    $sessionId, $eventTitle, $eventType, $startDate, $endDate, $description, $branchId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Academic Event', 'Academics', "Added calendar event: $eventTitle");
    jsonResponse(true, 'Event added to academic calendar successfully!');
} else {
    jsonResponse(false, 'Failed to add event');
}

