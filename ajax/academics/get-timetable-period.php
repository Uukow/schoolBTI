<?php
/**
 * AJAX: Get Timetable Period Details
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$periodId = $_GET['id'] ?? 0;

if (empty($periodId)) {
    jsonResponse(false, 'Invalid period ID');
}

$sql = "SELECT t.*, s.subject_name, s.subject_code, st.first_name, st.last_name, st.staff_id,
        c.class_name, c.class_code, sec.section_name
        FROM timetable t
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN staff st ON t.teacher_id = st.id
        LEFT JOIN classes c ON t.class_id = c.id
        LEFT JOIN sections sec ON t.section_id = sec.id
        WHERE t.id = ?";

$stmt = executeQuery($sql, 'i', [$periodId]);
$period = fetchOne($stmt);

if (!$period) {
    jsonResponse(false, 'Period not found');
}

jsonResponse(true, 'Period loaded', $period);

