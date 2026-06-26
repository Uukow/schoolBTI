<?php
/**
 * Process Class Graduation
 * 
 * Bulk graduation processing with automatic certificate generation
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
    // Parse student IDs
    $studentIds = isset($_POST['student_ids']) ? json_decode($_POST['student_ids'], true) : [];
    
    if (empty($studentIds)) {
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
    
    global $conn;
    
    // Get template
    $templateSql = "SELECT * FROM certificate_templates WHERE id = ?";
    $templateStmt = executeQuery($templateSql, 'i', [$templateId]);
    $template = fetchOne($templateStmt);
    
    if (!$template) {
        jsonResponse(false, 'Certificate template not found');
    }
    
    // Get grading scheme
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
    
    $results = [];
    $graduatedCount = 0;
    $certificatesGenerated = 0;
    
    foreach ($studentIds as $studentId) {
        $studentId = intval($studentId);
        $result = [
            'student_id' => $studentId,
            'student_name' => '',
            'status' => 'failed',
            'message' => '',
            'certificate_id' => null,
            'certificate_number' => null
        ];
        
        try {
            // Get student details
            $studentSql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name,
                          CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as full_name
                          FROM students s
                          LEFT JOIN classes c ON s.current_class_id = c.id
                          LEFT JOIN sections sec ON s.current_section_id = sec.id
                          LEFT JOIN branches b ON s.branch_id = b.id
                          WHERE s.id = ?";
            $studentStmt = executeQuery($studentSql, 'i', [$studentId]);
            $studentData = fetchOne($studentStmt);
            
            if (!$studentData) {
                $result['message'] = 'Student not found';
                $results[] = $result;
                continue;
            }
            
            $result['student_name'] = $studentData['full_name'];
            
            // Update student status to Graduated
            $updateStatusSql = "UPDATE students SET status = 'Graduated', updated_at = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateStatusSql);
            $updateStmt->bind_param('i', $studentId);
            
            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update student status: ' . $updateStmt->error);
            }
            
            // Check if certificate already exists
            $existingCertSql = "SELECT id FROM certificates 
                               WHERE student_id = ? AND certificate_type = 'graduation' AND status = 'issued'";
            $existingCertStmt = executeQuery($existingCertSql, 'i', [$studentId]);
            $existingCert = fetchOne($existingCertStmt);
            
            if ($existingCert) {
                $result['status'] = 'skipped';
                $result['message'] = 'Certificate already exists';
                $result['certificate_id'] = $existingCert['id'];
                $results[] = $result;
                $graduatedCount++;
                continue;
            }
            
            // Calculate academic data
            $academicData = calculateAcademicDataForGraduation($studentId, $sessionId, $classId, $gradeItems, $scheme);
            
            // Calculate attendance
            $attendancePercentage = calculateAttendancePercentageForGraduation($studentId, $sessionId, $classId);
            
            // Generate unique certificate number
            $certificateNumber = generateCertificateNumber('graduation');
            
            // Generate verification code
            $verificationCode = generateToken(16);
            
            // Insert certificate record
            $insertSql = "INSERT INTO certificates (
                certificate_number, verification_code, template_id, student_id, 
                session_id, class_id, certificate_type, issue_date, valid_until,
                academic_data, gpa, cgpa, attendance_percentage,
                remarks, status, issued_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'graduation', ?, ?, ?, ?, ?, ?, ?, 'issued', ?, NOW())";
            
            $academicDataJson = json_encode($academicData);
            $gpa = $academicData['gpa'] ?? null;
            $cgpa = $academicData['cgpa'] ?? null;
            $issuedBy = $_SESSION['user_id'];
            
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param('ssiiiiisssdddsi', 
                $certificateNumber, $verificationCode, $templateId, $studentId, 
                $sessionId, $classId, $issueDate, $validUntil,
                $academicDataJson, $gpa, $cgpa, $attendancePercentage,
                $remarks, $issuedBy
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create certificate: ' . $stmt->error);
            }
            
            $certificateId = $conn->insert_id;
            
            // Log activity
            logActivity($_SESSION['user_id'], 'Graduate Student', 'Certificates',
                       "Graduated student {$studentData['student_id']} and generated certificate {$certificateNumber}");
            
            $result['status'] = 'graduated';
            $result['message'] = 'Successfully graduated';
            $result['certificate_id'] = $certificateId;
            $result['certificate_number'] = $certificateNumber;
            
            $graduatedCount++;
            $certificatesGenerated++;
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        $results[] = $result;
    }
    
    $conn->commit();
    
    jsonResponse(true, 'Graduation processed successfully', [
        'graduated_count' => $graduatedCount,
        'certificates_generated' => $certificatesGenerated,
        'results' => $results
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    error_log('Graduation processing error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to process graduation: ' . $e->getMessage());
}

/**
 * Calculate academic data for graduation
 */
function calculateAcademicDataForGraduation($studentId, $sessionId, $classId, $gradeItems, $scheme) {
    $sql = "SELECT 
            es.subject_id, s.subject_name, s.subject_code,
            es.total_marks,
            sm.marks_obtained, sm.is_absent,
            e.exam_name
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN subjects s ON es.subject_id = s.id
            WHERE sm.student_id = ? 
            AND e.session_id = ? 
            AND e.class_id = ?
            AND sm.is_absent = 0
            ORDER BY s.subject_name, e.start_date";
    
    $stmt = executeQuery($sql, 'iii', [$studentId, $sessionId, $classId]);
    $marks = fetchAll($stmt);
    
    $subjects = [];
    $subjectMarks = [];
    
    // Group marks by subject
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
    
    $totalGradePoints = 0;
    $totalSubjects = 0;
    
    // Calculate average per subject
    foreach ($subjectMarks as $subjectId => $data) {
        $subjectTotal = 0;
        $subjectObtained = 0;
        
        foreach ($data['marks'] as $examMark) {
            $subjectTotal += $examMark['total_marks'];
            $subjectObtained += $examMark['marks_obtained'];
        }
        
        $percentage = $subjectTotal > 0 ? ($subjectObtained / $subjectTotal) * 100 : 0;
        
        // Assign grade
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
    }
    
    $gpa = $totalSubjects > 0 ? round($totalGradePoints / $totalSubjects, 2) : 0;
    
    return [
        'subjects' => $subjects,
        'gpa' => $gpa,
        'cgpa' => $gpa, // For single session
        'total_subjects' => $totalSubjects
    ];
}

/**
 * Calculate attendance percentage for graduation
 */
function calculateAttendancePercentageForGraduation($studentId, $sessionId, $classId) {
    $sessionSql = "SELECT start_date, end_date FROM academic_sessions WHERE id = ?";
    $sessionStmt = executeQuery($sessionSql, 'i', [$sessionId]);
    $session = fetchOne($sessionStmt);
    
    if (!$session) {
        return null;
    }
    
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
        return null;
    }
    
    return round(($attendance['present'] / $attendance['total']) * 100, 2);
}

/**
 * Generate unique certificate number
 */
function generateCertificateNumber($type) {
    $prefix = strtoupper(substr($type, 0, 4));
    $year = date('Y');
    
    global $conn;
    $sql = "SELECT certificate_number FROM certificates 
            WHERE certificate_number LIKE ? 
            ORDER BY id DESC LIMIT 1";
    
    $pattern = "$prefix-$year-%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $last = $result->fetch_assoc();
    
    if ($last) {
        $parts = explode('-', $last['certificate_number']);
        $sequence = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
    } else {
        $sequence = 1;
    }
    
    return sprintf("%s-%s-%04d", $prefix, $year, $sequence);
}


