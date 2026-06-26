<?php
/**
 * AJAX: Get Staff Attendance
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
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

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

try {
    $staffId = $_GET['staff_id'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');
    
    $sql = "SELECT sa.*, s.first_name, s.last_name, s.designation
            FROM staff_attendance sa
            LEFT JOIN staff s ON sa.staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE sa.attendance_date = ?";
    
    $params = [$date];
    $types = 's';
    
    if ($staffId) {
        $sql .= " AND sa.staff_id = ?";
        $params[] = $staffId;
        $types .= 'i';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND s.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY s.first_name, s.last_name";
    
    $stmt = executeQuery($sql, $types, $params);
    $attendance = fetchAll($stmt);
    
    $formatted = [];
    foreach ($attendance as $att) {
        // Format check_in and check_out times to HH:MM format for display
        $checkIn = null;
        if (!empty($att['check_in'])) {
            // Extract time part if it's a datetime, or use as-is if it's already time
            $timeStr = trim($att['check_in']);
            // Skip if time is "00:00:00" or "00:00" (treat as null/not set)
            if ($timeStr !== '00:00:00' && $timeStr !== '00:00' && !empty($timeStr)) {
                if (preg_match('/(\d{1,2}):(\d{2})(?::\d{2})?/', $timeStr, $matches)) {
                    $hour = (int)$matches[1];
                    $minute = (int)$matches[2];
                    // Only set if not midnight (00:00)
                    if ($hour != 0 || $minute != 0) {
                        $checkIn = sprintf('%02d:%02d', $hour, $minute);
                    }
                }
            }
        }
        
        $checkOut = null;
        if (!empty($att['check_out'])) {
            // Extract time part if it's a datetime, or use as-is if it's already time
            $timeStr = trim($att['check_out']);
            // Skip if time is "00:00:00" or "00:00" (treat as null/not set)
            if ($timeStr !== '00:00:00' && $timeStr !== '00:00' && !empty($timeStr)) {
                if (preg_match('/(\d{1,2}):(\d{2})(?::\d{2})?/', $timeStr, $matches)) {
                    $hour = (int)$matches[1];
                    $minute = (int)$matches[2];
                    // Only set if not midnight (00:00)
                    if ($hour != 0 || $minute != 0) {
                        $checkOut = sprintf('%02d:%02d', $hour, $minute);
                    }
                }
            }
        }
        
        $formatted[] = [
            'id' => $att['id'],
            'staff_id' => $att['staff_id'],
            'staff_name' => trim(($att['first_name'] ?? '') . ' ' . ($att['last_name'] ?? '')),
            'designation' => $att['designation'] ?? '',
            'attendance_date' => $att['attendance_date'],
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $att['status'],
            'remarks' => $att['remarks'],
            'created_at' => $att['created_at'],
        ];
    }
    
    jsonResponse(true, 'Staff attendance loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load staff attendance: ' . $e->getMessage());
}

