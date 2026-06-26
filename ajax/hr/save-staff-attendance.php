<?php
/**
 * AJAX: Save Staff Attendance
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

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$currentUser = null;
$userId = $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    if ($stmt === false) {
        jsonResponse(false, 'Database error: Failed to retrieve user information');
    }
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID or user not found');
    }
    
    // Check permissions for mobile app
    $allowedRoles = ['Super Admin', 'Admin'];
    $userRole = $currentUser['role_name'] ?? '';
    if (!in_array($userRole, $allowedRoles)) {
        jsonResponse(false, 'Permission denied. Only Super Admin and Admin can mark staff attendance.');
    }
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'User not logged in');
    }
    if (!hasRole(['Super Admin', 'Admin'])) {
        jsonResponse(false, 'Permission denied');
    }
    $currentUser = getCurrentUser();
    if (!$currentUser) {
        jsonResponse(false, 'Unable to retrieve user information');
    }
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

$staffId = (int)($data['staff_id'] ?? 0);
$attendanceDate = sanitize($data['attendance_date'] ?? date('Y-m-d'));
// Get raw values before sanitize to preserve null/empty
$checkInRaw = $data['check_in'] ?? null;
$checkOutRaw = $data['check_out'] ?? null;
$status = sanitize($data['status'] ?? 'Present');
$remarks = sanitize($data['remarks'] ?? '');

// Clean up raw values
if ($checkInRaw !== null) {
    $checkInRaw = trim($checkInRaw);
    if ($checkInRaw === '') {
        $checkInRaw = null;
    }
}
if ($checkOutRaw !== null) {
    $checkOutRaw = trim($checkOutRaw);
    if ($checkOutRaw === '') {
        $checkOutRaw = null;
    }
}

if (empty($staffId) || empty($attendanceDate) || empty($status)) {
    jsonResponse(false, 'Staff ID, date, and status are required');
}

// Format time to HH:MM:SS format if provided
$checkIn = null;
if (!empty($checkInRaw) && trim($checkInRaw) !== '' && trim($checkInRaw) !== '00:00' && trim($checkInRaw) !== '00:00:00') {
    $checkInRaw = trim($checkInRaw);
    // If time is in HH:MM format, convert to HH:MM:SS
    if (preg_match('/^(\d{1,2}):(\d{2})$/', $checkInRaw, $matches)) {
        $hour = (int)$matches[1];
        $minute = (int)$matches[2];
        // Only save if not midnight (00:00)
        if ($hour != 0 || $minute != 0) {
            $checkIn = sprintf('%02d:%02d:00', $hour, $minute);
        }
    } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $checkInRaw, $matches)) {
        // Already in HH:MM:SS format
        $hour = (int)$matches[1];
        $minute = (int)$matches[2];
        // Only save if not midnight (00:00:00)
        if ($hour != 0 || $minute != 0) {
            $checkIn = $checkInRaw;
        }
    } else {
        // Try to parse and format
        $time = strtotime($checkInRaw);
        if ($time !== false) {
            $formatted = date('H:i:s', $time);
            // Only save if not midnight
            if ($formatted !== '00:00:00') {
                $checkIn = $formatted;
            }
        }
    }
}

$checkOut = null;
if (!empty($checkOutRaw) && trim($checkOutRaw) !== '' && trim($checkOutRaw) !== '00:00' && trim($checkOutRaw) !== '00:00:00') {
    $checkOutRaw = trim($checkOutRaw);
    // If time is in HH:MM format, convert to HH:MM:SS
    if (preg_match('/^(\d{1,2}):(\d{2})$/', $checkOutRaw, $matches)) {
        $hour = (int)$matches[1];
        $minute = (int)$matches[2];
        // Only save if not midnight (00:00)
        if ($hour != 0 || $minute != 0) {
            $checkOut = sprintf('%02d:%02d:00', $hour, $minute);
        }
    } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $checkOutRaw, $matches)) {
        // Already in HH:MM:SS format
        $hour = (int)$matches[1];
        $minute = (int)$matches[2];
        // Only save if not midnight (00:00:00)
        if ($hour != 0 || $minute != 0) {
            $checkOut = $checkOutRaw;
        }
    } else {
        // Try to parse and format
        $time = strtotime($checkOutRaw);
        if ($time !== false) {
            $formatted = date('H:i:s', $time);
            // Only save if not midnight
            if ($formatted !== '00:00:00') {
                $checkOut = $formatted;
            }
        }
    }
}

// Check if attendance already exists
$checkSql = "SELECT id FROM staff_attendance WHERE staff_id = ? AND attendance_date = ?";
$checkStmt = executeQuery($checkSql, 'is', [$staffId, $attendanceDate]);
$existing = fetchOne($checkStmt);

// Prepare values - convert null/empty to NULL for database
$checkInValue = ($checkIn !== null && $checkIn !== '') ? $checkIn : null;
$checkOutValue = ($checkOut !== null && $checkOut !== '') ? $checkOut : null;

// Use direct SQL with NULL handling for better compatibility
if ($existing) {
    // Update existing record
    $sql = "UPDATE staff_attendance 
            SET check_in = " . ($checkInValue !== null ? "?" : "NULL") . ",
                check_out = " . ($checkOutValue !== null ? "?" : "NULL") . ",
                status = ?, 
                remarks = ?, 
                marked_by = ?
            WHERE id = ?";
    
    $params = [];
    $types = '';
    
    if ($checkInValue !== null) {
        $params[] = $checkInValue;
        $types .= 's';
    }
    if ($checkOutValue !== null) {
        $params[] = $checkOutValue;
        $types .= 's';
    }
    
    $params[] = $status;
    $types .= 's';
    $params[] = $remarks;
    $types .= 's';
    $params[] = $currentUser['id'];
    $types .= 'i';
    $params[] = $existing['id'];
    $types .= 'i';
    
    $stmt = executeQuery($sql, $types, $params);
} else {
    // Insert new record
    $sql = "INSERT INTO staff_attendance (staff_id, attendance_date, check_in, check_out, status, remarks, marked_by)
            VALUES (?, ?, " . ($checkInValue !== null ? "?" : "NULL") . ", " . ($checkOutValue !== null ? "?" : "NULL") . ", ?, ?, ?)";
    
    $params = [];
    $types = '';
    
    $params[] = $staffId;
    $types .= 'i';
    $params[] = $attendanceDate;
    $types .= 's';
    
    if ($checkInValue !== null) {
        $params[] = $checkInValue;
        $types .= 's';
    }
    if ($checkOutValue !== null) {
        $params[] = $checkOutValue;
        $types .= 's';
    }
    
    $params[] = $status;
    $types .= 's';
    $params[] = $remarks;
    $types .= 's';
    $params[] = $currentUser['id'];
    $types .= 'i';
    
    $stmt = executeQuery($sql, $types, $params);
}

if ($stmt) {
    $attendanceId = $existing ? $existing['id'] : getLastInsertId();
    if ($attendanceId && class_exists('AttendanceCalculationService')) {
        AttendanceCalculationService::applyToRecord(
            $attendanceId,
            $staffId,
            $attendanceDate,
            $checkInValue,
            $checkOutValue,
            $status
        );
    }
    logActivity($currentUser['id'], 'Save Staff Attendance', 'HR', "Saved attendance for staff ID: $staffId, Date: $attendanceDate");
    jsonResponse(true, 'Attendance saved successfully!');
} else {
    jsonResponse(false, 'Failed to save attendance');
}

