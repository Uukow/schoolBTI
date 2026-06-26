<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? ($_POST['api_key'] ?? '');
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$device = fetchOne(executeQuery("SELECT * FROM hr_biometric_devices WHERE api_key = ? AND is_active = 1", 's', [$apiKey]));
if (!$device) jsonResponse(false, 'Invalid API key');

$biometricId = sanitize($data['biometric_id'] ?? '');
$punchTime = sanitize($data['punch_time'] ?? date('Y-m-d H:i:s'));
$punchType = sanitize($data['punch_type'] ?? 'IN');

if (empty($biometricId)) jsonResponse(false, 'biometric_id required');

$map = fetchOne(executeQuery(
    "SELECT staff_id FROM hr_staff_biometric_map WHERE biometric_id = ? AND (device_id = ? OR device_id IS NULL)",
    'si', [$biometricId, $device['id']]
));
if (!$map) jsonResponse(false, 'Staff not mapped to biometric ID');

$staffId = $map['staff_id'];
$date = date('Y-m-d', strtotime($punchTime));
$time = date('H:i:s', strtotime($punchTime));

$existing = fetchOne(executeQuery("SELECT id FROM staff_attendance WHERE staff_id = ? AND attendance_date = ?", 'is', [$staffId, $date]));

if ($punchType === 'IN') {
    if ($existing) {
        executeQuery("UPDATE staff_attendance SET check_in=?, attendance_source='Biometric' WHERE id=?", 'si', [$time, $existing['id']]);
        $attId = $existing['id'];
    } else {
        executeQuery(
            "INSERT INTO staff_attendance (staff_id, attendance_date, check_in, status, attendance_source) VALUES (?, ?, ?, 'Present', 'Biometric')",
            'iss', [$staffId, $date, $time]
        );
        $attId = getLastInsertId();
    }
} else {
    if ($existing) {
        executeQuery("UPDATE staff_attendance SET check_out=?, attendance_source='Biometric' WHERE id=?", 'si', [$time, $existing['id']]);
        $attId = $existing['id'];
    } else {
        executeQuery(
            "INSERT INTO staff_attendance (staff_id, attendance_date, check_out, status, attendance_source) VALUES (?, ?, ?, 'Present', 'Biometric')",
            'iss', [$staffId, $date, $time]
        );
        $attId = getLastInsertId();
    }
}

if ($attId) {
    $att = fetchOne(executeQuery("SELECT * FROM staff_attendance WHERE id = ?", 'i', [$attId]));
    AttendanceCalculationService::applyToRecord($attId, $staffId, $date, $att['check_in'], $att['check_out'], $att['status']);
}

executeQuery("UPDATE hr_biometric_devices SET last_sync_at=NOW() WHERE id=?", 'i', [$device['id']]);
jsonResponse(true, 'Punch recorded', ['staff_id' => $staffId, 'date' => $date, 'time' => $time]);
