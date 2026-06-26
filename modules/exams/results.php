<?php
/**
 * Exam Results Page
 * 
 * View exam results and rankings
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Exam Results';

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

// Get results if exam selected
$results = [];
$examInfo = null;

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
            $_SESSION['error'] = 'You do not have permission to view results for this exam. This exam is not assigned to you.';
            redirect(APP_URL . 'modules/exams/results.php');
        }
    } else {
        // Super Admin and Admin can access any exam
        $sql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id WHERE e.id = ?";
        $stmt = executeQuery($sql, 'i', [$examId]);
        $examInfo = fetchOne($stmt);
    }
    
    if ($examInfo) {
        // Get results with calculated totals - only for students in assigned classes for teachers
        if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
            // Only show students from classes assigned to the teacher
            $sql = "SELECT DISTINCT s.id, s.student_id, s.first_name, s.last_name, c.class_name,
                    (SELECT SUM(es.total_marks) FROM exam_schedule es 
                     INNER JOIN subjects sub ON es.subject_id = sub.id
                     INNER JOIN class_subjects cs2 ON sub.id = cs2.subject_id AND c.id = cs2.class_id
                     WHERE es.exam_id = ? AND cs2.teacher_id = ?) as max_total_marks,
                    (SELECT SUM(sm.marks_obtained) 
                     FROM student_marks sm 
                     INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                     INNER JOIN subjects sub ON es.subject_id = sub.id
                     INNER JOIN class_subjects cs2 ON sub.id = cs2.subject_id AND c.id = cs2.class_id
                     WHERE sm.student_id = s.id AND es.exam_id = ? AND cs2.teacher_id = ?) as obtained_marks,
                    (SELECT COUNT(*) FROM student_marks sm 
                     INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                     INNER JOIN subjects sub ON es.subject_id = sub.id
                     INNER JOIN class_subjects cs2 ON sub.id = cs2.subject_id AND c.id = cs2.class_id
                     WHERE sm.student_id = s.id AND es.exam_id = ? AND cs2.teacher_id = ? AND sm.is_absent = 1) as absent_subjects
                    FROM students s
                    INNER JOIN classes c ON s.current_class_id = c.id
                    INNER JOIN class_subjects cs ON c.id = cs.class_id
                    WHERE s.current_class_id = ? AND s.status = 'Active' AND cs.teacher_id = ? AND cs.session_id = ?
                    HAVING obtained_marks IS NOT NULL
                    ORDER BY obtained_marks DESC";
            
            $stmt = executeQuery($sql, 'iiiiiiiii', [$examId, $teacherId, $examId, $teacherId, $examId, $teacherId, $examInfo['class_id'], $teacherId, $currentSession['id']]);
            $results = fetchAll($stmt);
        } else {
            // Super Admin and Admin see all results
            $sql = "SELECT s.id, s.student_id, s.first_name, s.last_name, c.class_name,
                    (SELECT SUM(es.total_marks) FROM exam_schedule es WHERE es.exam_id = ?) as max_total_marks,
                    (SELECT SUM(sm.marks_obtained) 
                     FROM student_marks sm 
                     INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id 
                     WHERE sm.student_id = s.id AND es.exam_id = ?) as obtained_marks,
                    (SELECT COUNT(*) FROM student_marks sm 
                     INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id 
                     WHERE sm.student_id = s.id AND es.exam_id = ? AND sm.is_absent = 1) as absent_subjects
                    FROM students s
                    LEFT JOIN classes c ON s.current_class_id = c.id
                    WHERE s.current_class_id = ? AND s.status = 'Active'
                    HAVING obtained_marks IS NOT NULL
                    ORDER BY obtained_marks DESC";
            
            $stmt = executeQuery($sql, 'iiii', [$examId, $examId, $examId, $examInfo['class_id']]);
            $results = fetchAll($stmt);
        }
        
        // Calculate percentage, grade, and rank
        $rank = 1;
        foreach ($results as &$result) {
            if ($result['max_total_marks'] > 0) {
                $result['percentage'] = round(($result['obtained_marks'] / $result['max_total_marks']) * 100, 2);
                $result['grade'] = getGrade($result['percentage']);
                $result['gpa'] = calculateGPA($result['percentage']);
            } else {
                $result['percentage'] = 0;
                $result['grade'] = 'F';
                $result['gpa'] = 0;
            }
            $result['rank'] = $rank++;
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
                        <div class="page-title-right no-print">
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportTableToExcel('resultsTable', 'exam_results')" class="btn btn-success ms-2">
                                <i class="ri-file-excel-line"></i> Export
                            </button>
                        </div>
                        <h4 class="page-title">Exam Results</h4>
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
                        <strong>Note:</strong> You are viewing results for exams assigned to you only. 
                        If you don't see an exam, it may not be assigned to your classes/subjects.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Exam Selection -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Select Exam</label>
                                    <select class="form-select" name="exam_id" onchange="this.form.submit()" required>
                                        <option value="">Choose Exam</option>
                                        <?php foreach ($exams as $e): ?>
                                            <option value="<?php echo $e['id']; ?>" <?php echo ($examId == $e['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($e['exam_name']); ?> - <?php echo htmlspecialchars($e['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Display -->
            <?php if (!empty($results) && $examInfo): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h3><?php echo htmlspecialchars($examInfo['exam_name']); ?></h3>
                                <p class="text-muted">
                                    Class: <strong><?php echo htmlspecialchars($examInfo['class_name']); ?></strong> | 
                                    Total Students: <strong><?php echo count($results); ?></strong>
                                </p>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="resultsTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Total Marks</th>
                                            <th>Obtained</th>
                                            <th>Percentage</th>
                                            <th>Grade</th>
                                            <th>GPA</th>
                                            <th>Absent Subjects</th>
                                            <th class="no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td class="text-center">
                                                <strong>
                                                    <?php 
                                                    $rankBadge = $result['rank'] == 1 ? 'gold' : ($result['rank'] == 2 ? 'silver' : ($result['rank'] == 3 ? 'bronze' : ''));
                                                    ?>
                                                    <?php if ($result['rank'] <= 3): ?>
                                                        <span class="badge bg-warning"><?php echo $result['rank']; ?></span>
                                                    <?php else: ?>
                                                        <?php echo $result['rank']; ?>
                                                    <?php endif; ?>
                                                </strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($result['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                                            <td><?php echo number_format($result['max_total_marks'], 0); ?></td>
                                            <td><strong><?php echo number_format($result['obtained_marks'], 2); ?></strong></td>
                                            <td>
                                                <?php
                                                $percentage = $result['percentage'];
                                                $badgeClass = $percentage >= 90 ? 'success' : ($percentage >= 75 ? 'primary' : ($percentage >= 60 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger')));
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo number_format($percentage, 2); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo $result['grade']; ?></strong>
                                            </td>
                                            <td><?php echo number_format($result['gpa'], 2); ?></td>
                                            <td>
                                                <?php if ($result['absent_subjects'] > 0): ?>
                                                    <span class="badge bg-danger"><?php echo $result['absent_subjects']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="no-print">
                                                <a href="<?php echo APP_URL; ?>modules/exams/report-cards.php?student_id=<?php echo $result['id']; ?>&exam_id=<?php echo $examId; ?>" 
                                                   class="btn btn-sm btn-info" target="_blank" title="Report Card">
                                                    <i class="ri-file-text-line"></i>
                                                </a>
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

