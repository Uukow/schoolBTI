<?php
/**
 * Get Teacher Exam Schedules - AJAX Endpoint
 * 
 * Returns exam schedules for classes/subjects assigned to the teacher
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

    // Get exam schedules
    if ($isSuperAdmin) {
        // Super Admin sees all exam schedules
        $sql = "SELECT es.id, es.exam_id, es.subject_id, es.exam_date, es.start_time, es.end_time, 
                es.max_marks, es.total_marks, e.exam_name, s.subject_name, c.class_name
                FROM exam_schedule es
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN classes c ON e.class_id = c.id
                WHERE e.session_id = ?
                ORDER BY es.exam_date DESC, es.start_time";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $schedules = fetchAll($stmt);
    } else {
        // Teacher sees only exam schedules for their assigned classes/subjects
        $sql = "SELECT es.id, es.exam_id, es.subject_id, es.exam_date, es.start_time, es.end_time,
                es.max_marks, es.total_marks, e.exam_name, s.subject_name, c.class_name
                FROM exam_schedule es
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN classes c ON e.class_id = c.id
                INNER JOIN class_subjects cs ON s.id = cs.subject_id AND c.id = cs.class_id
                WHERE cs.teacher_id = ? AND e.session_id = ?
                ORDER BY es.exam_date DESC, es.start_time";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $schedules = fetchAll($stmt);
    }

    jsonResponse(true, 'Exam schedules loaded', ['schedules' => $schedules]);

} catch (Exception $e) {
    error_log('Get teacher exam schedules error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load exam schedules: ' . $e->getMessage());
    exit;
}

