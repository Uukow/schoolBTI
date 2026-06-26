<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
        jsonResponse(false, 'Permission denied');
    }

    $currentUser = getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $id = (int)($data['id'] ?? 0);
    $holidayName = sanitize($data['holiday_name'] ?? '');
    $startDate = sanitize($data['start_date'] ?? $data['holiday_date'] ?? '');
    $endDate = sanitize($data['end_date'] ?? '');
    $branchId = isset($data['branch_id']) && $data['branch_id'] !== '' && $data['branch_id'] !== null
        ? (int)$data['branch_id'] : null;
    $holidayType = sanitize($data['holiday_type'] ?? 'Public');
    $isRecurring = (int)($data['is_recurring'] ?? 0);
    $description = sanitize($data['description'] ?? '');

    if (empty($holidayName) || empty($startDate)) {
        jsonResponse(false, 'Holiday name and start date are required');
    }

    if (empty($endDate)) {
        $endDate = $startDate;
    }

    if ($endDate < $startDate) {
        jsonResponse(false, 'End date cannot be before start date');
    }

    if (!in_array($holidayType, ['Public', 'Institutional', 'Optional'], true)) {
        jsonResponse(false, 'Invalid holiday type');
    }

    // 8 placeholders: name(s), start(s), end(s), branch(i), type(s), recurring(i), notes(s), id/created_by(i)
    $types = 'sssisisi';

    if ($id > 0) {
        $stmt = executeQuery(
            "UPDATE hr_holidays SET holiday_name=?, holiday_date=?, end_date=?, branch_id=?,
             holiday_type=?, is_recurring=?, description=? WHERE id=?",
            $types,
            [$holidayName, $startDate, $endDate, $branchId, $holidayType, $isRecurring, $description, $id]
        );
    } else {
        $stmt = executeQuery(
            "INSERT INTO hr_holidays (holiday_name, holiday_date, end_date, branch_id, holiday_type, is_recurring, description, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            $types,
            [$holidayName, $startDate, $endDate, $branchId, $holidayType, $isRecurring, $description, (int)$currentUser['id']]
        );
    }

    if ($stmt) {
        logActivity($currentUser['id'], 'Save Holiday', 'HR', "$holidayName ($startDate to $endDate)");
        jsonResponse(true, 'Holiday saved successfully');
    }

    global $conn;
    $dbError = (isset($conn) && $conn instanceof mysqli) ? $conn->error : 'Unknown error';
    jsonResponse(false, 'Failed to save holiday: ' . $dbError);
} catch (Throwable $e) {
    error_log('save-holiday.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
