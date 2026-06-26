<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$id     = (int)($_POST['id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');
if (!$id || !in_array($status, ['approved','rejected','completed','cancelled'])) jsonResponse(false, 'Invalid data');

$bk = fetchOne(executeQuery("SELECT * FROM lab_bookings WHERE id=?", 'i', [$id]));
if (!$bk) jsonResponse(false, 'Booking not found');

if ($status === 'approved') {
    $stmt = executeQuery("UPDATE lab_bookings SET status='approved', approved_by=?, approved_at=NOW() WHERE id=?", 'ii', [$currentUser['id'], $id]);
} else {
    $stmt = executeQuery("UPDATE lab_bookings SET status=?, updated_at=NOW() WHERE id=?", 'si', [$status, $id]);
}

if ($stmt) {
    logActivity($currentUser['id'], 'Update Booking', 'Laboratory', "Booking $id set to $status");
    jsonResponse(true, 'Booking ' . $status . ' successfully!');
} else {
    jsonResponse(false, 'Failed to update booking');
}
