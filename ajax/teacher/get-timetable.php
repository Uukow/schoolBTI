<?php
/**
 * Get Teacher Timetable - AJAX Endpoint
 * 
 * Returns teacher's weekly timetable
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

try {
    // Support both session-based and user_id parameter authentication
    $requestUserId = $_GET['user_id'] ?? null;
    
    if ($requestUserId) {
        // Authenticate via user_id parameter (for Flutter app)
        $userSql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.is_active = 1";
        $userStmt = executeQuery($userSql, 'i', [$requestUserId]);
        $currentUser = fetchOne($userStmt);
        
        if (!$currentUser) {
            jsonResponse(false, 'Invalid user ID', null, 401);
            exit;
        }
        
        // Set session for compatibility
        $_SESSION['user_id'] = $currentUser['id'];
        $_SESSION['role_name'] = $currentUser['role_name'];
    } else {
        // Session-based authentication
        if (!isLoggedIn()) {
            jsonResponse(false, 'Unauthorized', null, 401);
            exit;
        }
        $currentUser = getCurrentUser();
    }

    if (!hasRole(['Teacher', 'Super Admin'])) {
        jsonResponse(false, 'Permission denied', null, 403);
        exit;
    }

    $isSuperAdmin = hasRole(['Super Admin']);

    $teacher = null;
    $teacherId = null;

    if (!$isSuperAdmin) {
        $teacher = getTeacherByUserId($currentUser['id']);
        if (!$teacher) {
            jsonResponse(false, 'Teacher profile not found');
            exit;
        }
        $teacherId = $teacher['id'];
    }

    $currentSession = getCurrentSession();
    if (!$currentSession) {
        jsonResponse(false, 'No active academic session found');
        exit;
    }

    // Get timetable
    if ($isSuperAdmin) {
        $sql = "SELECT t.id, t.day_of_week as day, t.start_time, t.end_time, 
                t.class_id, c.class_name, t.subject_id, s.subject_name, t.room_no as room, sec.section_name
                FROM timetable t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE t.session_id = ?
                ORDER BY FIELD(t.day_of_week, 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'), t.start_time";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $timetable = fetchAll($stmt);
    } else {
        $sql = "SELECT t.id, t.day_of_week as day, t.start_time, t.end_time, 
                t.class_id, c.class_name, t.subject_id, s.subject_name, t.room_no as room, sec.section_name
                FROM timetable t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE t.teacher_id = ? AND t.session_id = ?
                ORDER BY FIELD(t.day_of_week, 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'), t.start_time";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $timetable = fetchAll($stmt);
    }

    jsonResponse(true, 'Timetable loaded', ['timetable' => $timetable]);

} catch (Exception $e) {
    error_log('Get teacher timetable error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load timetable: ' . $e->getMessage());
    exit;
}

