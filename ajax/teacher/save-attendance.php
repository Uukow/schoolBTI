<?php
/**
 * Save Attendance - AJAX Endpoint
 * 
 * Save student attendance (teacher only, fully isolated)
 * STRICT: Class must be assigned to teacher via class_subjects table
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

    if (!hasRole(['Teacher', 'Super Admin'])) {
        jsonResponse(false, 'Permission denied', null, 403);
        exit;
    }

    $isSuperAdmin = hasRole(['Super Admin']);

    $teacher = null;
    $teacherId = null;

    if (!$isSuperAdmin) {
        $teacher = getTeacherByUserId($currentUser['id']);
        if (!$teacher) {
            jsonResponse(false, 'Teacher profile not found');
            exit;
        }
        $teacherId = $teacher['id'];
    }

    $date = $_POST['date'] ?? '';
    $classId = $_POST['class_id'] ?? '';
    $subjectId = $_POST['subject_id'] ?? '';
    $attendanceJson = $_POST['attendance'] ?? '';

    // Decode attendance JSON
    $attendance = [];
    if (!empty($attendanceJson)) {
        $attendance = json_decode($attendanceJson, true);
        if (!is_array($attendance)) {
            $attendance = [];
        }
    }

    if (empty($date) || empty($classId) || empty($subjectId) || empty($attendance)) {
        jsonResponse(false, 'Invalid data provided. Date, class, and subject are required.');
        exit;
    }

    // Get current session and validate date is within session period
    $currentSession = getCurrentSession();
    if (!$currentSession) {
        jsonResponse(false, 'No active academic session found. Please contact administrator.');
        exit;
    }

    $sessionStartDate = $currentSession['start_date'] ?? null;
    $sessionEndDate = $currentSession['end_date'] ?? null;

    // Validate date is within session period
    if ($sessionStartDate && $sessionEndDate) {
        if ($date < $sessionStartDate || $date > $sessionEndDate) {
            $startFormatted = formatDate($sessionStartDate, 'd M Y');
            $endFormatted = formatDate($sessionEndDate, 'd M Y');
            jsonResponse(false, 'Selected date is outside the current academic session period (' . 
                         $startFormatted . ' to ' . $endFormatted . ').');
            exit;
        }
    }

    // STRICT VERIFICATION: Verify that this class AND subject are assigned to the teacher
    if (!$isSuperAdmin) {
    $verifySql = "SELECT id FROM class_subjects 
                  WHERE class_id = ? AND subject_id = ? AND teacher_id = ? AND session_id = ?";
    $verifyStmt = executeQuery($verifySql, 'iiii', [$classId, $subjectId, $teacherId, $currentSession['id']]);
    $verification = fetchOne($verifyStmt);
    
    if (!$verification) {
        jsonResponse(false, 'Unauthorized: This subject is not assigned to you for this class.');
    }
    
    // Also verify via timetable
    $dayOfWeek = date('l', strtotime($date));
    $timetableVerifySql = "SELECT id FROM timetable 
                           WHERE class_id = ? AND subject_id = ? AND teacher_id = ? 
                           AND day_of_week = ? AND session_id = ?";
    $timetableVerifyStmt = executeQuery($timetableVerifySql, 'iiiss', [$classId, $subjectId, $teacherId, $dayOfWeek, $currentSession['id']]);
    $timetableVerification = fetchOne($timetableVerifyStmt);
    
        if (!$timetableVerification) {
            jsonResponse(false, 'Unauthorized: You do not teach this subject on ' . $dayOfWeek . '.');
            exit;
        }
    }

    // STRICT: Verify that all students belong to teacher's assigned class
    if (!$isSuperAdmin) {
    $studentIds = array_keys($attendance);
    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $types = str_repeat('i', count($studentIds));

    // STRICT QUERY: WHERE teacher_id = logged_in_teacher_id AND class_id = selected_class_id
    $sql = "SELECT DISTINCT s.id 
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
            WHERE s.id IN ($placeholders) AND cs.teacher_id = ? AND cs.class_id = ? AND cs.session_id = ?";
    $params = array_merge($studentIds, [$teacherId, $classId, $currentSession['id']]);
    $types .= 'iii';

    $stmt = executeQuery($sql, $types, $params);
    $validStudents = fetchAll($stmt);
    $validStudentIds = array_column($validStudents, 'id');

        // Check if all students are valid
        if (count($validStudentIds) !== count($studentIds)) {
            jsonResponse(false, 'Unauthorized: Some students do not belong to your assigned class');
            exit;
        }
    } else {
        // Super Admin: Verify students are in the selected class
        $studentIds = array_keys($attendance);
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $types = str_repeat('i', count($studentIds));

        $sql = "SELECT id FROM students 
                WHERE id IN ($placeholders) AND current_class_id = ?";
        $params = array_merge($studentIds, [$classId]);
        $types .= 'i';

        $stmt = executeQuery($sql, $types, $params);
        $validStudents = fetchAll($stmt);
        $validStudentIds = array_column($validStudents, 'id');

        if (count($validStudentIds) !== count($studentIds)) {
            jsonResponse(false, 'Some students do not belong to the selected class');
            exit;
        }
    }

    // Check if subject_id column exists and if old constraint still exists
    global $conn;
    $hasSubjectId = false;
    $hasOldConstraint = false;

    try {
    // Check if subject_id column exists
    $checkColumnSql = "SHOW COLUMNS FROM student_attendance LIKE 'subject_id'";
    $columnResult = $conn->query($checkColumnSql);
    $hasSubjectId = ($columnResult->num_rows > 0);
    
    // Check if old constraint still exists
    $checkConstraintSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_unique'";
    $constraintResult = $conn->query($checkConstraintSql);
    $hasOldConstraint = ($constraintResult->num_rows > 0);
    
    if (!$hasSubjectId) {
        jsonResponse(false, 'Database migration required! The subject_id column is missing. Please run: <a href="' . APP_URL . 'database/run_attendance_migration.php" target="_blank">Run Migration</a>');
    }
    
        if ($hasOldConstraint) {
            jsonResponse(false, 'Database migration incomplete! The old unique constraint still exists. Please run the migration script to update constraints: <a href="' . APP_URL . 'database/run_attendance_migration.php" target="_blank">Run Migration</a>');
            exit;
        }
    } catch (Exception $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage() . '. Please ensure the database migration has been completed.');
        exit;
    }

    // Save attendance
    $successCount = 0;
    $errorCount = 0;
    $errorMessages = [];

    beginTransaction();

    try {
    // STEP 1: Delete all existing attendance records for this class + subject + date combination
    // This ensures complete replacement when teacher saves again for the same date and period
    
    // Build delete query based on whether subject_id column exists
    if ($hasSubjectId) {
        // New schema: delete by class + subject + date
        $deleteSql = "DELETE sa FROM student_attendance sa
                      INNER JOIN students s ON sa.student_id = s.id
                      WHERE sa.class_id = ? AND sa.subject_id = ? AND sa.attendance_date = ?
                      AND s.current_class_id = ?";
        
        // For teachers, also verify they marked the original records (security)
        if (!$isSuperAdmin) {
            $deleteSql .= " AND sa.marked_by = ?";
            $deleteParams = [$classId, $subjectId, $date, $classId, $currentUser['id']];
            $deleteTypes = 'iisii';
        } else {
            $deleteParams = [$classId, $subjectId, $date, $classId];
            $deleteTypes = 'iisi';
        }
    } else {
        // Old schema: delete by class + date only (fallback)
        $deleteSql = "DELETE sa FROM student_attendance sa
                      INNER JOIN students s ON sa.student_id = s.id
                      WHERE sa.class_id = ? AND sa.attendance_date = ?
                      AND s.current_class_id = ?";
        
        if (!$isSuperAdmin) {
            $deleteSql .= " AND sa.marked_by = ?";
            $deleteParams = [$classId, $date, $classId, $currentUser['id']];
            $deleteTypes = 'isii';
        } else {
            $deleteParams = [$classId, $date, $classId];
            $deleteTypes = 'isi';
        }
    }
    
    $deleteStmt = executeQuery($deleteSql, $deleteTypes, $deleteParams);
    
    // Get deleted count
    $deletedCount = $conn->affected_rows ?? 0;
    
    // STEP 2: Insert all new attendance records
    foreach ($attendance as $studentId => $data) {
        if (!in_array($studentId, $validStudentIds)) {
            $errorCount++;
            $errorMessages[] = "Student ID $studentId is not authorized";
            continue;
        }
        
        $status = sanitize($data['status'] ?? 'Present');
        $remarks = sanitize($data['remarks'] ?? '');
        $attendanceId = $data['attendance_id'] ?? null;
        
        // Get student's class and section
        $studentSql = "SELECT current_class_id, current_section_id FROM students WHERE id = ?";
        $studentStmt = executeQuery($studentSql, 'i', [$studentId]);
        $student = fetchOne($studentStmt);
        
        if (!$student) {
            $errorCount++;
            $errorMessages[] = "Student ID $studentId not found";
            continue;
        }
        
        // Verify student is in the correct class
        if ($student['current_class_id'] != $classId) {
            $errorCount++;
            $errorMessages[] = "Student ID $studentId does not belong to this class";
            continue;
        }
        
        // Insert new attendance record (old records already deleted above)
        if ($hasSubjectId) {
            // New schema: include subject_id
            $insertSql = "INSERT INTO student_attendance (student_id, class_id, section_id, subject_id, attendance_date, status, remarks, marked_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $result = executeQuery($insertSql, 'iiiisssi', [
                $studentId,
                $classId,
                $student['current_section_id'],
                $subjectId,
                $date,
                $status,
                $remarks,
                $currentUser['id']
            ]);
        } else {
            // Old schema: without subject_id (should not happen if migration check passed, but fallback)
            $insertSql = "INSERT INTO student_attendance (student_id, class_id, section_id, attendance_date, status, remarks, marked_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            $result = executeQuery($insertSql, 'iiisssi', [
                $studentId,
                $classId,
                $student['current_section_id'],
                $date,
                $status,
                $remarks,
                $currentUser['id']
            ]);
        }
        
        if ($result) {
            $successCount++;
        } else {
            $errorCount++;
            global $conn;
            $errorMessages[] = "Failed to save attendance for student ID $studentId: " . ($conn->error ?? 'Unknown error');
        }
    }
    
    if ($errorCount > 0 && $successCount == 0) {
        rollbackTransaction();
        jsonResponse(false, 'Failed to save attendance. Errors: ' . implode(', ', array_slice($errorMessages, 0, 3)));
    }
    
        commitTransaction();
        
    } catch (Exception $e) {
        rollbackTransaction();
        error_log('Save attendance error: ' . $e->getMessage());
        jsonResponse(false, 'Error saving attendance: ' . $e->getMessage());
        exit;
    }

    // Log activity
    $logMessage = "Marked attendance for $successCount students on $date for class ID: $classId, subject ID: $subjectId";
    if ($deletedCount > 0) {
        $logMessage .= " (Replaced $deletedCount previous records)";
    }
    logActivity($currentUser['id'], 'Mark Attendance', 'Teacher Portal', $logMessage);

    if ($errorCount > 0) {
        jsonResponse(true, "Attendance saved for $successCount students. $errorCount failed." . ($deletedCount > 0 ? " Previous records replaced." : ""));
    } else {
        $message = "Attendance saved successfully for $successCount students.";
        if ($deletedCount > 0) {
            $message .= " Previous attendance records for this date and period have been replaced.";
        }
        jsonResponse(true, $message);
    }

} catch (Exception $e) {
    error_log('Save attendance error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to save attendance: ' . $e->getMessage());
    exit;
}
