<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$token = sanitize($data['token'] ?? '');
$staffId = (int)($data['staff_id'] ?? 0);

if (empty($token) || !$staffId) jsonResponse(false, 'Token and staff_id required');

$session = fetchOne(executeQuery(
    "SELECT * FROM hr_qr_sessions WHERE session_token = ? AND is_active = 1 AND valid_date = CURDATE()", 's', [$token]
));
if (!$session) jsonResponse(false, 'Invalid or expired QR session');

$now = date('H:i:s');
if ($session['valid_from'] && $now < $session['valid_from']) jsonResponse(false, 'QR session not yet active');
if ($session['valid_until'] && $now > $session['valid_until']) jsonResponse(false, 'QR session expired');

$date = date('Y-m-d');
$time = date('H:i:s');
$existing = fetchOne(executeQuery("SELECT id FROM staff_attendance WHERE staff_id = ? AND attendance_date = ?", 'is', [$staffId, $date]));

if ($existing) {
    executeQuery("UPDATE staff_attendance SET check_in=COALESCE(check_in, ?), status='Present', attendance_source='QR' WHERE id=?", 'si', [$time, $existing['id']]);
    $attId = $existing['id'];
} else {
    executeQuery(
        "INSERT INTO staff_attendance (staff_id, attendance_date, check_in, status, attendance_source) VALUES (?, ?, ?, 'Present', 'QR')",
        'iss', [$staffId, $date, $time]
    );
    $attId = getLastInsertId();
}

AttendanceCalculationService::applyToRecord($attId, $staffId, $date, $time, null, 'Present');
jsonResponse(true, 'QR check-in recorded');
