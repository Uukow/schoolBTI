<?php
/**
 * Get Student Timetable
 * 
 * Returns weekly timetable for the logged-in student
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

    // Get student ID and class info
    $studentSql = "SELECT id, current_class_id, current_section_id FROM students WHERE user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student || !$student['current_class_id'] || !$student['current_section_id']) {
        jsonResponse(true, 'No timetable found', []);
        exit;
    }

    $classId = $student['current_class_id'];
    $sectionId = $student['current_section_id'];

    // Get timetable
    // Note: timetable table has teacher_id directly, no separate class_teachers table
    $currentSession = getCurrentSession();
    $sessionId = $currentSession ? $currentSession['id'] : null;
    
    if (!$sessionId) {
        jsonResponse(false, 'No active academic session found');
        exit;
    }
    
    $timetableSql = "SELECT 
        t.id,
        t.day_of_week as day,
        t.start_time,
        t.end_time,
        s.subject_name,
        COALESCE(CONCAT(st.first_name, ' ', st.last_name), 'Not Assigned') as teacher_name,
        t.room_no
        FROM timetable t
        INNER JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN staff st ON t.teacher_id = st.id
        WHERE t.class_id = ? AND t.section_id = ? AND t.session_id = ?
        ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time";
    $stmt = executeQuery($timetableSql, 'iii', [$classId, $sectionId, $sessionId]);
    $timetable = fetchAll($stmt);

    $response = [];
    foreach ($timetable as $item) {
        $response[] = [
            'id' => $item['id'],
            'day' => $item['day'],
            'start_time' => $item['start_time'],
            'end_time' => $item['end_time'],
            'subject_name' => $item['subject_name'],
            'teacher_name' => $item['teacher_name'],
            'room_no' => $item['room_no'],
        ];
    }

    jsonResponse(true, 'Timetable retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

