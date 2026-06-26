<?php
/**
 * Verify Graduation Requirements
 * 
 * Checks all students in a class against graduation requirements
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to perform this action');
}

try {
    $classId = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $branchId = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;
    
    if (!$classId || !$sessionId) {
        jsonResponse(false, 'Class and session are required');
    }
    
    // Get requirements (with defaults)
    $minGpa = isset($_POST['min_gpa']) ? floatval($_POST['min_gpa']) : 2.00;
    $minAttendance = isset($_POST['min_attendance']) ? floatval($_POST['min_attendance']) : 75.00;
    $requiredSubjects = isset($_POST['required_subjects']) ? intval($_POST['required_subjects']) : 0;
    $minPassing = isset($_POST['min_passing']) ? floatval($_POST['min_passing']) : 50.00;
    
    // Get all students in the class
    $studentsSql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name,
                    CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as full_name
                    FROM students s
                    LEFT JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN sections sec ON s.current_section_id = sec.id
                    LEFT JOIN branches b ON s.branch_id = b.id
                    WHERE s.current_class_id = ? AND s.status IN ('Active', 'Graduated')";
    
    if ($branchId) {
        $studentsSql .= " AND s.branch_id = $branchId";
    }
    
    $studentsSql .= " ORDER BY s.first_name, s.last_name";
    
    $studentsStmt = executeQuery($studentsSql, 'i', [$classId]);
    $students = fetchAll($studentsStmt);
    
    if (empty($students)) {
        jsonResponse(false, 'No students found in the selected class');
    }
    
    // Get grading scheme for GPA calculation
    $schemeSql = "SELECT * FROM grading_schemes WHERE is_active = 1 AND is_default = 1 LIMIT 1";
    $schemeStmt = executeQuery($schemeSql);
    $scheme = fetchOne($schemeStmt);
    
    if (!$scheme) {
        jsonResponse(false, 'No active grading scheme found. Please configure a grading scheme first.');
    }
    
    $gradeItemsSql = "SELECT * FROM grading_scale_items WHERE grading_scheme_id = ? ORDER BY min_percentage DESC";
    $gradeItemsStmt = executeQuery($gradeItemsSql, 'i', [$scheme['id']]);
    $gradeItems = fetchAll($gradeItemsStmt);
    
    // Get total subjects for this class
    $totalSubjectsSql = "SELECT COUNT(DISTINCT cs.subject_id) as total 
                         FROM class_subjects cs 
                         WHERE cs.class_id = ?";
    $totalSubjectsStmt = executeQuery($totalSubjectsSql, 'i', [$classId]);
    $totalSubjectsData = fetchOne($totalSubjectsStmt);
    $totalSubjects = $totalSubjectsData ? intval($totalSubjectsData['total']) : 0;
    
    if ($requiredSubjects == 0) {
        $requiredSubjects = $totalSubjects; // All subjects required if not specified
    }
    
    $verifiedStudents = [];
    
    foreach ($students as $student) {
        $studentId = $student['id'];
        
        // Check if student already has a graduation certificate
        $existingCertSql = "SELECT id, certificate_number FROM certificates 
                           WHERE student_id = ? AND certificate_type = 'graduation' AND status = 'issued'";
        $existingCertStmt = executeQuery($existingCertSql, 'i', [$studentId]);
        $existingCert = fetchOne($existingCertStmt);
        
        // Calculate GPA/CGPA
        $gpa = calculateStudentGPA($studentId, $sessionId, $classId, $gradeItems);
        
        // Calculate attendance percentage
        $attendancePercentage = calculateAttendancePercentage($studentId, $sessionId, $classId);
        
        // Count completed subjects (subjects with passing marks)
        $completedSubjects = countCompletedSubjects($studentId, $sessionId, $classId, $minPassing);
        
        // Verify requirements
        $gpaMet = $gpa >= $minGpa;
        $attendanceMet = $attendancePercentage >= $minAttendance;
        $subjectsMet = $completedSubjects >= $requiredSubjects;
        
        // Determine eligibility status
        $eligibilityStatus = 'eligible';
        if (!$gpaMet || !$attendanceMet || !$subjectsMet) {
            $eligibilityStatus = 'not_eligible';
        }
        
        // If student already graduated but requirements not met, mark as pending review
        if ($student['status'] === 'Graduated' && $eligibilityStatus === 'not_eligible') {
            $eligibilityStatus = 'pending';
        }
        
        $verifiedStudents[] = [
            'id' => $studentId,
            'student_id' => $student['student_id'],
            'full_name' => $student['full_name'],
            'current_status' => $student['status'],
            'gpa' => $gpa,
            'attendance_percentage' => $attendancePercentage,
            'completed_subjects' => $completedSubjects,
            'total_subjects' => $totalSubjects,
            'has_existing_certificate' => $existingCert ? true : false,
            'existing_certificate_number' => $existingCert ? $existingCert['certificate_number'] : null,
            'eligibility_status' => $eligibilityStatus,
            'requirements' => [
                'min_gpa' => $minGpa,
                'min_attendance' => $minAttendance,
                'required_subjects' => $requiredSubjects,
                'min_passing' => $minPassing,
                'gpa_met' => $gpaMet,
                'attendance_met' => $attendanceMet,
                'subjects_met' => $subjectsMet
            ]
        ];
    }
    
    jsonResponse(true, 'Graduation requirements verified successfully', [
        'students' => $verifiedStudents,
        'requirements' => [
            'min_gpa' => $minGpa,
            'min_attendance' => $minAttendance,
            'required_subjects' => $requiredSubjects,
            'min_passing' => $minPassing,
            'total_subjects' => $totalSubjects
        ]
    ]);
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

/**
 * Calculate student GPA for a session
 */
