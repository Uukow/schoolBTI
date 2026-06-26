<?php
/**
 * Get Teacher Students - AJAX Endpoint
 * 
 * Returns students assigned to the teacher's classes/subjects
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

    $classId = $_GET['class_id'] ?? null;
    $subjectId = $_GET['subject_id'] ?? null;

    // Get students
    if ($isSuperAdmin) {
        if ($classId) {
            $sql = "SELECT DISTINCT s.id, s.student_id, s.admission_no as admission_number,
                    CONCAT(s.first_name, ' ', s.last_name) as full_name,
                    s.email, s.phone, s.photo, s.current_class_id, c.class_name, sec.section_name, s.status
                    FROM students s
                    INNER JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN sections sec ON s.current_section_id = sec.id
                    WHERE s.current_class_id = ? AND s.status = 'Active'
                    ORDER BY s.first_name, s.last_name";
            $stmt = executeQuery($sql, 'i', [$classId]);
            $students = fetchAll($stmt);
        } else {
            $sql = "SELECT DISTINCT s.id, s.student_id, s.admission_no as admission_number,
                    CONCAT(s.first_name, ' ', s.last_name) as full_name,
                    s.email, s.phone, s.photo, s.current_class_id, c.class_name, sec.section_name, s.status
                    FROM students s
                    INNER JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN sections sec ON s.current_section_id = sec.id
                    WHERE s.status = 'Active'
                    ORDER BY c.class_order, s.first_name, s.last_name";
            $stmt = executeQuery($sql);
            $students = fetchAll($stmt);
        }
    } else {
        if ($classId && $subjectId) {
            // Verify teacher is assigned to this class/subject
            $verifySql = "SELECT id FROM class_subjects 
                         WHERE teacher_id = ? AND class_id = ? AND subject_id = ? AND session_id = ?";
            $verifyStmt = executeQuery($verifySql, 'iiii', [$teacherId, $classId, $subjectId, $currentSession['id']]);
            $verify = fetchOne($verifyStmt);
            
            if (!$verify) {
                jsonResponse(false, 'You are not assigned to this class/subject');
                exit;
            }

            $sql = "SELECT DISTINCT s.id, s.student_id, s.admission_no as admission_number,
                    CONCAT(s.first_name, ' ', s.last_name) as full_name,
                    s.email, s.phone, s.photo, s.current_class_id, c.class_name, sec.section_name, s.status
                    FROM students s
                    INNER JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN sections sec ON s.current_section_id = sec.id
                    WHERE s.current_class_id = ? AND s.status = 'Active'
                    ORDER BY s.first_name, s.last_name";
            $stmt = executeQuery($sql, 'i', [$classId]);
            $students = fetchAll($stmt);
        } else {
            // Get all students from teacher's assigned classes
            $sql = "SELECT DISTINCT s.id, s.student_id, s.admission_no as admission_number,
                    CONCAT(s.first_name, ' ', s.last_name) as full_name,
                    s.email, s.phone, s.photo, s.current_class_id, c.class_name, sec.section_name, s.status
                    FROM students s
                    INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
                    INNER JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN sections sec ON s.current_section_id = sec.id
                    WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.status = 'Active'
                    ORDER BY c.class_order, s.first_name, s.last_name";
            $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
            $students = fetchAll($stmt);
        }
    }

    jsonResponse(true, 'Students loaded', ['students' => $students]);

} catch (Exception $e) {
    error_log('Get teacher students error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load students: ' . $e->getMessage());
    exit;
}

