<?php
/**
 * Report Cards Page
 * 
 * Generate and view student report cards
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Report Cards';

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

$teacher = null;
$teacherId = null;
$currentSession = getCurrentSession();

if ($isTeacher && !$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'modules/teacher/dashboard.php');
    }
    $teacherId = $teacher['id'];
}

// Get parameters
$studentId = $_GET['student_id'] ?? '';
$examId = $_GET['exam_id'] ?? '';

// Get exams - filtered for teachers
if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
    // Teachers see only exams for classes assigned to them
    $examsSql = "SELECT DISTINCT e.*, c.class_name 
                 FROM exams e 
                 INNER JOIN classes c ON e.class_id = c.id
                 INNER JOIN class_subjects cs ON c.id = cs.class_id
                 WHERE cs.teacher_id = ? AND e.session_id = ?
                 ORDER BY e.start_date DESC";
    $exams = fetchAll(executeQuery($examsSql, 'ii', [$teacherId, $currentSession['id']]));
} else {
    // Super Admin and Admin see all exams
    $examsSql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id ORDER BY e.start_date DESC";
    $exams = fetchAll(executeQuery($examsSql));
}

// Get students - filtered for teachers
if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
    // Teachers see only students from classes assigned to them
    $studentsSql = "SELECT DISTINCT s.*, c.class_name 
                    FROM students s 
                    INNER JOIN classes c ON s.current_class_id = c.id
                    INNER JOIN class_subjects cs ON c.id = cs.class_id
                    WHERE s.status = 'Active' AND cs.teacher_id = ? AND cs.session_id = ?
                    ORDER BY s.first_name";
    $students = fetchAll(executeQuery($studentsSql, 'ii', [$teacherId, $currentSession['id']]));
} else {
    // Super Admin and Admin see all students
    $studentsSql = "SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.current_class_id = c.id WHERE s.status = 'Active' ORDER BY s.first_name";
    $students = fetchAll(executeQuery($studentsSql));
}

// Get report card data if both selected
$reportCard = null;
$subjectMarks = [];
$examInfo = null;
$studentInfo = null;

if ($studentId && $examId) {
    // Get student info - verify teacher assignment for teachers
    if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
        // Verify student is from teacher's assigned classes
        $sql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name 
                FROM students s 
                INNER JOIN classes c ON s.current_class_id = c.id
                INNER JOIN class_subjects cs ON c.id = cs.class_id
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE s.id = ? AND cs.teacher_id = ? AND cs.session_id = ?";
        $stmt = executeQuery($sql, 'iii', [$studentId, $teacherId, $currentSession['id']]);
        $studentInfo = fetchOne($stmt);
        
        if (!$studentInfo) {
            $_SESSION['error'] = 'You do not have permission to view this student\'s report card. This student is not in your assigned classes.';
            redirect(APP_URL . 'modules/exams/report-cards.php');
        }
    } else {
        // Super Admin and Admin can access any student
        $sql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name 
                FROM students s 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE s.id = ?";
        $stmt = executeQuery($sql, 'i', [$studentId]);
        $studentInfo = fetchOne($stmt);
    }
    
    // Get exam info - verify teacher assignment for teachers
    if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
        // Verify this exam belongs to teacher's assigned classes
        $sql = "SELECT e.*, c.class_name 
                FROM exams e 
                INNER JOIN classes c ON e.class_id = c.id
                INNER JOIN class_subjects cs ON c.id = cs.class_id
                WHERE e.id = ? AND cs.teacher_id = ? AND e.session_id = ?";
        $stmt = executeQuery($sql, 'iii', [$examId, $teacherId, $currentSession['id']]);
        $examInfo = fetchOne($stmt);
        
        if (!$examInfo) {
            $_SESSION['error'] = 'You do not have permission to view this exam\'s report card. This exam is not assigned to you.';
            redirect(APP_URL . 'modules/exams/report-cards.php');
        }
    } else {
        // Super Admin and Admin can access any exam
        $sql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id WHERE e.id = ?";
        $stmt = executeQuery($sql, 'i', [$examId]);
        $examInfo = fetchOne($stmt);
    }
    
    if ($studentInfo && $examInfo) {
        // Get subject-wise marks - filtered for teachers
        if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
            // Only show subjects assigned to the teacher
            $sql = "SELECT es.*, s.subject_name, s.subject_code, sm.marks_obtained, sm.is_absent, sm.remarks
                    FROM exam_schedule es
                    INNER JOIN subjects s ON es.subject_id = s.id
                    INNER JOIN class_subjects cs ON s.id = cs.subject_id AND cs.class_id = ?
                    LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id AND sm.student_id = ?
                    WHERE es.exam_id = ? AND cs.teacher_id = ? AND cs.session_id = ?
                    ORDER BY es.exam_date";
            $stmt = executeQuery($sql, 'iiiii', [$examInfo['class_id'], $studentId, $examId, $teacherId, $currentSession['id']]);
            $subjectMarks = fetchAll($stmt);
        } else {
            // Super Admin and Admin see all subjects
            $sql = "SELECT es.*, s.subject_name, s.subject_code, sm.marks_obtained, sm.is_absent, sm.remarks
                    FROM exam_schedule es
                    LEFT JOIN subjects s ON es.subject_id = s.id
                    LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id AND sm.student_id = ?
                    WHERE es.exam_id = ?
                    ORDER BY es.exam_date";
            $stmt = executeQuery($sql, 'ii', [$studentId, $examId]);
            $subjectMarks = fetchAll($stmt);
        }
        
        // Calculate totals
        $totalMarks = 0;
        $obtainedMarks = 0;
        $absentCount = 0;
        
        foreach ($subjectMarks as $subject) {
            $totalMarks += $subject['total_marks'];
            if (!$subject['is_absent'] && $subject['marks_obtained'] !== null) {
                $obtainedMarks += $subject['marks_obtained'];
            } else {
                $absentCount++;
            }
        }
        
        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
        $grade = getGrade($percentage);
        $gpa = calculateGPA($percentage);
        
        // Get rank
        $rankSql = "SELECT COUNT(*) + 1 as rank
                    FROM (
                        SELECT s.id, SUM(sm.marks_obtained) as total
                        FROM students s
                        INNER JOIN student_marks sm ON s.id = sm.student_id
                        INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                        WHERE es.exam_id = ? AND s.current_class_id = ? AND s.status = 'Active'
                        GROUP BY s.id
                        HAVING total > ?
                    ) as ranked";
        $rankStmt = executeQuery($rankSql, 'iid', [$examId, $examInfo['class_id'], $obtainedMarks]);
        $rankResult = fetchOne($rankStmt);
        $rank = $rankResult['rank'] ?? 0;
        
        // Get attendance percentage
        $attendanceSql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days
            FROM student_attendance 
            WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
        $attendanceStmt = executeQuery($attendanceSql, 'iss', [
            $studentId, 
            $examInfo['start_date'], 
            $examInfo['end_date']
        ]);
        $attendance = fetchOne($attendanceStmt);
        $attendancePercentage = ($attendance['total_days'] > 0) ? 
            round(($attendance['present_days'] / $attendance['total_days']) * 100, 2) : 0;
        
        $reportCard = [
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => $percentage,
            'grade' => $grade,
            'gpa' => $gpa,
            'rank' => $rank,
            'attendance_percentage' => $attendancePercentage,
            'absent_subjects' => $absentCount
        ];
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right no-print">
                            <?php if ($reportCard): ?>
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="ri-printer-line"></i> Print Report Card
                            </button>
                            <button onclick="generatePDF()" class="btn btn-danger ms-2">
                                <i class="ri-file-pdf-line"></i> Download PDF
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Report Cards</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="ri-error-warning-line me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isTeacher && !$isSuperAdmin): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> 
                        <strong>Note:</strong> You are viewing report cards for students and exams assigned to you only. 
                        If you don't see a student or exam, they may not be assigned to your classes/subjects.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Selection Form -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label required">Select Student</label>
                                    <select class="form-select" name="student_id" required>
                                        <option value="">Choose Student</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>" <?php echo ($studentId == $student['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['class_name'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label required">Select Exam</label>
                                    <select class="form-select" name="exam_id" required>
                                        <option value="">Choose Exam</option>
                                        <?php foreach ($exams as $exam): ?>
                                            <option value="<?php echo $exam['id']; ?>" <?php echo ($examId == $exam['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($exam['exam_name'] . ' - ' . $exam['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Generate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Card Display -->
            <?php if ($reportCard && $studentInfo && $examInfo): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card" id="reportCard">
                        <div class="card-body">
                            <!-- Header -->
                            <div class="text-center mb-4">
                                <h3 class="mb-1"><?php echo APP_NAME; ?></h3>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($studentInfo['branch_name']); ?></p>
                                <h4 class="mt-3 mb-1">REPORT CARD</h4>
                                <p class="text-muted"><?php echo htmlspecialchars($examInfo['exam_name']); ?></p>
                            </div>

                            <!-- Student Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th width="40%">Student ID:</th>
                                            <td><?php echo htmlspecialchars($studentInfo['student_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td><strong><?php echo htmlspecialchars($studentInfo['first_name'] . ' ' . $studentInfo['last_name']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Class:</th>
                                            <td><?php echo htmlspecialchars($studentInfo['class_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Section:</th>
                                            <td><?php echo htmlspecialchars($studentInfo['section_name'] ?? 'N/A'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th width="40%">Exam Period:</th>
                                            <td><?php echo formatDate($examInfo['start_date']); ?> to <?php echo formatDate($examInfo['end_date']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Academic Year:</th>
                                            <td><?php echo htmlspecialchars(getCurrentSession()['session_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date Generated:</th>
                                            <td><?php echo date('d M Y'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Subject Marks -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Code</th>
                                            <th>Total Marks</th>
                                            <th>Marks Obtained</th>
                                            <th>Percentage</th>
                                            <th>Grade</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjectMarks as $subject): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                            <td><?php echo number_format($subject['total_marks'], 0); ?></td>
                                            <td>
                                                <?php if ($subject['is_absent']): ?>
                                                    <span class="badge bg-danger">Absent</span>
                                                <?php else: ?>
                                                    <?php echo number_format($subject['marks_obtained'] ?? 0, 2); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!$subject['is_absent'] && $subject['marks_obtained'] !== null) {
                                                    $subPercentage = ($subject['marks_obtained'] / $subject['total_marks']) * 100;
                                                    echo number_format($subPercentage, 2) . '%';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!$subject['is_absent'] && $subject['marks_obtained'] !== null) {
                                                    $subPercentage = ($subject['marks_obtained'] / $subject['total_marks']) * 100;
                                                    echo getGrade($subPercentage);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($subject['remarks'] ?? '-'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="50%">Total Marks:</th>
                                            <td><strong><?php echo number_format($reportCard['total_marks'], 2); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Marks Obtained:</th>
                                            <td><strong><?php echo number_format($reportCard['obtained_marks'], 2); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Percentage:</th>
                                            <td>
                                                <strong>
                                                    <?php 
                                                    $badgeClass = $reportCard['percentage'] >= 90 ? 'success' : 
                                                                 ($reportCard['percentage'] >= 75 ? 'primary' : 
                                                                 ($reportCard['percentage'] >= 60 ? 'info' : 
                                                                 ($reportCard['percentage'] >= 50 ? 'warning' : 'danger')));
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>">
                                                        <?php echo number_format($reportCard['percentage'], 2); ?>%
                                                    </span>
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Grade:</th>
                                            <td><strong><?php echo $reportCard['grade']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>GPA:</th>
                                            <td><strong><?php echo number_format($reportCard['gpa'], 2); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="50%">Rank:</th>
                                            <td><strong>#<?php echo $reportCard['rank']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Attendance:</th>
                                            <td>
                                                <strong>
                                                    <span class="badge bg-<?php echo $reportCard['attendance_percentage'] >= 75 ? 'success' : 'warning'; ?>">
                                                        <?php echo number_format($reportCard['attendance_percentage'], 2); ?>%
                                                    </span>
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Absent Subjects:</th>
                                            <td>
                                                <?php if ($reportCard['absent_subjects'] > 0): ?>
                                                    <span class="badge bg-danger"><?php echo $reportCard['absent_subjects']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">None</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="row mt-4">
                                <div class="col-md-6 text-center">
                                    <p class="mb-0">_______________________</p>
                                    <p class="mb-0"><strong>Class Teacher</strong></p>
                                </div>
                                <div class="col-md-6 text-center">
                                    <p class="mb-0">_______________________</p>
                                    <p class="mb-0"><strong>Principal</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    #reportCard {
        border: none;
        box-shadow: none;
    }
}
</style>

<script>
function generatePDF() {
    Swal.fire({
        title: 'Generating PDF...',
        text: 'Please wait while we generate the report card PDF.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // In a real implementation, you would call a PDF generation endpoint
    setTimeout(() => {
        Swal.fire({
            icon: 'info',
            title: 'PDF Generation',
            text: 'PDF generation feature will be implemented with a PDF library like TCPDF or DomPDF.',
            confirmButtonText: 'OK'
        });
    }, 1500);
}
</script>

