<?php
/**
 * Get Teacher Dashboard Stats - AJAX Endpoint
 * 
 * Returns teacher statistics for dashboard
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

    $stats = [];

    if ($isSuperAdmin) {
        $sql = "SELECT COUNT(DISTINCT cs.class_id) as count 
                FROM class_subjects cs 
                WHERE cs.session_id = ?";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $stats['total_classes'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT cs.subject_id) as count 
                FROM class_subjects cs 
                WHERE cs.session_id = ?";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $stats['total_subjects'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT s.id) as count 
                FROM students s
                WHERE s.status = 'Active'";
        $stmt = executeQuery($sql);
        $stats['total_students'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT s.id) as count 
                FROM students s
                LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = CURDATE()
                WHERE s.status = 'Active' AND sa.id IS NULL";
        $stmt = executeQuery($sql);
        $stats['pending_attendance'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT es.id) as count 
                FROM exam_schedule es
                LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
                WHERE es.exam_date <= CURDATE() AND sm.id IS NULL";
        $stmt = executeQuery($sql);
        $stats['pending_marks'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT DISTINCT t.*, c.class_name, sec.section_name, s.subject_name, t.start_time, t.end_time
                FROM timetable t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN sections sec ON t.section_id = sec.id
                INNER JOIN subjects s ON t.subject_id = s.id
                WHERE t.session_id = ? 
                AND t.day_of_week = UPPER(DAYNAME(CURDATE()))
                ORDER BY t.start_time";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $todayClasses = fetchAll($stmt);
        $stats['today_classes'] = count($todayClasses);

        $sql = "SELECT COUNT(*) as count FROM lesson_plans WHERE session_id = ?";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $stats['completed_lesson_plans'] = (int)(fetchOne($stmt)['count'] ?? 0);
    } else {
        // Teacher sees only their data
        $sql = "SELECT COUNT(DISTINCT cs.class_id) as count 
                FROM class_subjects cs 
                WHERE cs.teacher_id = ? AND cs.session_id = ?";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $stats['total_classes'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT cs.subject_id) as count 
                FROM class_subjects cs 
                WHERE cs.teacher_id = ? AND cs.session_id = ?";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $stats['total_subjects'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT s.id) as count 
                FROM students s
                INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
                WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.status = 'Active'";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $stats['total_students'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT s.id) as count 
                FROM students s
                INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
                LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = CURDATE()
                WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.status = 'Active' AND sa.id IS NULL";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $stats['pending_attendance'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT COUNT(DISTINCT es.id) as count 
                FROM exam_schedule es
                INNER JOIN class_subjects cs ON es.subject_id = cs.subject_id
                LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
                WHERE cs.teacher_id = ? AND es.exam_date <= CURDATE() AND sm.id IS NULL";
        $stmt = executeQuery($sql, 'i', [$teacherId]);
        $stats['pending_marks'] = (int)(fetchOne($stmt)['count'] ?? 0);

        $sql = "SELECT DISTINCT t.*, c.class_name, sec.section_name, s.subject_name, t.start_time, t.end_time
                FROM timetable t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN sections sec ON t.section_id = sec.id
                INNER JOIN subjects s ON t.subject_id = s.id
                WHERE t.teacher_id = ? AND t.session_id = ? 
                AND t.day_of_week = UPPER(DAYNAME(CURDATE()))
                ORDER BY t.start_time";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $todayClasses = fetchAll($stmt);
        $stats['today_classes'] = count($todayClasses);

        $sql = "SELECT COUNT(*) as count FROM lesson_plans WHERE teacher_id = ? AND session_id = ?";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $stats['completed_lesson_plans'] = (int)(fetchOne($stmt)['count'] ?? 0);
    }

    // Calculate attendance rate
    $stats['attendance_rate'] = 0.0;
    if ($stats['total_students'] > 0) {
        $presentCount = $stats['total_students'] - $stats['pending_attendance'];
        $stats['attendance_rate'] = round(($presentCount / $stats['total_students']) * 100, 2);
    }

    jsonResponse(true, 'Dashboard stats loaded', ['stats' => $stats]);

} catch (Exception $e) {
    error_log('Teacher dashboard error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load dashboard stats: ' . $e->getMessage());
    exit;
}

