<?php
/**
 * Get Teacher Lesson Plans - AJAX Endpoint
 * 
 * Returns lesson plans created by the teacher
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

    // Build query
    // Note: Database columns are content, resources, assessment
    // But Flutter model expects activities, materials, homework
    // So we alias them to match Flutter expectations
    if ($isSuperAdmin) {
        $sql = "SELECT lp.id, lp.lesson_title as title, lp.lesson_date as date,
                lp.class_id, c.class_name, lp.subject_id, s.subject_name,
                lp.objectives, 
                lp.content as activities, 
                lp.resources as materials, 
                lp.assessment as homework, 
                lp.methodology as notes
                FROM lesson_plans lp
                INNER JOIN classes c ON lp.class_id = c.id
                INNER JOIN subjects s ON lp.subject_id = s.id
                WHERE lp.session_id = ?";
        $params = [$currentSession['id']];
        $types = 'i';

        if ($classId) {
            $sql .= " AND lp.class_id = ?";
            $params[] = $classId;
            $types .= 'i';
        }
        if ($subjectId) {
            $sql .= " AND lp.subject_id = ?";
            $params[] = $subjectId;
            $types .= 'i';
        }

        $sql .= " ORDER BY lp.lesson_date DESC, c.class_name, s.subject_name";
        $stmt = executeQuery($sql, $types, $params);
        $lessonPlans = fetchAll($stmt);
    } else {
        $sql = "SELECT lp.id, lp.lesson_title as title, lp.lesson_date as date,
                lp.class_id, c.class_name, lp.subject_id, s.subject_name,
                lp.objectives, 
                lp.content as activities, 
                lp.resources as materials, 
                lp.assessment as homework, 
                lp.methodology as notes
                FROM lesson_plans lp
                INNER JOIN classes c ON lp.class_id = c.id
                INNER JOIN subjects s ON lp.subject_id = s.id
                WHERE lp.teacher_id = ? AND lp.session_id = ?";
        $params = [$teacherId, $currentSession['id']];
        $types = 'ii';

        if ($classId) {
            $sql .= " AND lp.class_id = ?";
            $params[] = $classId;
            $types .= 'i';
        }
        if ($subjectId) {
            $sql .= " AND lp.subject_id = ?";
            $params[] = $subjectId;
            $types .= 'i';
        }

        $sql .= " ORDER BY lp.lesson_date DESC, c.class_name, s.subject_name";
        $stmt = executeQuery($sql, $types, $params);
        $lessonPlans = fetchAll($stmt);
    }

    jsonResponse(true, 'Lesson plans loaded', ['lesson_plans' => $lessonPlans]);

} catch (Exception $e) {
    error_log('Get teacher lesson plans error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load lesson plans: ' . $e->getMessage());
    exit;
}

