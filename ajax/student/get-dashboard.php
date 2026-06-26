<?php
/**
 * Get Student Dashboard Statistics
 * 
 * Returns dashboard statistics for the logged-in student
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
    // Support both session and user_id parameter authentication
    $userId = null;
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        $_SESSION['user_id'] = $userId;
    } elseif (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } else {
        jsonResponse(false, 'User not logged in');
        exit;
    }

    // Verify user is a student
    $userCheckSql = "SELECT u.id, r.role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($userCheckSql, 'i', [$userId]);
    $user = fetchOne($stmt);

    if (!$user || $user['role_name'] !== 'Student') {
        jsonResponse(false, 'Unauthorized: Student access only');
        exit;
    }

    // Get student ID
    $studentSql = "SELECT id FROM students WHERE user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student) {
        jsonResponse(false, 'Student record not found');
        exit;
    }

    $studentId = $student['id'];

    // Get attendance percentage (last 30 days)
    $attendanceSql = "SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days
        FROM student_attendance 
        WHERE student_id = ? AND attendance_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = executeQuery($attendanceSql, 'i', [$studentId]);
    $attendance = fetchOne($stmt);
    $attendancePercentage = $attendance['total_days'] > 0 
        ? round(($attendance['present_days'] / $attendance['total_days']) * 100, 2)
        : 0;

    // Get student's class and section info
    $studentInfoSql = "SELECT current_class_id, current_section_id FROM students WHERE id = ?";
    $stmt = executeQuery($studentInfoSql, 'i', [$studentId]);
    $studentInfo = fetchOne($stmt);
    $classId = $studentInfo['current_class_id'] ?? null;
    $sectionId = $studentInfo['current_section_id'] ?? null;

    // Get current session
    $currentSession = getCurrentSession();
    $sessionId = $currentSession ? $currentSession['id'] : null;

    // Get total classes (subjects) for student's class
    $totalClasses = 0;
    if ($classId && $sessionId) {
        $classesSql = "SELECT COUNT(DISTINCT cs.subject_id) as total_classes
                       FROM class_subjects cs
                       WHERE cs.class_id = ? AND cs.session_id = ?";
        $stmt = executeQuery($classesSql, 'ii', [$classId, $sessionId]);
        $classes = fetchOne($stmt);
        $totalClasses = $classes['total_classes'] ?? 0;
    }

    // Get pending fees
    // Note: Use fee_invoices table, not student_fees (which doesn't exist)
    $pendingFees = 0.0;
    if ($sessionId) {
        $feesSql = "SELECT SUM(due_amount) as pending
                    FROM fee_invoices
                    WHERE student_id = ? AND session_id = ? AND status IN ('Unpaid', 'Partially Paid', 'Overdue')";
        $stmt = executeQuery($feesSql, 'ii', [$studentId, $sessionId]);
        $fees = fetchOne($stmt);
        $pendingFees = $fees['pending'] ?? 0.0;
    }

    // Get total assignments for student's class
    $assignmentsCount = 0;
    if ($classId && $sessionId) {
        $assignmentsSql = "SELECT COUNT(*) as total_assignments
                          FROM assignments a
                          WHERE a.class_id = ? AND a.session_id = ?";
        $stmt = executeQuery($assignmentsSql, 'ii', [$classId, $sessionId]);
        $assignments = fetchOne($stmt);
        $assignmentsCount = $assignments['total_assignments'] ?? 0;
    }

    // Get assignments due soon (next 7 days)
    $dueSoonCount = 0;
    if ($classId && $sessionId) {
        $dueSoonSql = "SELECT COUNT(*) as due_soon
                       FROM assignments a
                       WHERE a.class_id = ? AND a.session_id = ? 
                       AND a.due_date >= CURDATE() 
                       AND a.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $stmt = executeQuery($dueSoonSql, 'ii', [$classId, $sessionId]);
        $dueSoon = fetchOne($stmt);
        $dueSoonCount = $dueSoon['due_soon'] ?? 0;
    }

    // Get upcoming exams (next 30 days)
    // Note: exam_schedule table (singular) doesn't have class_id/section_id - those are in exams table
    $upcomingExams = 0;
    if ($classId && $sessionId) {
        $examsSql = "SELECT COUNT(DISTINCT es.id) as upcoming_exams
                     FROM exam_schedule es
                     INNER JOIN exams e ON es.exam_id = e.id
                     WHERE e.class_id = ? AND e.session_id = ?
                     AND es.exam_date >= CURDATE() 
                     AND es.exam_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        $stmt = executeQuery($examsSql, 'ii', [$classId, $sessionId]);
        $exams = fetchOne($stmt);
        $upcomingExams = $exams['upcoming_exams'] ?? 0;
    }

    // Get exams scheduled (all future exams)
    // Note: exam_schedule table (singular) doesn't have class_id/section_id - those are in exams table
    $examsScheduled = 0;
    if ($classId && $sessionId) {
        $scheduledSql = "SELECT COUNT(DISTINCT es.id) as scheduled_exams
                        FROM exam_schedule es
                        INNER JOIN exams e ON es.exam_id = e.id
                        WHERE e.class_id = ? AND e.session_id = ?
                        AND es.exam_date >= CURDATE()";
        $stmt = executeQuery($scheduledSql, 'ii', [$classId, $sessionId]);
        $scheduled = fetchOne($stmt);
        $examsScheduled = $scheduled['scheduled_exams'] ?? 0;
    }

    // Get today's items (assignments and exams due today)
    $todayCount = 0;
    if ($classId && $sessionId) {
        // Today's assignments
        $todayAssignmentsSql = "SELECT COUNT(*) as today_assignments
                               FROM assignments a
                               WHERE a.class_id = ? AND a.session_id = ?
                               AND DATE(a.due_date) = CURDATE()";
        $stmt = executeQuery($todayAssignmentsSql, 'ii', [$classId, $sessionId]);
        $todayAssignments = fetchOne($stmt);
        $todayAssignmentsCount = $todayAssignments['today_assignments'] ?? 0;

        // Today's exams
        // Note: exam_schedule table (singular) doesn't have class_id/section_id - those are in exams table
        $todayExamsCount = 0;
        if ($classId && $sessionId) {
            $todayExamsSql = "SELECT COUNT(*) as today_exams
                             FROM exam_schedule es
                             INNER JOIN exams e ON es.exam_id = e.id
                             WHERE e.class_id = ? AND e.session_id = ?
                             AND DATE(es.exam_date) = CURDATE()";
            $stmt = executeQuery($todayExamsSql, 'ii', [$classId, $sessionId]);
            $todayExams = fetchOne($stmt);
            $todayExamsCount = $todayExams['today_exams'] ?? 0;
        }

        $todayCount = $todayAssignmentsCount + $todayExamsCount;
    }

    $response = [
        'attendance_percentage' => $attendancePercentage,
        'total_classes' => $totalClasses,
        'pending_fees' => floatval($pendingFees),
        'upcoming_exams' => $upcomingExams,
        'assignments' => $assignmentsCount,
        'due_soon' => $dueSoonCount,
        'exams_scheduled' => $examsScheduled,
        'today' => $todayCount,
    ];

    jsonResponse(true, 'Dashboard statistics retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

