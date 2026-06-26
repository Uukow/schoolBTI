<?php
/**
 * Attendance Classes List - Teacher Portal
 * 
 * List of classes assigned to teacher for attendance marking
 * Each class has its own separate attendance page
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher', 'Super Admin']);

$pageTitle = __('mark_attendance');

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$teacher = null;
$teacherId = null;

if ($isSuperAdmin) {
    // Super Admin can mark attendance for any class
    $teacherId = null;
} else {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'dashboard.php');
    }
    $teacherId = $teacher['id'];
}

$currentSession = getCurrentSession();

// Get selected date (default: today) to filter by timetable day
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$dayOfWeek = date('l', strtotime($selectedDate)); // Get day name (Monday, Tuesday, etc.)

// Get classes assigned to teacher - STRICT FILTERING: WHERE teacher_id = logged_in_teacher_id
// AND filtered by timetable to only show classes/subjects teacher teaches on selected day
if ($isSuperAdmin) {
    $sql = "SELECT DISTINCT c.*, 
            (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Active') as student_count
            FROM classes c
            WHERE c.is_active = 1
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            ORDER BY c.class_order";
    $classes = fetchAll(executeQuery($sql));
    
    // Get all subjects for each class
    foreach ($classes as &$class) {
        $subjectsSql = "SELECT DISTINCT s.id, s.subject_name, s.subject_code
                       FROM subjects s
                       INNER JOIN class_subjects cs ON s.id = cs.subject_id
                       WHERE cs.class_id = ? AND cs.session_id = ?
                       ORDER BY s.subject_name";
        $subjectsStmt = executeQuery($subjectsSql, 'ii', [$class['id'], $currentSession['id']]);
        $class['subjects'] = fetchAll($subjectsStmt);
    }
} else {
    // STRICT: Only classes where teacher is assigned via class_subjects
    // AND filtered by timetable to only show classes/subjects teacher teaches on selected day
    $sql = "SELECT DISTINCT c.*, 
            (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Active') as student_count
            FROM classes c
            INNER JOIN class_subjects cs ON c.id = cs.class_id
            WHERE cs.teacher_id = ? AND cs.session_id = ? 
            AND c.is_active = 1
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            ORDER BY c.class_order";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $classes = fetchAll($stmt);
    
    // Get subjects for each class that teacher teaches on the selected day (from timetable)
    foreach ($classes as &$class) {
        $subjectsSql = "SELECT DISTINCT s.id, s.subject_name, s.subject_code, t.section_id, sec.section_name
                       FROM subjects s
                       INNER JOIN class_subjects cs ON s.id = cs.subject_id
                       INNER JOIN timetable t ON s.id = t.subject_id AND t.class_id = cs.class_id
                       LEFT JOIN sections sec ON t.section_id = sec.id
                       WHERE cs.teacher_id = ? AND cs.class_id = ? AND cs.session_id = ?
                       AND t.day_of_week = ? AND t.teacher_id = ?
                       ORDER BY s.subject_name, sec.section_name";
        $subjectsStmt = executeQuery($subjectsSql, 'iiiss', [$teacherId, $class['id'], $currentSession['id'], $dayOfWeek, $teacherId]);
        $class['subjects'] = fetchAll($subjectsStmt);
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
                        <h4 class="page-title"><?php echo __('mark_attendance'); ?> - <?php echo __('select_class'); ?></h4>
                        <div class="page-title-right">
                            <span class="text-muted"><?php echo __('select_class'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Select Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
                                    <small class="text-muted">Only classes/subjects you teach on this day will be shown</small>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Filter by Date
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> 
                        <strong>Note:</strong> You can only mark attendance for subjects you teach. Only classes/subjects scheduled for <strong><?php echo $dayOfWeek; ?></strong> (<?php echo formatDate($selectedDate); ?>) are shown below.
                    </div>
                </div>
            </div>

            <!-- Classes Grid -->
            <div class="row">
                <?php if (empty($classes)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="ri-alert-line"></i> <?php echo __('no_classes_assigned'); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $hasAnySubjects = false;
                    foreach ($classes as $class): 
                        // Only show classes that have subjects for this teacher on this day
                        if (!empty($class['subjects'])) {
                            $hasAnySubjects = true;
                    ?>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-lg me-3">
                                            <div class="avatar-title bg-primary rounded-circle text-white display-5">
                                                <?php echo strtoupper(substr($class['class_name'], 0, 2)); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="mb-1"><?php echo htmlspecialchars($class['class_name']); ?></h4>
                                            <p class="text-muted mb-0">
                                                <i class="ri-user-3-line"></i> <?php echo $class['student_count']; ?> <?php echo __('students'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong class="text-muted d-block mb-2">Subjects for <?php echo $dayOfWeek; ?>:</strong>
                                        <?php foreach ($class['subjects'] as $subject): ?>
                                            <a href="<?php echo APP_URL; ?>modules/teacher/attendance.php?class_id=<?php echo $class['id']; ?>&subject_id=<?php echo $subject['id']; ?>&date=<?php echo urlencode($selectedDate); ?><?php echo !empty($subject['section_id']) ? '&section_id=' . $subject['section_id'] : ''; ?>" 
                                               class="btn btn-sm btn-outline-primary mb-2 me-2">
                                                <i class="ri-book-open-line"></i> <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                <?php if (!empty($subject['section_name'])): ?>
                                                    <small>(<?php echo htmlspecialchars($subject['section_name']); ?>)</small>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    endforeach; 
                    
                    if (!$hasAnySubjects):
                    ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="ri-alert-line"></i> 
                                No classes/subjects are scheduled for you on <strong><?php echo $dayOfWeek; ?></strong> (<?php echo formatDate($selectedDate); ?>). 
                                Please select a different date or contact administrator if you believe this is incorrect.
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<?php include '../../includes/footer-scripts.php'; ?>








