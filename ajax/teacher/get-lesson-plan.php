<?php
/**
 * Get Lesson Plan - AJAX Endpoint
 * 
 * Get lesson plan details for editing
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if (!hasRole(['Teacher', 'Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$lessonPlanId = $_GET['id'] ?? 0;

if (empty($lessonPlanId)) {
    jsonResponse(false, 'Invalid lesson plan ID');
}

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

$teacher = null;
$teacherId = null;

if (!$isSuperAdmin && !$isAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        jsonResponse(false, 'Teacher profile not found');
    }
    $teacherId = $teacher['id'];
}

// Get lesson plan details
$sql = "SELECT lp.*, c.class_name, s.subject_name
        FROM lesson_plans lp
        INNER JOIN classes c ON lp.class_id = c.id
        INNER JOIN subjects s ON lp.subject_id = s.id
        WHERE lp.id = ?";
$stmt = executeQuery($sql, 'i', [$lessonPlanId]);
$lessonPlan = fetchOne($stmt);

if (!$lessonPlan) {
    jsonResponse(false, 'Lesson plan not found');
}

// Verify ownership (teachers can only edit their own lesson plans)
if ($isTeacher && !$isSuperAdmin && !$isAdmin && $lessonPlan['teacher_id'] != $teacherId) {
    jsonResponse(false, 'You do not have permission to edit this lesson plan');
}

jsonResponse(true, 'Lesson plan loaded', $lessonPlan);

