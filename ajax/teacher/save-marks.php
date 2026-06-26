<?php
/**
 * Save Marks - AJAX Endpoint
 * 
 * Save exam marks (teacher only, fully isolated)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if (!hasRole(['Teacher', 'Super Admin'])) {
    jsonResponse(false, 'Permission denied');
}

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$teacher = null;
$teacherId = null;

if (!$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        jsonResponse(false, 'Teacher profile not found');
    }
    $teacherId = $teacher['id'];
}
$examScheduleId = $_POST['exam_schedule_id'] ?? '';
$marks = $_POST['marks'] ?? [];

if (empty($examScheduleId) || empty($marks)) {
    jsonResponse(false, 'Invalid data provided');
}

// Verify exam schedule (skip verification for Super Admin)
if ($isSuperAdmin) {
    $sql = "SELECT es.*, s.subject_id, c.class_id
            FROM exam_schedule es
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN subjects s ON es.subject_id = s.id
            INNER JOIN classes c ON e.class_id = c.id
            WHERE es.id = ?";
    $stmt = executeQuery($sql, 'i', [$examScheduleId]);
    $examSchedule = fetchOne($stmt);
    
    if (!$examSchedule) {
        jsonResponse(false, 'Exam schedule not found');
    }
    
    $validStudentIds = array_keys($marks);
} else {
    $sql = "SELECT es.*, s.subject_id, c.class_id
            FROM exam_schedule es
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN subjects s ON es.subject_id = s.id
            INNER JOIN classes c ON e.class_id = c.id
            INNER JOIN class_subjects cs ON s.id = cs.subject_id AND c.id = cs.class_id
            WHERE es.id = ? AND cs.teacher_id = ?";
    $stmt = executeQuery($sql, 'ii', [$examScheduleId, $teacherId]);
    $examSchedule = fetchOne($stmt);

    if (!$examSchedule) {
        jsonResponse(false, 'Unauthorized: Exam schedule does not belong to your subjects');
    }

    // Verify that all students belong to teacher's classes
    $studentIds = array_keys($marks);
    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $types = str_repeat('i', count($studentIds));

    $sql = "SELECT DISTINCT s.id 
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
            WHERE s.id IN ($placeholders) AND cs.teacher_id = ?";
    $params = array_merge($studentIds, [$teacherId]);
    $types .= 'i';

    $stmt = executeQuery($sql, $types, $params);
    $validStudents = fetchAll($stmt);
    $validStudentIds = array_column($validStudents, 'id');

    // Check if all students are valid
    if (count($validStudentIds) !== count($studentIds)) {
        jsonResponse(false, 'Unauthorized: Some students do not belong to your classes');
    }
}

// Save marks
$successCount = 0;
$errorCount = 0;

foreach ($marks as $studentId => $data) {
    if (!in_array($studentId, $validStudentIds)) {
        $errorCount++;
        continue;
    }
    
    $marksObtained = !empty($data['marks_obtained']) ? floatval($data['marks_obtained']) : null;
    $isAbsent = isset($data['is_absent']) && $data['is_absent'] == '1' ? 1 : 0;
    $remarks = sanitize($data['remarks'] ?? '');
    
    // If absent, set marks to null
    if ($isAbsent) {
        $marksObtained = null;
    }
    
    // Check if marks already exist
    $checkSql = "SELECT id FROM student_marks WHERE student_id = ? AND exam_schedule_id = ?";
    $existing = fetchOne(executeQuery($checkSql, 'ii', [$studentId, $examScheduleId]));
    
    if ($existing) {
        // Update existing
        $updateSql = "UPDATE student_marks SET marks_obtained = ?, is_absent = ?, remarks = ?, entered_by = ? WHERE id = ?";
        $result = executeQuery($updateSql, 'disii', [$marksObtained, $isAbsent, $remarks, $currentUser['id'], $existing['id']]);
    } else {
        // Insert new
        $insertSql = "INSERT INTO student_marks (student_id, exam_schedule_id, marks_obtained, is_absent, remarks, entered_by) 
                     VALUES (?, ?, ?, ?, ?, ?)";
        $result = executeQuery($insertSql, 'iidsis', [
            $studentId,
            $examScheduleId,
            $marksObtained,
            $isAbsent,
            $remarks,
            $currentUser['id']
        ]);
    }
    
    if ($result) {
        $successCount++;
    } else {
        $errorCount++;
    }
}

// Log activity
logActivity($currentUser['id'], 'Enter Marks', 'Teacher Portal', "Entered marks for $successCount students");

if ($errorCount > 0) {
    jsonResponse(true, "Marks saved for $successCount students. $errorCount failed.");
} else {
    jsonResponse(true, "Marks saved successfully for $successCount students.");
}

