<?php
/**
 * Generate Certificates
 * 
 * Core logic for generating certificates with verified academic data and GPA calculation
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
    // Parse students JSON
    $students = isset($_POST['students']) ? json_decode($_POST['students'], true) : [];
    
    if (empty($students)) {
        jsonResponse(false, 'No students selected');
    }
    
    // Validate required fields
    $templateId = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
    $gradingSchemeId = isset($_POST['grading_scheme_id']) ? intval($_POST['grading_scheme_id']) : 0;
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $classId = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    
    if (!$templateId || !$gradingSchemeId || !$sessionId || !$classId) {
        jsonResponse(false, 'Missing required parameters');
    }
    
    $issueDate = sanitize($_POST['issue_date'] ?? date('Y-m-d'));
    $validUntil = !empty($_POST['valid_until']) ? sanitize($_POST['valid_until']) : null;
    $remarks = sanitize($_POST['remarks'] ?? '');
    $includeGrades = isset($_POST['include_grades']) ? 1 : 0;
    $includeGpa = isset($_POST['include_gpa']) ? 1 : 0;
    $includeAttendance = isset($_POST['include_attendance']) ? 1 : 0;
    $includeRank = isset($_POST['include_rank']) ? 1 : 0;
    
    global $conn;
    
    // Get template
    $templateSql = "SELECT * FROM certificate_templates WHERE id = ?";
    $templateStmt = executeQuery($templateSql, 'i', [$templateId]);
    $template = fetchOne($templateStmt);
    
    if (!$template) {
        jsonResponse(false, 'Template not found');
    }
    
    // Get grading scheme with items
    $schemeSql = "SELECT * FROM grading_schemes WHERE id = ?";
    $schemeStmt = executeQuery($schemeSql, 'i', [$gradingSchemeId]);
    $scheme = fetchOne($schemeStmt);
    
    if (!$scheme) {
        jsonResponse(false, 'Grading scheme not found');
    }
    
    $gradeItemsSql = "SELECT * FROM grading_scale_items WHERE grading_scheme_id = ? ORDER BY min_percentage DESC";
    $gradeItemsStmt = executeQuery($gradeItemsSql, 'i', [$gradingSchemeId]);
    $gradeItems = fetchAll($gradeItemsStmt);
    
    $conn->begin_transaction();
    
    $generatedCertificates = [];
    $generatedCount = 0;
    
    foreach ($students as $student) {
        $studentId = intval($student['id']);
        
        // Get student details
        $studentSql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name
                      FROM students s
                      LEFT JOIN classes c ON s.current_class_id = c.id
                      LEFT JOIN sections sec ON s.current_section_id = sec.id
                      LEFT JOIN branches b ON s.branch_id = b.id
                      WHERE s.id = ?";
        $studentStmt = executeQuery($studentSql, 'i', [$studentId]);
        $studentData = fetchOne($studentStmt);
        
        if (!$studentData) {
            continue; // Skip if student not found
        }
        
        // Calculate GPA/CGPA from exam marks
        $academicData = calculateAcademicData($studentId, $sessionId, $classId, $gradeItems, $scheme);
        
        // Get attendance if requested
        $attendancePercentage = null;
        if ($includeAttendance) {
            $attendanceSql = "SELECT COUNT(*) as total,
                             SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
                             FROM student_attendance 
                             WHERE student_id = ?";
            $attendanceStmt = executeQuery($attendanceSql, 'i', [$studentId]);
            $attendance = fetchOne($attendanceStmt);
            
            if ($attendance && $attendance['total'] > 0) {
                $attendancePercentage = round(($attendance['present'] / $attendance['total']) * 100, 2);
            }
        }
        
        // Generate unique certificate number
        $certificateNumber = generateCertificateNumber($template['certificate_type']);
        
        // Generate verification code
        $verificationCode = generateToken(16);
        
        // Generate QR code data (URL to verify certificate)
        $qrCodeData = APP_URL . 'verify-certificate.php?code=' . $verificationCode;
        
        // Insert certificate record
        $insertSql = "INSERT INTO certificates (
            certificate_number, verification_code, template_id, student_id, 
            session_id, class_id, certificate_type, issue_date, valid_until,
            academic_data, gpa, cgpa, attendance_percentage, class_rank,
            remarks, status, issued_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'issued', ?, NOW())";
        
        $academicDataJson = json_encode($academicData);
        $gpa = $academicData['gpa'] ?? null;
        $cgpa = $academicData['cgpa'] ?? null;
        $classRank = $includeRank ? calculateClassRank($studentId, $sessionId, $classId) : null;
        $issuedBy = $_SESSION['user_id'];
        
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param('ssiiiiisssdddisi', 
            $certificateNumber, $verificationCode, $templateId, $studentId, 
            $sessionId, $classId, $template['certificate_type'], $issueDate, $validUntil,
            $academicDataJson, $gpa, $cgpa, $attendancePercentage, $classRank,
            $remarks, $issuedBy
        );
        
        if ($stmt->execute()) {
            $certificateId = $conn->insert_id;
            
            $generatedCertificates[] = [
                'id' => $certificateId,
                'student_id' => $studentId,
                'student_name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                'certificate_number' => $certificateNumber,
                'verification_code' => $verificationCode
            ];
            
            $generatedCount++;
            
            // Log activity
            logActivity($_SESSION['user_id'], 'Generate Certificate', 'Certificates',
                       "Generated certificate {$certificateNumber} for student ID {$studentData['student_id']}");
        }
    }
    
    $conn->commit();
    
    jsonResponse(true, 'Certificates generated successfully', [
        'generated_count' => $generatedCount,
        'certificates' => $generatedCertificates
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    jsonResponse(false, $e->getMessage());
}

/**
 * Calculate academic data including GPA/CGPA
 */
