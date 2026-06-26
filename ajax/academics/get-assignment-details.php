<?php
/**
 * AJAX: Get Assignment Details (Comprehensive)
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$assignmentId = $_GET['id'] ?? 0;

if (empty($assignmentId)) {
    jsonResponse(false, 'Invalid assignment ID');
}

// Get assignment with all related information
$sql = "SELECT cs.*, 
        c.class_name, c.class_code, c.description as class_description,
        s.subject_name, s.subject_code, s.subject_type, s.description as subject_description,
        st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
        b.branch_name,
        sess.session_name
        FROM class_subjects cs
        INNER JOIN classes c ON cs.class_id = c.id
        INNER JOIN subjects s ON cs.subject_id = s.id
        LEFT JOIN staff st ON cs.teacher_id = st.id
        LEFT JOIN branches b ON c.branch_id = b.id
        LEFT JOIN academic_sessions sess ON cs.session_id = sess.id
        WHERE cs.id = ?";

$stmt = executeQuery($sql, 'i', [$assignmentId]);
$assignment = fetchOne($stmt);

if (!$assignment) {
    jsonResponse(false, 'Assignment not found');
}

$response = [
    'assignment' => $assignment
];

jsonResponse(true, 'Assignment details loaded', $response);

