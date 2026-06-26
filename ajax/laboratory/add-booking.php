<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser  = getCurrentUser();
$sectionId    = (int)($_POST['section_id']      ?? 0);
$experimentId = !empty($_POST['experiment_id']) ? (int)$_POST['experiment_id'] : null;
$bookingDate  = sanitize($_POST['booking_date'] ?? '');
$startTime    = sanitize($_POST['start_time']   ?? '');
$endTime      = sanitize($_POST['end_time']     ?? '');
$attendees    = max(1, (int)($_POST['attendees_count'] ?? 1));
$requesterName= sanitize($_POST['requester_name']   ?? '');
$purpose      = sanitize($_POST['purpose']          ?? '');
$equipment    = sanitize($_POST['equipment_needed'] ?? '');
$notes        = sanitize($_POST['notes'] ?? '');

if (!$sectionId || empty($bookingDate) || empty($startTime) || empty($endTime)) {
    jsonResponse(false, 'Section, date, and times are required');
}
if (empty($purpose)) jsonResponse(false, 'Purpose is required');

// Conflict check
$conflict = fetchOne(executeQuery(
    "SELECT id FROM lab_bookings WHERE section_id=? AND booking_date=? AND status IN('pending','approved')
     AND ((start_time < ? AND end_time > ?) OR (start_time >= ? AND start_time < ?))",
    'isssss', [$sectionId, $bookingDate, $endTime, $startTime, $startTime, $endTime]
));

if ($conflict) jsonResponse(false, 'Conflict: Another booking exists for this section at that time. Please choose a different time slot.');

$cnt = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_bookings"))['c'] ?? 0;
$bookNum = 'BK-' . date('Ymd') . '-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);

$sql = "INSERT INTO lab_bookings (booking_number, section_id, requester_id, requester_name, purpose, booking_date,
        start_time, end_time, attendees_count, experiment_id, equipment_needed, status, notes, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
$stmt = executeQuery($sql, 'siisssssiissi', [
    $bookNum, $sectionId, $currentUser['id'], $requesterName, $purpose,
    $bookingDate, $startTime, $endTime, $attendees, $experimentId,
    $equipment, $notes, $currentUser['branch_id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Book Laboratory', 'Laboratory', "Booking $bookNum for $bookingDate");
    jsonResponse(true, "Booking $bookNum submitted! Pending approval.");
} else {
    jsonResponse(false, 'Failed to create booking');
}
