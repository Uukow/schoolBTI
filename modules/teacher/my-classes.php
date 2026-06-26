<?php
/**
 * My Classes - Teacher Portal
 * 
 * Display classes and subjects assigned to the teacher
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(teacherPortalRoles());

$pageTitle = __('my_classes');

// Get current user and teacher record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$teacher = null;
$teacherId = null;

if ($isPortalViewer) {
    // Super Admin sees all classes/subjects
    $teacherId = null;
} else {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'modules/teacher/dashboard.php');
    }
    $teacherId = $teacher['id'];
}

$currentSession = getCurrentSession();

// Check if session exists
if (!$currentSession) {
    $_SESSION['error'] = 'No active session found. Please contact administrator.';
    redirect(APP_URL . 'modules/teacher/dashboard.php');
}

// Get assigned classes and subjects
if ($isPortalViewer) {
    $sql = "SELECT cs.*, c.class_name, s.subject_name, s.subject_code, st.first_name as teacher_first_name, st.last_name as teacher_last_name,
            (SELECT COUNT(*) FROM students st WHERE st.current_class_id = cs.class_id AND st.status = 'Active') as student_count
            FROM class_subjects cs
            INNER JOIN classes c ON cs.class_id = c.id
            INNER JOIN subjects s ON cs.subject_id = s.id
            LEFT JOIN staff st ON cs.teacher_id = st.id
            WHERE cs.session_id = ?
            ORDER BY c.class_order, s.subject_name";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $assignedClasses = fetchAll($stmt);
} else {
    $sql = "SELECT cs.*, c.class_name, s.subject_name, s.subject_code,
            (SELECT COUNT(*) FROM students st WHERE st.current_class_id = cs.class_id AND st.status = 'Active') as student_count
            FROM class_subjects cs
            INNER JOIN classes c ON cs.class_id = c.id
            INNER JOIN subjects s ON cs.subject_id = s.id
            WHERE cs.teacher_id = ? AND cs.session_id = ?
            ORDER BY c.class_order, s.subject_name";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $assignedClasses = fetchAll($stmt);
    
    // If teacher has no classes assigned, redirect to teacher dashboard
    if (empty($assignedClasses)) {
        $_SESSION['info'] = 'No classes or subjects assigned to you yet. Please contact administrator.';
        redirect(APP_URL . 'modules/teacher/dashboard.php');
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
                        <h4 class="page-title"><?php echo __('my_classes_subjects'); ?></h4>
                    </div>
                </div>
            </div>

            <!-- Classes List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('assigned_classes_subjects'); ?></h4>
                            
                            <?php if (empty($assignedClasses)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> <?php echo __('no_classes_assigned'); ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="classes-table">
                                        <thead>
                                            <tr>
                                                <?php if ($isPortalViewer): ?>
                                                <th><?php echo __('teacher'); ?></th>
                                                <?php endif; ?>
                                                <th><?php echo __('class'); ?></th>
                                                <th><?php echo __('subject'); ?></th>
                                                <th><?php echo __('subject_code'); ?></th>
                                                <th><?php echo __('students'); ?></th>
                                                <th><?php echo __('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignedClasses as $class): ?>
                                                <tr>
                                                    <?php if ($isPortalViewer): ?>
                                                    <td><?php echo htmlspecialchars(($class['teacher_first_name'] ?? '') . ' ' . ($class['teacher_last_name'] ?? '')); ?></td>
                                                    <?php endif; ?>
                                                    <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($class['subject_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($class['subject_code']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $class['student_count']; ?> <?php echo __('students'); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/teacher/my-students.php?class_id=<?php echo $class['class_id']; ?>&subject_id=<?php echo $class['subject_id']; ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="ri-user-3-line"></i> <?php echo __('view_students'); ?>
                                                        </a>
                                                        <a href="<?php echo APP_URL; ?>modules/teacher/attendance.php?class_id=<?php echo $class['class_id']; ?>" 
                                                           class="btn btn-sm btn-success">
                                                            <i class="ri-calendar-check-line"></i> <?php echo __('attendance'); ?>
                                                        </a>
                                                    </td>
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

<?php include '../../includes/footer-scripts.php'; ?>

<script>
$(document).ready(function() {
    $('#classes-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>

