<?php
/**
 * API Student Marks/Results Endpoint
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get user from request
$userId = $_GET['user_id'] ?? null;
// Optional filter by subject
$subjectId = $_GET['subject_id'] ?? null;

if (!$userId) {
    sendApiResponse(false, 'User ID is required', null, 400);
}

// Helper: Ensure user is student and valid (Reused logic, ideally in a shared helper func)
$sql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?";
$user = fetchOne(executeQuery($sql, 'i', [$userId]));

if (!$user || $user['role_name'] !== 'Student') {
    sendApiResponse(false, 'Access denied or User not found', null, 403);
}

$student = getStudentByUserId($userId);
if (!$student) {
    sendApiResponse(false, 'Student record not found', null, 404);
}

$currentSession = getCurrentSession();
if (!$currentSession) {
    sendApiResponse(false, 'No active session', null, 500);
}

$marksData = [];

// Base query for marks
// Structure: exam_schedule -> exams -> marks
// We need to fetch marks for the current student in the current session
// Adapted from my-marks.php logic

$sql = "SELECT m.obtained_marks, m.remarks, 
        e.exam_name, e.exam_type, e.total_marks,
        s.subject_name, s.subject_code,
        es.exam_date
        FROM marks m
        INNER JOIN exam_schedule es ON m.exam_schedule_id = es.id
        INNER JOIN exams e ON es.exam_id = e.id
        INNER JOIN subjects s ON es.subject_id = s.id
        WHERE m.student_id = ? AND es.session_id = ?";

$params = [$student['id'], $currentSession['id']];
$types = 'ii';

if ($subjectId) {
    $sql .= " AND es.subject_id = ?";
    $params[] = $subjectId;
    $types .= 'i';
}

$sql .= " ORDER BY es.exam_date DESC, s.subject_name ASC";

$stmt = executeQuery($sql, $types, $params);
$marksList = fetchAll($stmt);

// Transform data for easier consumption
foreach ($marksList as $mark) {
    $marksData[] = [
        'exam_name' => $mark['exam_name'],
        'exam_type' => $mark['exam_type'],
        'subject_name' => $mark['subject_name'],
        'subject_code' => $mark['subject_code'],
        'obtained_marks' => (float)$mark['obtained_marks'],
        'total_marks' => (float)$mark['total_marks'],
        'percentage' => ($mark['total_marks'] > 0) ? round(($mark['obtained_marks'] / $mark['total_marks']) * 100, 1) : 0,
        'exam_date' => $mark['exam_date'],
        'remarks' => $mark['remarks']
    ];
}

sendApiResponse(true, 'Marks retrieved successfully', $marksData);
