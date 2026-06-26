<?php
/**
 * API Student Assignments Endpoint
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get user from request
$userId = $_GET['user_id'] ?? null;
// Optional filter by subject or status
$subjectId = $_GET['subject_id'] ?? null;
$status = $_GET['status'] ?? null; // e.g., 'Pending', 'Submitted'

if (!$userId) {
    sendApiResponse(false, 'User ID is required', null, 400);
}

// User role check
$sql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?";
$user = fetchOne(executeQuery($sql, 'i', [$userId]));

if (!$user || $user['role_name'] !== 'Student') {
    sendApiResponse(false, 'Access denied', null, 403);
}

$student = getStudentByUserId($userId);
if (!$student) {
    sendApiResponse(false, 'Student record not found', null, 404);
}

$currentSession = getCurrentSession();
if (!$currentSession) {
    sendApiResponse(false, 'No active session', null, 500);
}

// Assignments Query
// Based on my-assignments.php structure (assumed)
// Usually assignments are linked to class_id/section_id
// And student_submissions table tracks submission status

$classId = $student['current_class_id'];
$sectionId = $student['current_section_id'];

$sql = "SELECT a.*, s.subject_name, s.subject_code,
        st.first_name as teacher_first_name, st.last_name as teacher_last_name,
        ss.submission_date, ss.obtained_marks, ss.remarks as teacher_remarks,
        CASE WHEN ss.id IS NOT NULL THEN 'Submitted' ELSE 'Pending' END as submission_status
        FROM assignments a
        INNER JOIN subjects s ON a.subject_id = s.id
        LEFT JOIN staff st ON a.created_by = st.user_id 
        LEFT JOIN student_submissions ss ON a.id = ss.assignment_id AND ss.student_id = ?
        WHERE a.class_id = ? AND (a.section_id IS NULL OR a.section_id = ?) 
        AND a.session_id = ?";

$params = [$student['id'], $classId, $sectionId, $currentSession['id']];
$types = 'iiii';

if ($subjectId) {
    $sql .= " AND a.subject_id = ?";
    $params[] = $subjectId;
    $types .= 'i';
}

$sql .= " ORDER BY a.due_date ASC";

$stmt = executeQuery($sql, $types, $params);
$assignmentsRaw = fetchAll($stmt);

$assignmentsData = [];
foreach ($assignmentsRaw as $assignment) {
    // Filter by status if requested
    if ($status && $assignment['submission_status'] !== $status) {
        continue;
    }
    
    $assignmentsData[] = [
        'id' => $assignment['id'],
        'title' => $assignment['title'],
        'description' => $assignment['description'], // Assuming description column exists
        'subject_name' => $assignment['subject_name'],
        'subject_code' => $assignment['subject_code'],
        'teacher_name' => trim(($assignment['teacher_first_name'] ?? '') . ' ' . ($assignment['teacher_last_name'] ?? '')),
        'due_date' => $assignment['due_date'],
        'assigned_date' => $assignment['created_at'], // Or assigned_date
        'total_marks' => $assignment['marks'], // Assuming marks column
        'submission_status' => $assignment['submission_status'],
        'obtained_marks' => $assignment['obtained_marks'] ?? null,
        'has_attachment' => !empty($assignment['file_path']) // If file upload exists
    ];
}

sendApiResponse(true, 'Assignments retrieved successfully', $assignmentsData);
