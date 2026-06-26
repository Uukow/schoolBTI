<?php
/**
 * View Student - Teacher Portal
 * 
 * View student details (only students in teacher's classes)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(teacherPortalRoles());

$pageTitle = 'Student Details';

// Get current user and teacher record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$teacher = null;
$teacherId = null;

if (!$isPortalViewer) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'dashboard.php');
    }
    $teacherId = $teacher['id'];
}

$currentSession = getCurrentSession();

$studentId = $_GET['id'] ?? 0;

if (empty($studentId)) {
    $_SESSION['error'] = 'Invalid student ID';
    redirect(APP_URL . 'modules/teacher/my-students.php');
}

// Get student details
if ($isPortalViewer) {
    // Super Admin can view any student
    $sql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE s.id = ?";
    $stmt = executeQuery($sql, 'i', [$studentId]);
    $student = fetchOne($stmt);
} else {
    // Teacher can only view students in their classes
    $sql = "SELECT DISTINCT s.*, c.class_name, sec.section_name, b.branch_name
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE s.id = ? AND cs.teacher_id = ? AND cs.session_id = ?";
    $stmt = executeQuery($sql, 'iii', [$studentId, $teacherId, $currentSession['id']]);
    $student = fetchOne($stmt);
}

if (!$student) {
    $_SESSION['error'] = 'Student not found or you do not have access to view this student.';
    redirect(APP_URL . 'modules/teacher/my-students.php');
}

// Get attendance summary
$attendanceSql = "SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_days
    FROM student_attendance 
    WHERE student_id = ?";
$attendanceStats = fetchOne(executeQuery($attendanceSql, 'i', [$studentId]));

// Get recent marks
$marksSql = "SELECT sm.*, es.exam_date, s.subject_name, e.exam_name
             FROM student_marks sm
             INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
             INNER JOIN exams e ON es.exam_id = e.id
             INNER JOIN subjects s ON es.subject_id = s.id
             WHERE sm.student_id = ?
             ORDER BY es.exam_date DESC
             LIMIT 10";
$recentMarks = fetchAll(executeQuery($marksSql, 'i', [$studentId]));

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
                        <h4 class="page-title">Student Details</h4>
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/teacher/my-students.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Students
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Student Info -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($student['photo'])): ?>
                                <img src="<?php echo APP_URL . $student['photo']; ?>" alt="Photo" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                            <?php else: ?>
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title bg-primary rounded-circle text-white display-4">
                                        <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($student['class_name'] . ' - ' . ($student['section_name'] ?? 'N/A')); ?></p>
                            
                            <div class="text-start mt-4">
                                <p class="text-muted mb-2"><i class="ri-user-line me-2"></i> <strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                                <p class="text-muted mb-2"><i class="ri-mail-line me-2"></i> <strong>Email:</strong> <?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></p>
                                <p class="text-muted mb-2"><i class="ri-phone-line me-2"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></p>
                                <p class="text-muted mb-2"><i class="ri-calendar-line me-2"></i> <strong>Date of Birth:</strong> <?php echo formatDate($student['date_of_birth']); ?></p>
                                <p class="text-muted mb-2"><i class="ri-genderless-line me-2"></i> <strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Summary -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Attendance Summary</h4>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <h3 class="text-success"><?php echo $attendanceStats['present_days'] ?? 0; ?></h3>
                                    <p class="text-muted mb-0">Present</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <h3 class="text-danger"><?php echo $attendanceStats['absent_days'] ?? 0; ?></h3>
                                    <p class="text-muted mb-0">Absent</p>
                                </div>
                                <div class="col-12">
                                    <p class="text-muted mb-0">Total Days: <strong><?php echo $attendanceStats['total_days'] ?? 0; ?></strong></p>
                                    <?php if ($attendanceStats['total_days'] > 0): ?>
                                        <?php $percentage = calculatePercentage($attendanceStats['present_days'], $attendanceStats['total_days']); ?>
                                        <p class="text-muted mb-0">Attendance: <strong><?php echo number_format($percentage, 2); ?>%</strong></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Details -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Personal Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['gender']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" class="form-control" value="<?php echo formatDate($student['date_of_birth']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" readonly>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <hr>

                            <h4 class="header-title mb-3">Academic Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Section</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Branch</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['branch_name'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['status']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Marks -->
                    <?php if (!empty($recentMarks)): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Recent Exam Results</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Exam</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Marks</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMarks as $mark): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($mark['exam_name']); ?></td>
                                                <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                                <td><?php echo formatDate($mark['exam_date']); ?></td>
                                                <td>
                                                    <?php if ($mark['is_absent']): ?>
                                                        <span class="badge bg-danger">Absent</span>
                                                    <?php else: ?>
                                                        <?php echo $mark['marks_obtained'] ?? 'N/A'; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$mark['is_absent'] && $mark['marks_obtained'] !== null): ?>
                                                        <span class="badge bg-success">Graded</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<?php include '../../includes/footer-scripts.php'; ?>

