<?php
/**
 * Generate Academic Transcript
 * 
 * Generate comprehensive academic transcript with session-wise records
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
    $studentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $gradingSchemeId = isset($_POST['grading_scheme_id']) ? intval($_POST['grading_scheme_id']) : 0;
    
    if (!$studentId || !$gradingSchemeId) {
        jsonResponse(false, 'Student ID and grading scheme are required');
    }
    
    $includeAllSessions = isset($_POST['include_all_sessions']) ? 1 : 0;
    $includeSemesterGpa = isset($_POST['include_semester_gpa']) ? 1 : 0;
    $includeCredits = isset($_POST['include_credits']) ? 1 : 0;
    $includeAttendance = isset($_POST['include_attendance']) ? 1 : 0;
    
    global $conn;
    
    // Get student details
    $studentSql = "SELECT s.*, c.class_name, b.branch_name,
                   CONCAT(s.first_name, ' ', s.last_name) as full_name
                   FROM students s
                   LEFT JOIN classes c ON s.current_class_id = c.id
                   LEFT JOIN branches b ON s.branch_id = b.id
                   WHERE s.id = ?";
    $studentStmt = executeQuery($studentSql, 'i', [$studentId]);
    $student = fetchOne($studentStmt);
    
    if (!$student) {
        jsonResponse(false, 'Student not found');
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
    
    // Get all academic sessions for this student
    $sessionsSql = "SELECT DISTINCT acs.* 
                    FROM academic_sessions acs
                    INNER JOIN exams e ON e.session_id = acs.id
                    INNER JOIN exam_schedule es ON es.exam_id = e.id
                    INNER JOIN student_marks sm ON sm.exam_schedule_id = es.id
                    WHERE sm.student_id = ?
                    ORDER BY acs.start_date";
    $sessionsStmt = executeQuery($sessionsSql, 'i', [$studentId]);
    $sessions = fetchAll($sessionsStmt);
    
    if (empty($sessions)) {
        jsonResponse(false, 'No academic records found for this student. The student must have exam marks to generate a transcript.');
    }
    
    $sessionData = [];
    $totalCredits = 0;
    $totalGradePoints = 0;
    $totalSessions = 0;
    
    foreach ($sessions as $session) {
        // Get subjects and marks for this session
        $subjectsSql = "SELECT DISTINCT
                       s.id as subject_id, s.subject_name, s.subject_code,
                       AVG(sm.marks_obtained / es.total_marks * 100) as avg_percentage
                       FROM subjects s
                       INNER JOIN exam_schedule es ON es.subject_id = s.id
                       INNER JOIN student_marks sm ON sm.exam_schedule_id = es.id
                       INNER JOIN exams e ON es.exam_id = e.id
                       WHERE sm.student_id = ? 
                       AND e.session_id = ?
                       AND sm.is_absent = 0
                       GROUP BY s.id, s.subject_name, s.subject_code
                       ORDER BY s.subject_name";
        
        $subjectsStmt = executeQuery($subjectsSql, 'ii', [$studentId, $session['id']]);
        $subjects = fetchAll($subjectsStmt);
        
        $sessionGradePoints = 0;
        $sessionSubjects = 0;
        $sessionCredits = 0;
        
        $processedSubjects = [];
        foreach ($subjects as $subject) {
            $percentage = $subject['avg_percentage'];
            
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
            
            $credits = 3; // Default credit hours per subject (can be customized)
            
            $processedSubjects[] = [
                'subject_code' => $subject['subject_code'],
                'subject_name' => $subject['subject_name'],
                'marks' => round($percentage, 2),
                'grade' => $grade,
                'grade_points' => $gradePoint,
                'credits' => $includeCredits ? $credits : null
            ];
            
            $sessionGradePoints += $gradePoint;
            $sessionSubjects++;
            $sessionCredits += $credits;
        }
        
        $sessionGpa = $sessionSubjects > 0 ? round($sessionGradePoints / $sessionSubjects, 2) : 0;
        
        $sessionData[] = [
            'session_id' => $session['id'],
            'session_name' => $session['session_name'],
            'subjects' => $processedSubjects,
            'gpa' => $includeSemesterGpa ? $sessionGpa : null,
            'total_credits' => $includeCredits ? $sessionCredits : null
        ];
        
        $totalGradePoints += $sessionGradePoints;
        $totalSessions += $sessionSubjects;
        $totalCredits += $sessionCredits;
    }
    
    // Calculate CGPA
    $cgpa = $totalSessions > 0 ? round($totalGradePoints / $totalSessions, 2) : 0;
    
    // Generate transcript number
    $transcriptNumber = generateTranscriptNumber();
    
    // Save transcript record
    $conn->begin_transaction();
    
    $insertSql = "INSERT INTO transcripts (
        transcript_number, student_id, grading_scheme_id,
        academic_data, total_credits, cgpa, overall_percentage,
        generated_by, generated_at, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'issued')";
    
    $academicDataJson = json_encode($sessionData);
    $overallPercentage = ($cgpa / floatval($scheme['max_gpa'])) * 100;
    $generatedBy = $_SESSION['user_id'];
    
    $stmt = $conn->prepare($insertSql);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
    }
    
    // Correct type string: s=string, i=integer, d=double
    // Parameters: transcript_number(s), student_id(i), grading_scheme_id(i), academic_data(s), 
    //             total_credits(d), cgpa(d), overall_percentage(d), generated_by(i)
    $stmt->bind_param('siisdddi', 
        $transcriptNumber, $studentId, $gradingSchemeId,
        $academicDataJson, $totalCredits, $cgpa, $overallPercentage,
        $generatedBy
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save transcript: ' . $stmt->error);
    }
    
    $transcriptId = $conn->insert_id;
    
    // Log activity
    logActivity($_SESSION['user_id'], 'Generate Transcript', 'Certificates',
               "Generated transcript $transcriptNumber for student {$student['student_id']}");
    
    $conn->commit();
    
    jsonResponse(true, 'Transcript generated successfully', [
        'transcript' => [
            'id' => $transcriptId,
            'transcript_number' => $transcriptNumber,
            'total_credits' => $includeCredits ? $totalCredits : null,
            'cgpa' => $cgpa,
            'overall_percentage' => round($overallPercentage, 2)
        ],
        'student' => $student,
        'sessions' => $sessionData
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    error_log('Transcript generation error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to generate transcript: ' . $e->getMessage());
}

/**
 * Generate unique transcript number
 */
function generateTranscriptNumber() {
    $year = date('Y');
    
    $sql = "SELECT transcript_number FROM transcripts 
            WHERE transcript_number LIKE ? 
            ORDER BY id DESC LIMIT 1";
    
    $pattern = "TR-$year-%";
    $stmt = executeQuery($sql, 's', [$pattern]);
    $last = fetchOne($stmt);
    
    if ($last) {
        $parts = explode('-', $last['transcript_number']);
        $sequence = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
    } else {
        $sequence = 1;
    }
    
    return sprintf("TR-%s-%05d", $year, $sequence);
}

