<?php
/**
 * Get Student Classes and Subjects
 * 
 * Returns classes and subjects for the logged-in student
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
    // Support both session and user_id parameter authentication
    $userId = null;
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        $_SESSION['user_id'] = $userId;
    } elseif (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } else {
        jsonResponse(false, 'User not logged in');
        exit;
    }

    // Verify user is a student
    $userCheckSql = "SELECT u.id, r.role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($userCheckSql, 'i', [$userId]);
    $user = fetchOne($stmt);

    if (!$user || $user['role_name'] !== 'Student') {
        jsonResponse(false, 'Unauthorized: Student access only');
        exit;
    }

    // Get student ID
    $studentSql = "SELECT id, current_class_id, current_section_id FROM students WHERE user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student) {
        jsonResponse(false, 'Student record not found');
        exit;
    }

    $studentId = $student['id'];
    $classId = $student['current_class_id'];
    $sectionId = $student['current_section_id'];

    if (!$classId || !$sectionId) {
        jsonResponse(true, 'No classes assigned', []);
        exit;
    }

    // Get subjects for student's class
    // Note: class_subjects table has teacher_id directly, no separate class_teachers table
    $currentSession = getCurrentSession();
    $sessionId = $currentSession ? $currentSession['id'] : null;
    
    if (!$sessionId) {
        jsonResponse(false, 'No active academic session found');
        exit;
    }
    
    $subjectsSql = "SELECT DISTINCT
                    cs.class_id as class_id,
                    c.class_name as class_name,
                    s.id as subject_id,
                    s.subject_name as subject_name,
                    s.subject_code as subject_code,
                    COALESCE(CONCAT(st.first_name, ' ', st.last_name), 'Not Assigned') as teacher_name
                    FROM class_subjects cs
                    INNER JOIN subjects s ON cs.subject_id = s.id
                    INNER JOIN classes c ON cs.class_id = c.id
                    LEFT JOIN staff st ON cs.teacher_id = st.id
                    WHERE cs.class_id = ? AND cs.session_id = ?
                    ORDER BY s.subject_name";
    $stmt = executeQuery($subjectsSql, 'ii', [$classId, $sessionId]);
    $subjects = fetchAll($stmt);

    $response = [];
    foreach ($subjects as $subject) {
        $response[] = [
            'class_id' => $subject['class_id'],
            'class_name' => $subject['class_name'],
            'subject_id' => $subject['subject_id'],
            'subject_name' => $subject['subject_name'],
            'subject_code' => $subject['subject_code'],
            'teacher_name' => $subject['teacher_name'],
        ];
    }

    jsonResponse(true, 'Student classes retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

