<?php
/**
 * AJAX: Delete Academic Calendar Event
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$eventId = $_POST['id'] ?? 0;

if (empty($eventId)) jsonResponse(false, 'Invalid event ID');

$sql = "DELETE FROM academic_calendar WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$eventId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Academic Event', 'Academics', "Deleted calendar event ID: $eventId");
    jsonResponse(true, 'Event removed from calendar');
} else {
    jsonResponse(false, 'Failed to delete event');
}

