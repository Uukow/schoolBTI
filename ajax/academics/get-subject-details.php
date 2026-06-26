<?php
/**
 * AJAX: Get Subject Details (Comprehensive)
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$subjectId = $_GET['id'] ?? 0;

if (empty($subjectId)) {
    jsonResponse(false, 'Invalid subject ID');
}

// Get subject basic information
$sql = "SELECT * FROM subjects WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$subjectId]);
$subject = fetchOne($stmt);

if (!$subject) {
    jsonResponse(false, 'Subject not found');
}

$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Get classes where this subject is assigned
$classesSql = "SELECT cs.*, c.class_name, c.class_code, b.branch_name,
               st.first_name as teacher_first_name, st.last_name as teacher_last_name
               FROM class_subjects cs
               INNER JOIN classes c ON cs.class_id = c.id
               LEFT JOIN branches b ON c.branch_id = b.id
               LEFT JOIN staff st ON cs.teacher_id = st.id
               WHERE cs.subject_id = ? AND cs.session_id = ?
               ORDER BY c.class_order";

$classesStmt = executeQuery($classesSql, 'ii', [$subjectId, $sessionId]);
$assignedClasses = fetchAll($classesStmt);

$response = [
    'subject' => $subject,
    'assignedClasses' => $assignedClasses,
    'assignedClassesCount' => count($assignedClasses)
];

jsonResponse(true, 'Subject details loaded', $response);

