<?php
/**
 * Exam Analytics Page
 * 
 * View exam performance analytics and statistics
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Exam Analytics';

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
$examId = $_GET['exam_id'] ?? '';
$classId = $_GET['class_id'] ?? '';

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

// Get classes - filtered for teachers
if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
    // Teachers see only classes assigned to them
    $classesSql = "SELECT DISTINCT c.* 
                   FROM classes c
                   INNER JOIN class_subjects cs ON c.id = cs.class_id
                   WHERE cs.teacher_id = ? AND cs.session_id = ? AND c.is_active = 1
                   ORDER BY c.class_order";
    $classes = fetchAll(executeQuery($classesSql, 'ii', [$teacherId, $currentSession['id']]));
} else {
    // Super Admin and Admin see all classes (excluding graduated)
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql));
}

// Get analytics data if exam selected
$analytics = null;
$subjectStats = [];
$performanceData = [];

if ($examId) {
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
            $_SESSION['error'] = 'You do not have permission to view analytics for this exam. This exam is not assigned to you.';
            redirect(APP_URL . 'modules/exams/analytics.php');
        }
    } else {
        // Super Admin and Admin can access any exam
        $sql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id WHERE e.id = ?";
        $stmt = executeQuery($sql, 'i', [$examId]);
        $examInfo = fetchOne($stmt);
    }
    
    if ($examInfo) {
        // Overall statistics - filtered for teachers
        if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
            // Only count students and marks from teacher's assigned subjects
            $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(DISTINCT CASE WHEN sm.marks_obtained IS NOT NULL THEN s.id END) as students_with_marks,
                    AVG(sm.marks_obtained) as average_marks,
                    MAX(sm.marks_obtained) as highest_marks,
                    MIN(sm.marks_obtained) as lowest_marks,
                    SUM(CASE WHEN sm.is_absent = 1 THEN 1 ELSE 0 END) as total_absents
                    FROM students s
                    INNER JOIN classes c ON s.current_class_id = c.id
                    INNER JOIN class_subjects cs ON c.id = cs.class_id
                    LEFT JOIN student_marks sm ON s.id = sm.student_id
                    LEFT JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                    LEFT JOIN subjects sub ON es.subject_id = sub.id
                    LEFT JOIN class_subjects cs2 ON sub.id = cs2.subject_id AND c.id = cs2.class_id
                    WHERE s.current_class_id = ? AND s.status = 'Active' AND es.exam_id = ? 
                    AND cs.teacher_id = ? AND cs.session_id = ? AND cs2.teacher_id = ?";
            $stmt = executeQuery($sql, 'iiiii', [$examInfo['class_id'], $examId, $teacherId, $currentSession['id'], $teacherId]);
            $analytics = fetchOne($stmt);
            
            // Subject-wise statistics - only teacher's subjects
            $sql = "SELECT s.subject_name, s.subject_code,
                    COUNT(DISTINCT sm.student_id) as students_count,
                    AVG(sm.marks_obtained) as avg_marks,
                    MAX(sm.marks_obtained) as max_marks,
                    MIN(sm.marks_obtained) as min_marks,
                    SUM(CASE WHEN sm.is_absent = 1 THEN 1 ELSE 0 END) as absents,
                    es.total_marks
                    FROM exam_schedule es
                    INNER JOIN subjects s ON es.subject_id = s.id
                    INNER JOIN class_subjects cs ON s.id = cs.subject_id AND cs.class_id = ?
                    LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
                    WHERE es.exam_id = ? AND cs.teacher_id = ? AND cs.session_id = ?
                    GROUP BY es.id
                    ORDER BY s.subject_name";
            $stmt = executeQuery($sql, 'iiii', [$examInfo['class_id'], $examId, $teacherId, $currentSession['id']]);
            $subjectStats = fetchAll($stmt);
            
            // Grade distribution - only teacher's subjects
            $sql = "SELECT 
                    CASE 
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 90 THEN 'A+'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 80 THEN 'A'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 70 THEN 'B+'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 60 THEN 'B'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 50 THEN 'C'
                        ELSE 'F'
                    END as grade,
                    COUNT(*) as count
                    FROM student_marks sm
                    INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                    INNER JOIN subjects sub ON es.subject_id = sub.id
                    INNER JOIN class_subjects cs ON sub.id = cs.subject_id AND cs.class_id = ?
                    WHERE es.exam_id = ? AND sm.is_absent = 0 AND sm.marks_obtained IS NOT NULL 
                    AND cs.teacher_id = ? AND cs.session_id = ?
                    GROUP BY grade
                    ORDER BY 
                        CASE grade
                            WHEN 'A+' THEN 1
                            WHEN 'A' THEN 2
                            WHEN 'B+' THEN 3
                            WHEN 'B' THEN 4
                            WHEN 'C' THEN 5
                            WHEN 'F' THEN 6
                        END";
            $stmt = executeQuery($sql, 'iiii', [$examInfo['class_id'], $examId, $teacherId, $currentSession['id']]);
            $gradeDistribution = fetchAll($stmt);
            
            // Performance ranges - only teacher's subjects
            $sql = "SELECT 
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 90 THEN 1 END) as excellent,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 75 AND (sm.marks_obtained / es.total_marks * 100) < 90 THEN 1 END) as very_good,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 60 AND (sm.marks_obtained / es.total_marks * 100) < 75 THEN 1 END) as good,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 50 AND (sm.marks_obtained / es.total_marks * 100) < 60 THEN 1 END) as average,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) < 50 THEN 1 END) as below_average
                    FROM student_marks sm
                    INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                    INNER JOIN subjects sub ON es.subject_id = sub.id
                    INNER JOIN class_subjects cs ON sub.id = cs.subject_id AND cs.class_id = ?
                    WHERE es.exam_id = ? AND sm.is_absent = 0 AND sm.marks_obtained IS NOT NULL 
                    AND cs.teacher_id = ? AND cs.session_id = ?";
            $stmt = executeQuery($sql, 'iiii', [$examInfo['class_id'], $examId, $teacherId, $currentSession['id']]);
            $performanceData = fetchOne($stmt);
        } else {
            // Super Admin and Admin see all analytics
            $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(DISTINCT CASE WHEN sm.marks_obtained IS NOT NULL THEN s.id END) as students_with_marks,
                    AVG(sm.marks_obtained) as average_marks,
                    MAX(sm.marks_obtained) as highest_marks,
                    MIN(sm.marks_obtained) as lowest_marks,
                    SUM(CASE WHEN sm.is_absent = 1 THEN 1 ELSE 0 END) as total_absents
                    FROM students s
                    LEFT JOIN student_marks sm ON s.id = sm.student_id
                    LEFT JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                    WHERE s.current_class_id = ? AND s.status = 'Active' AND es.exam_id = ?";
            $stmt = executeQuery($sql, 'ii', [$examInfo['class_id'], $examId]);
            $analytics = fetchOne($stmt);
            
            // Subject-wise statistics
            $sql = "SELECT s.subject_name, s.subject_code,
                    COUNT(DISTINCT sm.student_id) as students_count,
                    AVG(sm.marks_obtained) as avg_marks,
                    MAX(sm.marks_obtained) as max_marks,
                    MIN(sm.marks_obtained) as min_marks,
                    SUM(CASE WHEN sm.is_absent = 1 THEN 1 ELSE 0 END) as absents,
                    es.total_marks
                    FROM exam_schedule es
                    LEFT JOIN subjects s ON es.subject_id = s.id
                    LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
                    WHERE es.exam_id = ?
                    GROUP BY es.id
                    ORDER BY s.subject_name";
            $stmt = executeQuery($sql, 'i', [$examId]);
            $subjectStats = fetchAll($stmt);
            
            // Grade distribution
            $sql = "SELECT 
                    CASE 
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 90 THEN 'A+'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 80 THEN 'A'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 70 THEN 'B+'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 60 THEN 'B'
                        WHEN (sm.marks_obtained / es.total_marks * 100) >= 50 THEN 'C'
                        ELSE 'F'
                    END as grade,
                    COUNT(*) as count
                    FROM student_marks sm
                    INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                    WHERE es.exam_id = ? AND sm.is_absent = 0 AND sm.marks_obtained IS NOT NULL
                    GROUP BY grade
                    ORDER BY 
                        CASE grade
                            WHEN 'A+' THEN 1
                            WHEN 'A' THEN 2
                            WHEN 'B+' THEN 3
                            WHEN 'B' THEN 4
                            WHEN 'C' THEN 5
                            WHEN 'F' THEN 6
                        END";
            $stmt = executeQuery($sql, 'i', [$examId]);
            $gradeDistribution = fetchAll($stmt);
            
            // Performance ranges
            $sql = "SELECT 
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 90 THEN 1 END) as excellent,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 75 AND (sm.marks_obtained / es.total_marks * 100) < 90 THEN 1 END) as very_good,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 60 AND (sm.marks_obtained / es.total_marks * 100) < 75 THEN 1 END) as good,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) >= 50 AND (sm.marks_obtained / es.total_marks * 100) < 60 THEN 1 END) as average,
                    COUNT(CASE WHEN (sm.marks_obtained / es.total_marks * 100) < 50 THEN 1 END) as below_average
                    FROM student_marks sm
                    INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                    WHERE es.exam_id = ? AND sm.is_absent = 0 AND sm.marks_obtained IS NOT NULL";
            $stmt = executeQuery($sql, 'i', [$examId]);
            $performanceData = fetchOne($stmt);
        }
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
                        <div class="page-title-right">
                            <button onclick="window.print()" class="btn btn-secondary no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                        </div>
                        <h4 class="page-title">Exam Analytics</h4>
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
                        <strong>Note:</strong> You are viewing analytics for exams assigned to you only. 
                        Statistics are calculated based on your assigned subjects and classes.
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
                                <div class="col-md-6">
                                    <label class="form-label required">Select Exam</label>
                                    <select class="form-select" name="exam_id" onchange="this.form.submit()" required>
                                        <option value="">Choose Exam</option>
                                        <?php foreach ($exams as $exam): ?>
                                            <option value="<?php echo $exam['id']; ?>" <?php echo ($examId == $exam['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($exam['exam_name'] . ' - ' . $exam['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($analytics && $examInfo): ?>
            <!-- Overall Statistics -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-user-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Students</h5>
                                    <h2 class="mb-0"><?php echo $analytics['total_students'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Average Marks</h5>
                                    <h2 class="mb-0"><?php echo number_format($analytics['average_marks'] ?? 0, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-arrow-up-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Highest Marks</h5>
                                    <h2 class="mb-0"><?php echo number_format($analytics['highest_marks'] ?? 0, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-close-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Absents</h5>
                                    <h2 class="mb-0"><?php echo $analytics['total_absents'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Distribution -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Performance Distribution</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Performance Range</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-success">Excellent (90%+)</span></td>
                                            <td><?php echo $performanceData['excellent'] ?? 0; ?></td>
                                            <td>
                                                <?php 
                                                $total = array_sum($performanceData ?? []);
                                                echo $total > 0 ? number_format(($performanceData['excellent'] / $total) * 100, 2) : 0; 
                                                ?>%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-primary">Very Good (75-89%)</span></td>
                                            <td><?php echo $performanceData['very_good'] ?? 0; ?></td>
                                            <td>
                                                <?php 
                                                echo $total > 0 ? number_format(($performanceData['very_good'] / $total) * 100, 2) : 0; 
                                                ?>%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info">Good (60-74%)</span></td>
                                            <td><?php echo $performanceData['good'] ?? 0; ?></td>
                                            <td>
                                                <?php 
                                                echo $total > 0 ? number_format(($performanceData['good'] / $total) * 100, 2) : 0; 
                                                ?>%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning">Average (50-59%)</span></td>
                                            <td><?php echo $performanceData['average'] ?? 0; ?></td>
                                            <td>
                                                <?php 
                                                echo $total > 0 ? number_format(($performanceData['average'] / $total) * 100, 2) : 0; 
                                                ?>%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-danger">Below Average (<50%)</span></td>
                                            <td><?php echo $performanceData['below_average'] ?? 0; ?></td>
                                            <td>
                                                <?php 
                                                echo $total > 0 ? number_format(($performanceData['below_average'] / $total) * 100, 2) : 0; 
                                                ?>%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Grade Distribution</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Grade</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($gradeDistribution as $grade): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($grade['grade']); ?></strong></td>
                                            <td><?php echo $grade['count']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject-wise Statistics -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Subject-wise Performance</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Code</th>
                                            <th>Students</th>
                                            <th>Avg Marks</th>
                                            <th>Max Marks</th>
                                            <th>Min Marks</th>
                                            <th>Absents</th>
                                            <th>Pass Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjectStats as $subject): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                            <td><?php echo $subject['students_count']; ?></td>
                                            <td><?php echo number_format($subject['avg_marks'] ?? 0, 2); ?></td>
                                            <td><?php echo number_format($subject['max_marks'] ?? 0, 2); ?></td>
                                            <td><?php echo number_format($subject['min_marks'] ?? 0, 2); ?></td>
                                            <td>
                                                <?php if ($subject['absents'] > 0): ?>
                                                    <span class="badge bg-danger"><?php echo $subject['absents']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $passingMarks = $subject['total_marks'] * 0.4; // 40% passing
                                                $passRate = $subject['students_count'] > 0 ? 
                                                    (($subject['students_count'] - $subject['absents']) / $subject['students_count']) * 100 : 0;
                                                $badgeClass = $passRate >= 80 ? 'success' : ($passRate >= 60 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo number_format($passRate, 1); ?>%
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

