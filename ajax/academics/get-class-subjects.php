<?php
/**
 * AJAX: Get Subjects for a Class
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_GET['class_id'] ?? 0;

if (empty($classId)) {
    jsonResponse(false, 'Invalid class ID');
}

$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Get subjects assigned to this class
$sql = "SELECT cs.*, s.subject_name, s.subject_code, s.subject_type,
        st.first_name as teacher_first_name, st.last_name as teacher_last_name
        FROM class_subjects cs
        INNER JOIN subjects s ON cs.subject_id = s.id
        LEFT JOIN staff st ON cs.teacher_id = st.id
        WHERE cs.class_id = ? AND cs.session_id = ?
        ORDER BY s.subject_name";

$stmt = executeQuery($sql, 'ii', [$classId, $sessionId]);
$subjects = fetchAll($stmt);

jsonResponse(true, 'Subjects loaded', $subjects);

