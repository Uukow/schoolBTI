<?php
/**
 * My Marks - Student Portal
 * 
 * View student's exam marks and results
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Marks & Results';

// Get current user and student record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$student = null;
$studentId = null;

if ($isPortalViewer) {
    $studentId = null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found. Please contact administrator to link your user account to a student record.';
        $studentId = null;
    } else {
        $studentId = $student['id'];
    }
}

$currentSession = getCurrentSession();

// Get filter parameters
$examFilter = $_GET['exam_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';

// Get marks
if ($isPortalViewer) {
    $marks = [];
} else {
    $sql = "SELECT sm.*, es.exam_date, es.total_marks, s.subject_name, s.subject_code,
            e.exam_name
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN subjects s ON es.subject_id = s.id
            WHERE sm.student_id = ?";
    
    $params = [$studentId];
    $types = 'i';
    
    if (!empty($examFilter)) {
        $sql .= " AND es.exam_id = ?";
        $params[] = $examFilter;
        $types .= 'i';
    }
    
    if (!empty($subjectFilter)) {
        $sql .= " AND es.subject_id = ?";
        $params[] = $subjectFilter;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY es.exam_date DESC, s.subject_name";
    
    $stmt = executeQuery($sql, $types, $params);
    $marks = fetchAll($stmt);
}

// Get exams for filter
$examsSql = "SELECT DISTINCT e.* FROM exams e
            INNER JOIN exam_schedule es ON e.id = es.exam_id
            INNER JOIN student_marks sm ON es.id = sm.exam_schedule_id
            WHERE sm.student_id = ? AND e.session_id = ?
            ORDER BY e.start_date DESC";
$exams = $isPortalViewer ? [] : fetchAll(executeQuery($examsSql, 'ii', [$studentId, $currentSession['id']]));

// Get subjects for filter
$subjectsSql = "SELECT DISTINCT s.* FROM subjects s
                INNER JOIN exam_schedule es ON s.id = es.subject_id
                INNER JOIN student_marks sm ON es.id = sm.exam_schedule_id
                WHERE sm.student_id = ?
                ORDER BY s.subject_name";
$subjects = $isPortalViewer ? [] : fetchAll(executeQuery($subjectsSql, 'i', [$studentId]));

// Calculate overall statistics
$overallStats = [
    'total_exams' => 0,
    'total_marks_obtained' => 0,
    'total_marks_possible' => 0,
    'average_percentage' => 0,
    'average_grade' => 'N/A'
];

if (!$isPortalViewer && !empty($marks)) {
    $overallStats['total_exams'] = count($marks);
    foreach ($marks as $mark) {
        $overallStats['total_marks_obtained'] += $mark['marks_obtained'];
        $overallStats['total_marks_possible'] += $mark['total_marks'];
    }
    if ($overallStats['total_marks_possible'] > 0) {
        $overallStats['average_percentage'] = round(($overallStats['total_marks_obtained'] / $overallStats['total_marks_possible']) * 100, 2);
        $overallStats['average_grade'] = getGrade($overallStats['average_percentage']);
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
                        <h4 class="page-title">My Marks & Results</h4>
                    </div>
                </div>
            </div>

            <?php if (!$isPortalViewer): ?>
            <!-- Overall Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-list-3-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Exams</h5>
                            <h3 class="mt-3 mb-3"><?php echo $overallStats['total_exams']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-edit-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Marks</h5>
                            <h3 class="mt-3 mb-3">
                                <?php echo $overallStats['total_marks_obtained']; ?> / <?php echo $overallStats['total_marks_possible']; ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-percent-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Average %</h5>
                            <h3 class="mt-3 mb-3"><?php echo $overallStats['average_percentage']; ?>%</h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-star-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Average Grade</h5>
                            <h3 class="mt-3 mb-3">
                                <span class="badge bg-<?php echo $overallStats['average_percentage'] >= 50 ? 'success' : 'danger'; ?>">
                                    <?php echo $overallStats['average_grade']; ?>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Filter Marks</h4>
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">Exam</label>
                                            <select name="exam_id" class="form-select">
                                                <option value="">All Exams</option>
                                                <?php foreach ($exams as $exam): ?>
                                                    <option value="<?php echo $exam['id']; ?>" <?php echo $examFilter == $exam['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($exam['exam_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">Subject</label>
                                            <select name="subject_id" class="form-select">
                                                <option value="">All Subjects</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo $subject['id']; ?>" <?php echo $subjectFilter == $subject['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ri-search-line"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Marks Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Exam Results</h4>
                            
                            <?php if (empty($marks)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No marks found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="marks-table">
                                        <thead>
                                            <tr>
                                                <th>Exam</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Marks Obtained</th>
                                                <th>Total Marks</th>
                                                <th>Percentage</th>
                                                <th>Grade</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($marks as $mark): ?>
                                                <?php
                                                $percentage = calculatePercentage($mark['marks_obtained'], $mark['total_marks']);
                                                $grade = getGrade($percentage);
                                                $gradeClass = $percentage >= 50 ? 'success' : 'danger';
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($mark['exam_name']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($mark['subject_name']); ?>
                                                        <small class="text-muted d-block"><?php echo htmlspecialchars($mark['subject_code']); ?></small>
                                                    </td>
                                                    <td><?php echo formatDate($mark['exam_date']); ?></td>
                                                    <td><strong><?php echo $mark['marks_obtained']; ?></strong></td>
                                                    <td><?php echo $mark['total_marks']; ?></td>
                                                    <td><?php echo $percentage; ?>%</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $gradeClass; ?>"><?php echo $grade; ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($mark['remarks'] ?? 'N/A'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<script>
$(document).ready(function() {
    $('#marks-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'desc']]
    });
});
</script>