function calculateAcademicData($studentId, $sessionId, $classId, $gradeItems, $scheme) {
    // Get all exam marks for the student in this session
    $sql = "SELECT 
            es.subject_id, s.subject_name, s.subject_code,
            es.total_marks, es.passing_marks,
            sm.marks_obtained, sm.is_absent,
            e.exam_name, et.exam_name as exam_type_name
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN exam_types et ON e.exam_type_id = et.id
            INNER JOIN subjects s ON es.subject_id = s.id
            WHERE sm.student_id = ? 
            AND e.session_id = ? 
            AND e.class_id = ?
            AND sm.is_absent = 0
            ORDER BY s.subject_name, e.start_date";
    
    $stmt = executeQuery($sql, 'iii', [$studentId, $sessionId, $classId]);
    $marks = fetchAll($stmt);
    
    $subjects = [];
    $totalGradePoints = 0;
    $totalSubjects = 0;
    $totalMarks = 0;
    $obtainedMarks = 0;
    
    // Group marks by subject
    $subjectMarks = [];
    foreach ($marks as $mark) {
        $subjectId = $mark['subject_id'];
        if (!isset($subjectMarks[$subjectId])) {
            $subjectMarks[$subjectId] = [
                'subject_name' => $mark['subject_name'],
                'subject_code' => $mark['subject_code'],
                'marks' => []
            ];
        }
        $subjectMarks[$subjectId]['marks'][] = [
            'exam_name' => $mark['exam_name'],
            'marks_obtained' => $mark['marks_obtained'],
            'total_marks' => $mark['total_marks']
        ];
    }
    
    // Calculate average per subject and assign grade
    foreach ($subjectMarks as $subjectId => $data) {
        $subjectTotal = 0;
        $subjectObtained = 0;
        $examCount = count($data['marks']);
        
        foreach ($data['marks'] as $examMark) {
            $subjectTotal += $examMark['total_marks'];
            $subjectObtained += $examMark['marks_obtained'];
        }
        
        // Calculate average percentage for this subject
        $percentage = $subjectTotal > 0 ? ($subjectObtained / $subjectTotal) * 100 : 0;
        
        // Assign grade based on grading scheme
        $grade = null;
        $gradePoint = 0;
        foreach ($gradeItems as $item) {
            if ($percentage >= $item['min_percentage'] && $percentage <= $item['max_percentage']) {
                $grade = $item['grade_letter'];
                $gradePoint = $item['grade_point'];
                break;
            }
        }
        
        $subjects[] = [
            'subject_name' => $data['subject_name'],
            'subject_code' => $data['subject_code'],
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'grade_points' => $gradePoint,
            'marks_obtained' => $subjectObtained,
            'total_marks' => $subjectTotal
        ];
        
        $totalGradePoints += $gradePoint;
        $totalSubjects++;
        $totalMarks += $subjectTotal;
        $obtainedMarks += $subjectObtained;
    }
    
    // Calculate GPA and overall percentage
    $gpa = $totalSubjects > 0 ? round($totalGradePoints / $totalSubjects, 2) : 0;
    $cgpa = $gpa; // For single session, CGPA = GPA
    $overallPercentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
    
    return [
        'subjects' => $subjects,
        'gpa' => $gpa,
        'cgpa' => $cgpa,
        'total_subjects' => $totalSubjects,
        'overall_percentage' => $overallPercentage,
        'total_marks' => $totalMarks,
        'obtained_marks' => $obtainedMarks
    ];
}

/**
 * Calculate class rank
 */
function calculateClassRank($studentId, $sessionId, $classId) {
    $sql = "SELECT COUNT(*) + 1 as rank
            FROM (
                SELECT s.id, SUM(sm.marks_obtained) as total
                FROM students s
                INNER JOIN student_marks sm ON s.id = sm.student_id
                INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                INNER JOIN exams e ON es.exam_id = e.id
                WHERE e.session_id = ? AND e.class_id = ? 
                AND s.status = 'Active' AND sm.is_absent = 0
                GROUP BY s.id
                HAVING total > (
                    SELECT SUM(sm2.marks_obtained)
                    FROM student_marks sm2
                    INNER JOIN exam_schedule es2 ON sm2.exam_schedule_id = es2.id
                    INNER JOIN exams e2 ON es2.exam_id = e2.id
                    WHERE sm2.student_id = ? 
                    AND e2.session_id = ? 
                    AND e2.class_id = ?
                    AND sm2.is_absent = 0
                )
            ) as ranked";
    
    $stmt = executeQuery($sql, 'iiiii', [$sessionId, $classId, $studentId, $sessionId, $classId]);
    $result = fetchOne($stmt);
    
    return $result ? $result['rank'] : null;
}

/**
 * Generate unique certificate number
 */
function generateCertificateNumber($type) {
    $prefix = strtoupper(substr($type, 0, 4));
    $year = date('Y');
    
    // Get the last certificate number for this type and year
    $sql = "SELECT certificate_number FROM certificates 
            WHERE certificate_number LIKE ? 
            ORDER BY id DESC LIMIT 1";
    
    $pattern = "$prefix-$year-%";
    $stmt = executeQuery($sql, 's', [$pattern]);
    $last = fetchOne($stmt);
    
    if ($last) {
        // Extract sequence number and increment
        $parts = explode('-', $last['certificate_number']);
        $sequence = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
    } else {
        $sequence = 1;
    }
    
    return sprintf("%s-%s-%04d", $prefix, $year, $sequence);
}

