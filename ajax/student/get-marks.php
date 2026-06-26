<?php
/**
 * Get Student Marks
 * 
 * Returns exam marks for the logged-in student
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
    $studentSql = "SELECT id FROM students WHERE user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student) {
        jsonResponse(false, 'Student record not found');
        exit;
    }

    $studentId = $student['id'];

    // Get exam filter
    $examId = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : null;

    // Build query
    $marksSql = "SELECT 
        e.id as exam_id,
        e.exam_name,
        s.id as subject_id,
        s.subject_name,
        em.marks_obtained,
        es.total_marks,
        em.grade,
        em.rank
        FROM exam_marks em
        INNER JOIN exam_schedule es ON em.exam_schedule_id = es.id
        INNER JOIN exams e ON es.exam_id = e.id
        INNER JOIN subjects s ON es.subject_id = s.id
        WHERE em.student_id = ?";
    $params = [$studentId];
    $types = 'i';

    if ($examId) {
        $marksSql .= " AND e.id = ?";
        $params[] = $examId;
        $types .= 'i';
    }

    $marksSql .= " ORDER BY e.exam_date DESC, s.subject_name";

    $stmt = executeQuery($marksSql, $types, $params);
    $marks = fetchAll($stmt);

    $response = [];
    foreach ($marks as $mark) {
        $response[] = [
            'exam_id' => $mark['exam_id'],
            'exam_name' => $mark['exam_name'],
            'subject_id' => $mark['subject_id'],
            'subject_name' => $mark['subject_name'],
            'marks_obtained' => $mark['marks_obtained'] !== null ? floatval($mark['marks_obtained']) : null,
            'total_marks' => floatval($mark['total_marks']),
            'grade' => $mark['grade'],
            'rank' => $mark['rank'] !== null ? intval($mark['rank']) : null,
        ];
    }

    jsonResponse(true, 'Marks retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

