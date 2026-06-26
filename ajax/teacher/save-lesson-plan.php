<?php
/**
 * Save Lesson Plan - AJAX Endpoint
 * 
 * Save lesson plan (teacher only, fully isolated)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Support both session-based and user_id parameter authentication
    $requestUserId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    
    if ($requestUserId) {
        // Authenticate via user_id parameter (for Flutter app)
        $userSql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.is_active = 1";
        $userStmt = executeQuery($userSql, 'i', [$requestUserId]);
        $currentUser = fetchOne($userStmt);
        
        if (!$currentUser) {
            jsonResponse(false, 'Invalid user ID', null, 401);
            exit;
        }
        
        // Set session for compatibility
        $_SESSION['user_id'] = $currentUser['id'];
        $_SESSION['role_name'] = $currentUser['role_name'];
    } else {
        // Session-based authentication
        if (!isLoggedIn()) {
            jsonResponse(false, 'Unauthorized', null, 401);
            exit;
        }
        $currentUser = getCurrentUser();
    }

    if (!hasRole(['Teacher', 'Super Admin', 'Admin'])) {
        jsonResponse(false, 'Permission denied', null, 403);
        exit;
    }

    $isSuperAdmin = hasRole(['Super Admin']);
    $isAdmin = hasRole(['Admin']);
    $isTeacher = hasRole(['Teacher']);

    $teacher = null;
    $teacherId = null;

    if ($isSuperAdmin || $isAdmin) {
        // Admin and Super Admin can create lesson plans for any teacher
        $teacherId = $_POST['teacher_id'] ?? '';
        if (empty($teacherId)) {
            jsonResponse(false, 'Teacher ID is required');
            exit;
        }
    } else {
        // Teachers can only create lesson plans for themselves
        $teacher = getTeacherByUserId($currentUser['id']);
        if (!$teacher) {
            jsonResponse(false, 'Teacher profile not found');
            exit;
        }
        $teacherId = $teacher['id'];
    }

$currentSession = getCurrentSession();

if (!$currentSession) {
    jsonResponse(false, 'No active session found. Please contact administrator.');
}

$lessonPlanId = $_POST['id'] ?? null;
$classId = $_POST['class_id'] ?? '';
$subjectId = $_POST['subject_id'] ?? '';
$lessonDate = $_POST['date'] ?? $_POST['lesson_date'] ?? '';
$lessonTitle = $_POST['title'] ?? $_POST['lesson_title'] ?? '';
$objectives = $_POST['objectives'] ?? '';
// Support both Flutter field names (activities, materials, homework) and database names (content, resources, assessment)
$content = $_POST['activities'] ?? $_POST['content'] ?? '';
$methodology = $_POST['notes'] ?? $_POST['methodology'] ?? '';
$resources = $_POST['materials'] ?? $_POST['resources'] ?? '';
$assessment = $_POST['homework'] ?? $_POST['assessment'] ?? '';
$status = $_POST['status'] ?? 'Draft';

    if (empty($classId) || empty($subjectId) || empty($lessonDate) || empty($lessonTitle)) {
        jsonResponse(false, 'Please fill all required fields');
        exit;
    }

    // Check if class is graduated
    $graduationCheck = validateClassNotGraduated($classId, 'Lesson plan management');
    if (!$graduationCheck['success']) {
        jsonResponse(false, $graduationCheck['message']);
        exit;
    }

    // Validate teacher ID
    if (empty($teacherId)) {
        jsonResponse(false, 'Teacher ID is required');
        exit;
    }

    // If updating, verify ownership/permission
    if ($lessonPlanId) {
        $checkSql = "SELECT teacher_id FROM lesson_plans WHERE id = ?";
        $checkStmt = executeQuery($checkSql, 'i', [$lessonPlanId]);
        $existingPlan = fetchOne($checkStmt);
        
        if (!$existingPlan) {
            jsonResponse(false, 'Lesson plan not found');
            exit;
        }
        
        // Teachers can only edit their own lesson plans (unless Super Admin/Admin)
        if ($isTeacher && !$isSuperAdmin && !$isAdmin && $existingPlan['teacher_id'] != $teacherId) {
            jsonResponse(false, 'You do not have permission to edit this lesson plan');
            exit;
        }
    }

    // Verify that class and subject are assigned to this teacher (skip for Super Admin and Admin)
    if ($isTeacher && !$isSuperAdmin && !$isAdmin) {
        $sql = "SELECT id FROM class_subjects 
                WHERE class_id = ? AND subject_id = ? AND teacher_id = ? AND session_id = ?";
        $stmt = executeQuery($sql, 'iiii', [$classId, $subjectId, $teacherId, $currentSession['id']]);
        $assignment = fetchOne($stmt);

        if (!$assignment) {
            jsonResponse(false, 'Unauthorized: This class/subject is not assigned to you');
            exit;
        }
    }
    if ($lessonPlanId) {
        // Update existing lesson plan
        $updateSql = "UPDATE lesson_plans SET class_id = ?, subject_id = ?, lesson_title = ?, lesson_date = ?, 
                     objectives = ?, content = ?, methodology = ?, resources = ?, assessment = ?, status = ? 
                     WHERE id = ?";
        $stmt = executeQuery($updateSql, 'iissssssssi', [
            $classId,
            $subjectId,
            sanitize($lessonTitle),
            $lessonDate,
            sanitize($objectives),
            sanitize($content),
            sanitize($methodology),
            sanitize($resources),
            sanitize($assessment),
            $status,
            $lessonPlanId
        ]);
        
        if ($stmt !== false) {
            if (function_exists('logActivity')) {
                logActivity($currentUser['id'], 'Update Lesson Plan', 'Academics', "Updated lesson plan: $lessonTitle");
            }
            jsonResponse(true, 'Lesson plan updated successfully');
        } else {
            global $conn;
            $error = $conn->error ?? 'Unknown database error';
            jsonResponse(false, 'Failed to update lesson plan: ' . $error);
        }
    } else {
        // Insert new lesson plan
$insertSql = "INSERT INTO lesson_plans (teacher_id, class_id, subject_id, session_id, lesson_title, lesson_date, 
                                       objectives, content, methodology, resources, assessment, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = executeQuery($insertSql, 'iiiissssssss', [
    $teacherId,
    $classId,
    $subjectId,
    $currentSession['id'],
    sanitize($lessonTitle),
    $lessonDate,
    sanitize($objectives),
    sanitize($content),
    sanitize($methodology),
    sanitize($resources),
    sanitize($assessment),
    $status
]);

        if ($stmt !== false) {
            if (function_exists('logActivity')) {
                logActivity($currentUser['id'], 'Create Lesson Plan', 'Academics', "Created lesson plan: $lessonTitle");
            }
            jsonResponse(true, 'Lesson plan saved successfully');
        } else {
            global $conn;
            $error = $conn->error ?? 'Unknown database error';
            jsonResponse(false, 'Failed to save lesson plan: ' . $error);
        }
    }

} catch (Exception $e) {
    error_log('Save lesson plan error: ' . $e->getMessage());
    jsonResponse(false, 'Error saving lesson plan: ' . $e->getMessage());
    exit;
}

