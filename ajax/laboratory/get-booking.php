<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$id = (int)($_GET['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');

$row = fetchOne(executeQuery(
    "SELECT b.*, s.section_name, e.experiment_title, u.username as requester_user, a.username as approver_name
     FROM lab_bookings b
     LEFT JOIN lab_sections s ON b.section_id = s.id
     LEFT JOIN lab_experiments e ON b.experiment_id = e.id
     LEFT JOIN users u ON b.requester_id = u.id
     LEFT JOIN users a ON b.approved_by = a.id
     WHERE b.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Booking not found');
jsonResponse(true, '', $row);
