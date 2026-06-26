<?php
/**
 * AJAX: Get Classes for a Subject
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$subjectId = $_GET['subject_id'] ?? 0;

if (empty($subjectId)) {
    jsonResponse(false, 'Invalid subject ID');
}

$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Get classes where this subject is assigned (excluding graduated classes)
$sql = "SELECT cs.*, c.class_name, c.class_code, b.branch_name,
        st.first_name as teacher_first_name, st.last_name as teacher_last_name
        FROM class_subjects cs
        INNER JOIN classes c ON cs.class_id = c.id
        LEFT JOIN branches b ON c.branch_id = b.id
        LEFT JOIN staff st ON cs.teacher_id = st.id
        WHERE cs.subject_id = ? AND cs.session_id = ?
        AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
        ORDER BY c.class_order";

$stmt = executeQuery($sql, 'ii', [$subjectId, $sessionId]);
$classes = fetchAll($stmt);

jsonResponse(true, 'Classes loaded', $classes);

