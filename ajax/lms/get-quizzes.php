<?php
/**
 * AJAX: Get Quizzes
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
    $classId = $_GET['class_id'] ?? null;
    $subjectId = $_GET['subject_id'] ?? null;
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT q.*, 
            c.class_name,
            s.subject_name,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
            (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count
            FROM quizzes q
            LEFT JOIN classes c ON q.class_id = c.id
            LEFT JOIN subjects s ON q.subject_id = s.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($classId) {
        $sql .= " AND q.class_id = ?";
        $params[] = $classId;
        $types .= 'i';
    }
    
    if ($subjectId) {
        $sql .= " AND q.subject_id = ?";
        $params[] = $subjectId;
        $types .= 'i';
    }
    
    // Status filter based on start_time and end_time
    if ($status) {
        if ($status == 'Active') {
            $sql .= " AND (q.start_time IS NULL OR q.start_time <= NOW()) AND (q.end_time IS NULL OR q.end_time >= NOW())";
        } elseif ($status == 'Completed') {
            $sql .= " AND q.end_time IS NOT NULL AND q.end_time < NOW()";
        } elseif ($status == 'Scheduled') {
            $sql .= " AND q.start_time IS NOT NULL AND q.start_time > NOW()";
        }
    }
    
    $sql .= " ORDER BY q.created_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $quizzes = fetchAll($stmt);
    
    $formatted = [];
    foreach ($quizzes as $quiz) {
        $formatted[] = [
            'id' => $quiz['id'],
            'title' => $quiz['title'],
            'description' => $quiz['description'] ?? '',
            'class_id' => $quiz['class_id'],
            'class_name' => $quiz['class_name'],
            'subject_id' => $quiz['subject_id'],
            'subject_name' => $quiz['subject_name'],
            'duration_minutes' => $quiz['duration_minutes'] ?? 30,
            'total_marks' => $quiz['total_marks'] ?? 100,
            'question_count' => $quiz['question_count'] ?? 0,
            'start_date' => $quiz['start_time'] ? date('Y-m-d', strtotime($quiz['start_time'])) : null,
            'end_date' => $quiz['end_time'] ? date('Y-m-d', strtotime($quiz['end_time'])) : null,
            'status' => ($quiz['start_time'] && strtotime($quiz['start_time']) > time()) ? 'Scheduled' : 
                       (($quiz['end_time'] && strtotime($quiz['end_time']) < time()) ? 'Completed' : 'Active'),
            'created_by' => $quiz['created_by'] ?? '',
            'created_at' => $quiz['created_at'],
            'attempt_count' => $quiz['attempt_count'] ?? 0,
        ];
    }
    
    jsonResponse(true, 'Quizzes loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load quizzes: ' . $e->getMessage());
}

