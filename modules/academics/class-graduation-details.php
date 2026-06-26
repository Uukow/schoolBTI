<?php
/**
 * Class Graduation Details
 * 
 * View detailed information about a graduated class
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Class Graduation Details';

// Get class ID
$classId = $_GET['id'] ?? 0;

if (empty($classId)) {
    $_SESSION['error'] = 'Invalid class ID';
    redirect(APP_URL . 'modules/academics/class-graduation.php');
}

// Get current user
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

// Get class details with graduation information
$sql = "SELECT c.*, b.branch_name,
        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status IN ('Active', 'Graduated')) as total_students,
        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Graduated') as graduated_students,
        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Active') as active_students,
        (SELECT username FROM users WHERE id = c.graduated_by) as graduated_by_username
        FROM classes c
        LEFT JOIN branches b ON c.branch_id = b.id
        WHERE c.id = ?";

$stmt = executeQuery($sql, 'i', [$classId]);
$class = fetchOne($stmt);

if (!$class) {
    $_SESSION['error'] = 'Class not found';
    redirect(APP_URL . 'modules/academics/class-graduation.php');
}

// Get all students in this class
$studentsSql = "SELECT s.*, 
                (SELECT COUNT(*) FROM student_attendance sa WHERE sa.student_id = s.id) as attendance_count,
                (SELECT COUNT(*) FROM student_marks sm 
                 INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id 
                 WHERE sm.student_id = s.id) as marks_count
                FROM students s
                WHERE s.current_class_id = ?
                ORDER BY s.status DESC, s.first_name, s.last_name";
$studentsStmt = executeQuery($studentsSql, 'i', [$classId]);
$students = fetchAll($studentsStmt);

// Get graduation logs for this class
$logsSql = "SELECT cgl.*, u.username, u.email
            FROM class_graduation_logs cgl
            LEFT JOIN users u ON cgl.performed_by = u.id
            WHERE cgl.class_id = ?
            ORDER BY cgl.created_at DESC";
$logsStmt = executeQuery($logsSql, 'i', [$classId]);
$graduationLogs = fetchAll($logsStmt);

// Get academic records summary
$academicSummary = [
    'total_assignments' => 0,
    'total_exams' => 0,
    'total_attendance_records' => 0,
    'total_lesson_plans' => 0
];

$summarySql = "SELECT 
    (SELECT COUNT(*) FROM assignments WHERE class_id = ?) as total_assignments,
    (SELECT COUNT(*) FROM exams WHERE class_id = ?) as total_exams,
    (SELECT COUNT(*) FROM student_attendance WHERE class_id = ?) as total_attendance,
    (SELECT COUNT(*) FROM lesson_plans WHERE class_id = ?) as total_lessons";
$summaryStmt = executeQuery($summarySql, 'iiii', [$classId, $classId, $classId, $classId]);
$summary = fetchOne($summaryStmt);

if ($summary) {
    $academicSummary['total_assignments'] = $summary['total_assignments'] ?? 0;
    $academicSummary['total_exams'] = $summary['total_exams'] ?? 0;
    $academicSummary['total_attendance_records'] = $summary['total_attendance'] ?? 0;
    $academicSummary['total_lesson_plans'] = $summary['total_lessons'] ?? 0;
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
                            <a href="<?php echo APP_URL; ?>modules/academics/class-graduation.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Graduation
                            </a>
                        </div>
                        <h4 class="page-title">Class Graduation Details</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Class Information Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="header-title mb-3">Class Information</h4>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">Class Name:</th>
                                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Class Code:</th>
                                            <td><?php echo htmlspecialchars($class['class_code']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Branch:</th>
                                            <td><?php echo htmlspecialchars($class['branch_name'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Graduation Status:</th>
                                            <td>
                                                <?php if ($class['graduation_status'] === 'Graduated'): ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="ri-graduation-cap-line"></i> Graduated
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="ri-checkbox-circle-line"></i> Active
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if ($class['graduation_status'] === 'Graduated'): ?>
                                        <tr>
                                            <th>Graduated Date:</th>
                                            <td><?php echo $class['graduated_at'] ? formatDateTime($class['graduated_at']) : 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Graduated By:</th>
                                            <td><?php echo htmlspecialchars($class['graduated_by_username'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <?php if ($class['graduation_remarks']): ?>
                                        <tr>
                                            <th>Remarks:</th>
                                            <td><?php echo htmlspecialchars($class['graduation_remarks']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="header-title mb-3">Statistics</h4>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <div class="card bg-primary-lighten">
                                                <div class="card-body text-center">
                                                    <h3 class="mb-0"><?php echo $class['total_students']; ?></h3>
                                                    <p class="text-muted mb-0">Total Students</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="card bg-success-lighten">
                                                <div class="card-body text-center">
                                                    <h3 class="mb-0"><?php echo $class['graduated_students']; ?></h3>
                                                    <p class="text-muted mb-0">Graduated</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="card bg-info-lighten">
                                                <div class="card-body text-center">
                                                    <h3 class="mb-0"><?php echo $academicSummary['total_exams']; ?></h3>
                                                    <p class="text-muted mb-0">Exams</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="card bg-warning-lighten">
                                                <div class="card-body text-center">
                                                    <h3 class="mb-0"><?php echo $academicSummary['total_assignments']; ?></h3>
                                                    <p class="text-muted mb-0">Assignments</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Students (<?php echo count($students); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Admission No</th>
                                            <th>Status</th>
                                            <th>Attendance Records</th>
                                            <th>Marks Records</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No students found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                                    <td>
                                                        <?php if ($student['status'] === 'Graduated'): ?>
                                                            <span class="badge bg-secondary">Graduated</span>
                                                        <?php elseif ($student['status'] === 'Active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning"><?php echo htmlspecialchars($student['status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $student['attendance_count'] ?? 0; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $student['marks_count'] ?? 0; ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graduation History -->
            <?php if (!empty($graduationLogs)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Graduation History</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Action</th>
                                            <th>Students Affected</th>
                                            <th>Performed By</th>
                                            <th>IP Address</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($graduationLogs as $log): ?>
                                            <tr>
                                                <td><?php echo formatDateTime($log['created_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $log['action'] === 'Graduated' ? 'success' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($log['action']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $log['students_affected']; ?></td>
                                                <td><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></small></td>
                                                <td><?php echo htmlspecialchars($log['remarks'] ?? '-'); ?></td>
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

            <!-- Academic Records Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Academic Records Summary</h4>
                            <p class="text-muted">All academic records for this class are preserved in read-only mode for audit and reporting purposes.</p>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-info-lighten">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0"><?php echo $academicSummary['total_exams']; ?></h3>
                                            <p class="text-muted mb-0">Exams</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning-lighten">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0"><?php echo $academicSummary['total_assignments']; ?></h3>
                                            <p class="text-muted mb-0">Assignments</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success-lighten">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0"><?php echo $academicSummary['total_attendance_records']; ?></h3>
                                            <p class="text-muted mb-0">Attendance Records</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-primary-lighten">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0"><?php echo $academicSummary['total_lesson_plans']; ?></h3>
                                            <p class="text-muted mb-0">Lesson Plans</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

