<?php
/**
 * AJAX: Add Quiz
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
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? null;

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

// Check permissions
if (!hasRole(['Super Admin', 'Admin', 'Teacher'])) {
    jsonResponse(false, 'Permission denied');
}

try {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $classId = $data['class_id'] ?? null;
    $subjectId = $data['subject_id'] ?? null;
    $durationMinutes = $data['duration_minutes'] ?? 30;
    $totalMarks = $data['total_marks'] ?? 100;
    $startDate = $data['start_date'] ?? null;
    $endDate = $data['end_date'] ?? null;
    
    if (empty($title) || empty($description)) {
        jsonResponse(false, 'Title and description are required');
    }
    
    if (!$classId) {
        jsonResponse(false, 'Class is required');
    }
    
    if (!$startDate) {
        jsonResponse(false, 'Start date is required');
    }
    
    if (!$classId || !$subjectId) {
        jsonResponse(false, 'Class and subject are required');
    }
    
    // Get current active session
    $sessionSql = "SELECT id FROM academic_sessions WHERE is_active = 1 LIMIT 1";
    $sessionStmt = executeQuery($sessionSql);
    $session = fetchOne($sessionStmt);
    
    if (!$session) {
        jsonResponse(false, 'No active academic session found');
    }
    
    $createdBy = $currentUser['id'] ?? null;
    
    // Convert dates to datetime format
    $startTime = $startDate . ' 00:00:00';
    $endTime = $endDate ? $endDate . ' 23:59:59' : null;

    $sql = "INSERT INTO quizzes 
            (title, description, class_id, subject_id, session_id, duration_minutes, total_marks, start_time, end_time, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $title,
        $description,
        $classId,
        $subjectId,
        $session['id'],
        $durationMinutes,
        $totalMarks,
        $startTime,
        $endTime,
        $createdBy,
    ];

    // s,s,i,i,i,i,d,s,s,i  (10 params)
    $types = 'ssiiiidssi';
    
    executeQuery($sql, $types, $params);
    
    jsonResponse(true, 'Quiz added successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to add quiz: ' . $e->getMessage());
}
