<?php
/**
 * AJAX: Save Attendance Rule
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = (int) ($data['id'] ?? 0);
$ruleName = sanitize($data['rule_name'] ?? '');
$branchId = !empty($data['branch_id']) ? (int) $data['branch_id'] : null;
$workStart = sanitize($data['work_start_time'] ?? '08:00:00');
$workEnd = sanitize($data['work_end_time'] ?? '17:00:00');
$breakMinutes = (int) ($data['break_minutes'] ?? 60);
$gracePeriod = (int) ($data['grace_period_minutes'] ?? 15);
$halfDayThreshold = (float) ($data['half_day_threshold_hours'] ?? 4);
$overtimeThreshold = (int) ($data['overtime_threshold_minutes'] ?? 30);
$weekendDays = sanitize($data['weekend_days'] ?? '5,6');
$isActive = isset($data['is_active']) ? (int) $data['is_active'] : 1;

// Normalize time values (accept HH:MM or HH:MM:SS)
if (preg_match('/^\d{1,2}:\d{2}$/', $workStart)) {
    $workStart .= ':00';
}
if (preg_match('/^\d{1,2}:\d{2}$/', $workEnd)) {
    $workEnd .= ':00';
}

if (empty($ruleName)) {
    jsonResponse(false, 'Rule name is required');
}

// 11 placeholders: s i s s i i d i s i i
$types = 'sisssiidsii';

try {
    if ($id > 0) {
        $sql = "UPDATE hr_attendance_rules SET
                rule_name=?, branch_id=?, work_start_time=?, work_end_time=?,
                break_minutes=?, grace_period_minutes=?, half_day_threshold_hours=?,
                overtime_threshold_minutes=?, weekend_days=?, is_active=?
                WHERE id=?";
        $params = [
            $ruleName, $branchId, $workStart, $workEnd, $breakMinutes, $gracePeriod,
            $halfDayThreshold, $overtimeThreshold, $weekendDays, $isActive, $id
        ];
        $action = 'Update Attendance Rule';
    } else {
        $sql = "INSERT INTO hr_attendance_rules
                (rule_name, branch_id, work_start_time, work_end_time, break_minutes,
                 grace_period_minutes, half_day_threshold_hours, overtime_threshold_minutes,
                 weekend_days, is_active, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $ruleName, $branchId, $workStart, $workEnd, $breakMinutes, $gracePeriod,
            $halfDayThreshold, $overtimeThreshold, $weekendDays, $isActive, $currentUser['id']
        ];
        $action = 'Create Attendance Rule';
    }

    $stmt = executeQuery($sql, $types, $params);

    if ($stmt) {
        logActivity($currentUser['id'], $action, 'HR', "Rule: $ruleName");
        jsonResponse(true, 'Attendance rule saved successfully');
    }

    jsonResponse(false, 'Failed to save attendance rule. Check server logs.');
} catch (Throwable $e) {
    error_log('save-attendance-rule.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