function calculateStudentGPA($studentId, $sessionId, $classId, $gradeItems) {
    $sql = "SELECT 
            es.subject_id, s.subject_name,
            AVG(sm.marks_obtained / es.total_marks * 100) as avg_percentage
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN subjects s ON es.subject_id = s.id
            WHERE sm.student_id = ? 
            AND e.session_id = ? 
            AND e.class_id = ?
            AND sm.is_absent = 0
            GROUP BY es.subject_id, s.subject_name";
    
    $stmt = executeQuery($sql, 'iii', [$studentId, $sessionId, $classId]);
    $marks = fetchAll($stmt);
    
    if (empty($marks)) {
        return 0;
    }
    
    $totalGradePoints = 0;
    $subjectCount = 0;
    
    foreach ($marks as $mark) {
        $percentage = floatval($mark['avg_percentage']);
        
        // Find matching grade
        foreach ($gradeItems as $item) {
            if ($percentage >= $item['min_percentage'] && $percentage <= $item['max_percentage']) {
                $totalGradePoints += floatval($item['grade_point']);
                $subjectCount++;
                break;
            }
        }
    }
    
    return $subjectCount > 0 ? round($totalGradePoints / $subjectCount, 2) : 0;
}

/**
 * Calculate attendance percentage
 */
function calculateAttendancePercentage($studentId, $sessionId, $classId) {
    // Get session dates
    $sessionSql = "SELECT start_date, end_date FROM academic_sessions WHERE id = ?";
    $sessionStmt = executeQuery($sessionSql, 'i', [$sessionId]);
    $session = fetchOne($sessionStmt);
    
    if (!$session) {
        return 0;
    }
    
    // Count total attendance records for this student in this class during session
    $attendanceSql = "SELECT COUNT(*) as total,
                      SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
                      FROM student_attendance 
                      WHERE student_id = ? 
                      AND class_id = ?
                      AND attendance_date BETWEEN ? AND ?";
    
    $attendanceStmt = executeQuery($attendanceSql, 'iiss', [
        $studentId, 
        $classId, 
        $session['start_date'], 
        $session['end_date']
    ]);
    $attendance = fetchOne($attendanceStmt);
    
    if (!$attendance || $attendance['total'] == 0) {
        return 0;
    }
    
    return round(($attendance['present'] / $attendance['total']) * 100, 2);
}

/**
 * Count completed subjects (subjects with passing percentage)
 */
function countCompletedSubjects($studentId, $sessionId, $classId, $minPassing) {
    $sql = "SELECT 
            es.subject_id,
            AVG(sm.marks_obtained / es.total_marks * 100) as avg_percentage
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            WHERE sm.student_id = ? 
            AND e.session_id = ? 
            AND e.class_id = ?
            AND sm.is_absent = 0
            GROUP BY es.subject_id
            HAVING avg_percentage >= ?";
    
    $stmt = executeQuery($sql, 'iiid', [$studentId, $sessionId, $classId, $minPassing]);
    $completed = fetchAll($stmt);
    
    return count($completed);
}


