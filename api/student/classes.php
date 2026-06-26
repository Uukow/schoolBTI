<?php
/**
 * API Student Classes Endpoint
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get user from request
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    sendApiResponse(false, 'User ID is required', null, 400);
}

// Fetch user role
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);
$user = fetchOne($stmt);

if (!$user) {
    sendApiResponse(false, 'User not found', null, 404);
}

// Allow Student, Admin, and Super Admin roles
$allowedRoles = ['Student', 'Admin', 'Super Admin', 'Teacher'];
if (!in_array($user['role_name'], $allowedRoles)) {
     sendApiResponse(false, 'Access denied. Insufficient permissions.', null, 403);
}

$currentSession = getCurrentSession();
if (!$currentSession) {
     sendApiResponse(false, 'No active academic session found', null, 500);
}

$assignedClasses = [];

// For students, get their assigned classes
if ($user['role_name'] === 'Student') {
    $student = getStudentByUserId($userId);
    if ($student && isset($student['current_class_id']) && isset($student['current_section_id']) && $student['current_class_id'] && $student['current_section_id']) {
        $sql = "SELECT cs.id as class_subject_id, s.id as subject_id, c.class_name, sec.section_name, s.subject_name, s.subject_code, s.subject_type,
                st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.id as teacher_id
                FROM class_subjects cs
                INNER JOIN classes c ON cs.class_id = c.id
                INNER JOIN sections sec ON sec.class_id = cs.class_id AND sec.id = ?
                INNER JOIN subjects s ON cs.subject_id = s.id
                LEFT JOIN staff st ON cs.teacher_id = st.id
                WHERE cs.class_id = ? AND cs.session_id = ?
                ORDER BY s.subject_name";
                
         $stmt = executeQuery($sql, 'iii', [$student['current_section_id'], $student['current_class_id'], $currentSession['id']]);
         $assignedClasses = fetchAll($stmt);
    }
} else {
    // For Admin/Super Admin/Teacher, get all classes with subjects
    $sql = "SELECT DISTINCT c.id as class_id, c.class_name, c.class_code,
            (SELECT GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') 
             FROM class_subjects cs 
             INNER JOIN subjects s ON cs.subject_id = s.id 
             WHERE cs.class_id = c.id AND cs.session_id = ?) as subjects_list
            FROM classes c
            WHERE c.is_active = 1 
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            ORDER BY c.class_order, c.class_name";
    
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $allClasses = fetchAll($stmt);
    
    // Format as subjects for compatibility
    foreach ($allClasses as $class) {
        $assignedClasses[] = [
            'class_subject_id' => $class['class_id'],
            'subject_id' => $class['class_id'],
            'class_name' => $class['class_name'],
            'section_name' => 'All',
            'subject_name' => $class['class_name'],
            'subject_code' => $class['class_code'],
            'subject_type' => 'Core',
            'teacher_first_name' => '',
            'teacher_last_name' => '',
            'teacher_id' => null,
        ];
    }
}

// Format response
$responseData = [
    'class_name' => '', // Helper for UI header
    'section_name' => '',
    'subjects' => []
];

if (!empty($assignedClasses)) {
    $responseData['class_name'] = $assignedClasses[0]['class_name'];
    $responseData['section_name'] = $assignedClasses[0]['section_name'];
    
    foreach ($assignedClasses as $class) {
        $responseData['subjects'][] = [
            'subject_id' => $class['subject_id'],
            'subject_name' => $class['subject_name'],
            'subject_code' => $class['subject_code'],
            'subject_type' => $class['subject_type'],
            'teacher_name' => trim(($class['teacher_first_name'] ?? '') . ' ' . ($class['teacher_last_name'] ?? '')),
            'teacher_id' => $class['teacher_id']
        ];
    }
}

sendApiResponse(true, 'Classes retrieved successfully', $responseData);
