<?php
/**
 * Get Teacher Classes - AJAX Endpoint
 * 
 * Returns classes and subjects assigned to the teacher
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

    // Get assigned classes and subjects
    if ($isSuperAdmin) {
        $sql = "SELECT cs.id, cs.class_id, cs.subject_id, c.class_name, s.subject_name, s.subject_code,
                (SELECT COUNT(*) FROM students st WHERE st.current_class_id = cs.class_id AND st.status = 'Active') as student_count
                FROM class_subjects cs
                INNER JOIN classes c ON cs.class_id = c.id
                INNER JOIN subjects s ON cs.subject_id = s.id
                WHERE cs.session_id = ?
                ORDER BY c.class_order, s.subject_name";
        $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
        $assignedClasses = fetchAll($stmt);
    } else {
        $sql = "SELECT cs.id, cs.class_id, cs.subject_id, c.class_name, s.subject_name, s.subject_code,
                (SELECT COUNT(*) FROM students st WHERE st.current_class_id = cs.class_id AND st.status = 'Active') as student_count
                FROM class_subjects cs
                INNER JOIN classes c ON cs.class_id = c.id
                INNER JOIN subjects s ON cs.subject_id = s.id
                WHERE cs.teacher_id = ? AND cs.session_id = ?
                ORDER BY c.class_order, s.subject_name";
        $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
        $assignedClasses = fetchAll($stmt);
    }

    jsonResponse(true, 'Classes loaded', ['classes' => $assignedClasses]);

} catch (Exception $e) {
    error_log('Get teacher classes error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to load classes: ' . $e->getMessage());
    exit;
}

