<?php
/**
 * AJAX: Get Attendance Report
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

$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    $reportType = $_GET['report_type'] ?? 'Student Attendance';
    $classId = $_GET['class_id'] ?? null;
    $studentId = $_GET['student_id'] ?? null;
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    $summary = [];
    $details = [];
    
    try {
        if ($reportType == 'Student Attendance') {
            $sql = "SELECT sa.*, s.first_name, s.last_name, s.admission_number
                    FROM student_attendance sa
                    JOIN students s ON sa.student_id = s.id
                    WHERE sa.attendance_date BETWEEN ? AND ?";
            
            $params = [$startDate, $endDate];
            $types = 'ss';
            
            if ($studentId) {
                $sql .= " AND sa.student_id = ?";
                $params[] = $studentId;
                $types .= 'i';
            } elseif ($classId) {
                $sql .= " AND s.class_id = ?";
                $params[] = $classId;
                $types .= 'i';
            }
            
            $sql .= " ORDER BY sa.attendance_date DESC";
            
            $stmt = executeQuery($sql, $types, $params);
            $attendance = fetchAll($stmt) ?: [];
            
            $totalDays = count($attendance);
            $presentDays = 0;
            
            foreach ($attendance as $record) {
                if (($record['status'] ?? '') == 'Present') {
                    $presentDays++;
                }
                $details[] = [
                    'name' => ($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''),
                    'date' => $record['attendance_date'] ?? '',
                    'status' => $record['status'] ?? 'Unknown',
                ];
            }
            
            $summary = [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $totalDays - $presentDays,
                'attendance_percentage' => $totalDays > 0 
                    ? round(($presentDays / $totalDays) * 100, 2)
                    : 0,
            ];
        } else {
            $summary = ['message' => 'Report type not implemented'];
        }
    } catch (Exception $e) {
        $summary = ['message' => 'No attendance data available for selected period'];
        $details = [];
    }
    
    $formatted = [
        'report_type' => $reportType,
        'class_name' => $classId ? getClassName($classId) : null,
        'student_name' => null,
        'staff_name' => null,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'summary' => $summary,
        'details' => $details,
    ];
    
    jsonResponse(true, 'Attendance report loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load attendance report: ' . $e->getMessage());
}

function getClassName($classId) {
    if (!$classId) return null;
    try {
        $sql = "SELECT class_name FROM classes WHERE id = ?";
        $stmt = executeQuery($sql, 'i', [$classId]);
        $class = fetchOne($stmt);
        return $class ? ($class['class_name'] ?? '') : '';
    } catch (Exception $e) {
        return '';
    }
}
