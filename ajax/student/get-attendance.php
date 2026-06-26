<?php
/**
 * Get Student Attendance
 * 
 * Returns attendance records for the logged-in student
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

    // Get date filters
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // Get attendance records
    $attendanceSql = "SELECT 
        sa.attendance_date as date,
        sa.status,
        sa.remarks,
        s.subject_name,
        s.subject_code
        FROM student_attendance sa
        INNER JOIN subjects s ON sa.subject_id = s.id
        WHERE sa.student_id = ? AND sa.attendance_date >= ? AND sa.attendance_date <= ?
        ORDER BY sa.attendance_date DESC";
    $stmt = executeQuery($attendanceSql, 'iss', [$studentId, $startDate, $endDate]);
    $attendance = fetchAll($stmt);

    // Calculate overall statistics
    $totalDays = count($attendance);
    $present = 0;
    $absent = 0;
    $late = 0;
    $leave = 0;
    
    foreach ($attendance as $record) {
        switch ($record['status']) {
            case 'Present':
                $present++;
                break;
            case 'Absent':
                $absent++;
                break;
            case 'Late':
                $late++;
                break;
            case 'Leave':
                $leave++;
                break;
        }
    }
    
    $overallPercentage = $totalDays > 0 ? round(($present / $totalDays) * 100, 2) : 0;
    $absencePercentage = $totalDays > 0 ? round(($absent / $totalDays) * 100, 2) : 0;

    // Calculate statistics by time period
    $now = new DateTime();
    $weekStart = clone $now;
    $weekStart->modify('monday this week');
    $monthStart = clone $now;
    $monthStart->modify('first day of this month');
    $yearStart = clone $now;
    $yearStart->modify('first day of January this year');
    
    // This week
    $weekSql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent
        FROM student_attendance sa
        WHERE sa.student_id = ? AND sa.attendance_date >= ? AND sa.attendance_date <= ?";
    $stmt = executeQuery($weekSql, 'iss', [$studentId, $weekStart->format('Y-m-d'), $now->format('Y-m-d')]);
    $weekStats = fetchOne($stmt);
    $weekTotal = $weekStats['total'] ?? 0;
    $weekAbsent = $weekStats['absent'] ?? 0;
    $weekAbsencePercent = $weekTotal > 0 ? round(($weekAbsent / $weekTotal) * 100, 2) : 0;
    
    // This month
    $monthSql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent
        FROM student_attendance sa
        WHERE sa.student_id = ? AND sa.attendance_date >= ? AND sa.attendance_date <= ?";
    $stmt = executeQuery($monthSql, 'iss', [$studentId, $monthStart->format('Y-m-d'), $now->format('Y-m-d')]);
    $monthStats = fetchOne($stmt);
    $monthTotal = $monthStats['total'] ?? 0;
    $monthAbsent = $monthStats['absent'] ?? 0;
    $monthAbsencePercent = $monthTotal > 0 ? round(($monthAbsent / $monthTotal) * 100, 2) : 0;
    
    // This year
    $yearSql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent
        FROM student_attendance sa
        WHERE sa.student_id = ? AND sa.attendance_date >= ? AND sa.attendance_date <= ?";
    $stmt = executeQuery($yearSql, 'iss', [$studentId, $yearStart->format('Y-m-d'), $now->format('Y-m-d')]);
    $yearStats = fetchOne($stmt);
    $yearTotal = $yearStats['total'] ?? 0;
    $yearAbsent = $yearStats['absent'] ?? 0;
    $yearAbsencePercent = $yearTotal > 0 ? round(($yearAbsent / $yearTotal) * 100, 2) : 0;

    // Calculate statistics by subject
    $subjectStatsSql = "SELECT 
        s.subject_name,
        s.subject_code,
        COUNT(*) as total_days,
        SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN sa.status = 'Leave' THEN 1 ELSE 0 END) as `leave`
        FROM student_attendance sa
        INNER JOIN subjects s ON sa.subject_id = s.id
        WHERE sa.student_id = ? AND sa.attendance_date >= ? AND sa.attendance_date <= ?
        GROUP BY s.id, s.subject_name, s.subject_code
        ORDER BY s.subject_name";
    $stmt = executeQuery($subjectStatsSql, 'iss', [$studentId, $startDate, $endDate]);
    $subjectStats = fetchAll($stmt);
    
    $subjectStatsFormatted = [];
    foreach ($subjectStats as $stat) {
        $subjectTotal = $stat['total_days'];
        $subjectPresent = $stat['present'];
        $subjectAbsent = $stat['absent'];
        $subjectLate = $stat['late'];
        // Access 'leave' column (escaped with backticks in SQL)
        $subjectLeave = isset($stat['leave']) ? $stat['leave'] : 0;
        $subjectPercentage = $subjectTotal > 0 ? round(($subjectPresent / $subjectTotal) * 100, 2) : 0;
        
        $subjectStatsFormatted[] = [
            'subject_name' => $stat['subject_name'],
            'subject_code' => $stat['subject_code'],
            'total_days' => $subjectTotal,
            'present' => $subjectPresent,
            'absent' => $subjectAbsent,
            'late' => $subjectLate,
            'leave' => $subjectLeave,
            'attendance_percentage' => $subjectPercentage,
        ];
    }

    // Format attendance records
    $records = [];
    foreach ($attendance as $record) {
        $records[] = [
            'date' => $record['date'],
            'status' => $record['status'],
            'remarks' => $record['remarks'],
            'subject_name' => $record['subject_name'],
            'subject_code' => $record['subject_code'],
        ];
    }

    $response = [
        'overall' => [
            'total_days' => $totalDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'leave' => $leave,
            'attendance_percentage' => $overallPercentage,
            'absence_percentage' => $absencePercentage,
        ],
        'time_periods' => [
            'this_week' => [
                'total_days' => $weekTotal,
                'absent' => $weekAbsent,
                'absence_percentage' => $weekAbsencePercent,
            ],
            'this_month' => [
                'total_days' => $monthTotal,
                'absent' => $monthAbsent,
                'absence_percentage' => $monthAbsencePercent,
            ],
            'this_year' => [
                'total_days' => $yearTotal,
                'absent' => $yearAbsent,
                'absence_percentage' => $yearAbsencePercent,
            ],
        ],
        'by_subject' => $subjectStatsFormatted,
        'records' => $records,
    ];

    jsonResponse(true, 'Attendance records retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

