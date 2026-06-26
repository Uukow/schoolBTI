<?php
/**
 * Teacher Portal Dashboard
 * 
 * Teacher-specific dashboard with isolated data view
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(teacherPortalRoles());

$pageTitle = __('teacher_dashboard');

// Get current user and teacher record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$teacher = null;
$teacherId = null;

if ($isPortalViewer) {
    // Super Admin can view all data - no teacher filtering
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

// Get teacher statistics
$stats = [];

if ($isPortalViewer) {
    // Super Admin sees all data
    $sql = "SELECT COUNT(DISTINCT cs.class_id) as count 
            FROM class_subjects cs 
            WHERE cs.session_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $stats['total_classes'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT cs.subject_id) as count 
            FROM class_subjects cs 
            WHERE cs.session_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $stats['total_subjects'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT s.id) as count 
            FROM students s
            WHERE s.status = 'Active'";
    $stmt = executeQuery($sql);
    $stats['total_students'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT s.id) as count 
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = CURDATE()
            WHERE s.status = 'Active' AND sa.id IS NULL";
    $stmt = executeQuery($sql);
    $stats['pending_attendance'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT es.id) as count 
            FROM exam_schedule es
            LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
            WHERE es.exam_date <= CURDATE() AND sm.id IS NULL";
    $stmt = executeQuery($sql);
    $stats['pending_marks'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT DISTINCT t.*, c.class_name, sec.section_name, s.subject_name, t.start_time, t.end_time
            FROM timetable t
            INNER JOIN classes c ON t.class_id = c.id
            INNER JOIN sections sec ON t.section_id = sec.id
            INNER JOIN subjects s ON t.subject_id = s.id
            WHERE t.session_id = ? 
            AND t.day_of_week = UPPER(DAYNAME(CURDATE()))
            ORDER BY t.start_time";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $todayClasses = fetchAll($stmt);
} else {
    // Teacher sees only their data
    $sql = "SELECT COUNT(DISTINCT cs.class_id) as count 
            FROM class_subjects cs 
            WHERE cs.teacher_id = ? AND cs.session_id = ?";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $stats['total_classes'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT cs.subject_id) as count 
            FROM class_subjects cs 
            WHERE cs.teacher_id = ? AND cs.session_id = ?";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $stats['total_subjects'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT s.id) as count 
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
            WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.status = 'Active'";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $stats['total_students'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT s.id) as count 
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
            LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = CURDATE()
            WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.status = 'Active' AND sa.id IS NULL";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $stats['pending_attendance'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(DISTINCT es.id) as count 
            FROM exam_schedule es
            INNER JOIN class_subjects cs ON es.subject_id = cs.subject_id
            LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
            WHERE cs.teacher_id = ? AND es.exam_date <= CURDATE() AND sm.id IS NULL";
    $stmt = executeQuery($sql, 'i', [$teacherId]);
    $stats['pending_marks'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT DISTINCT t.*, c.class_name, sec.section_name, s.subject_name, t.start_time, t.end_time
            FROM timetable t
            INNER JOIN classes c ON t.class_id = c.id
            INNER JOIN sections sec ON t.section_id = sec.id
            INNER JOIN subjects s ON t.subject_id = s.id
            WHERE t.teacher_id = ? AND t.session_id = ? 
            AND t.day_of_week = UPPER(DAYNAME(CURDATE()))
            ORDER BY t.start_time";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $todayClasses = fetchAll($stmt);
}

// Recent announcements
$sql = "SELECT * FROM announcements 
        WHERE (target_audience = 'All' OR target_audience = 'Teachers')
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY created_at DESC LIMIT 5";
$announcements = fetchAll(executeQuery($sql));

// Recent lesson plans
if ($isPortalViewer) {
    $sql = "SELECT lp.*, c.class_name, s.subject_name, st.first_name, st.last_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            INNER JOIN staff st ON lp.teacher_id = st.id
            WHERE lp.session_id = ?
            ORDER BY lp.lesson_date DESC LIMIT 5";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $recentLessonPlans = fetchAll($stmt);
} else {
    $sql = "SELECT lp.*, c.class_name, s.subject_name 
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            WHERE lp.teacher_id = ? AND lp.session_id = ?
            ORDER BY lp.lesson_date DESC LIMIT 5";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $recentLessonPlans = fetchAll($stmt);
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
                        <h4 class="page-title"><?php echo __('teacher_dashboard'); ?></h4>
                        <div class="page-title-right">
                            <?php if ($isPortalViewer): ?>
                                <span class="text-muted">Super Admin View - All Teachers</span>
                            <?php else: ?>
                                <span class="text-muted"><?php echo __('welcome'); ?>, <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-book-open-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="<?php echo __('classes'); ?>"><?php echo __('classes'); ?></h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_classes']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-book-2-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="<?php echo __('subjects'); ?>"><?php echo __('subjects'); ?></h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_subjects']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-user-3-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="<?php echo __('students'); ?>"><?php echo __('students'); ?></h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_students']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-calendar-check-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="<?php echo __('pending_attendance'); ?>"><?php echo __('pending_attendance'); ?></h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['pending_attendance']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Today's Classes -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('todays_classes'); ?></h4>
                            <?php if (empty($todayClasses)): ?>
                                <p class="text-muted"><?php echo __('no_classes_today'); ?></p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('time'); ?></th>
                                                <th><?php echo __('class'); ?></th>
                                                <th><?php echo __('subject'); ?></th>
                                                <th><?php echo __('room'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todayClasses as $class): ?>
                                                <tr>
                                                    <td><?php echo date('H:i', strtotime($class['start_time'])); ?> - <?php echo date('H:i', strtotime($class['end_time'])); ?></td>
                                                    <td><?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($class['subject_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($class['room_no'] ?? 'N/A'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('recent_announcements'); ?></h4>
                            <?php if (empty($announcements)): ?>
                                <p class="text-muted"><?php echo __('no_announcements'); ?></p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($announcements as $announcement): ?>
                                        <div class="list-group-item px-0">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars(substr($announcement['content'], 0, 100)); ?>...</p>
                                            <small class="text-muted"><?php echo formatDate($announcement['created_at']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('quick_actions'); ?></h4>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/teacher/my-classes.php" class="btn btn-primary w-100">
                                        <i class="ri-book-open-line"></i> <?php echo __('my_classes'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/teacher/my-students.php" class="btn btn-info w-100">
                                        <i class="ri-user-3-line"></i> <?php echo __('my_students'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/teacher/attendance-classes.php" class="btn btn-success w-100">
                                        <i class="ri-calendar-check-line"></i> <?php echo __('mark_attendance'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/teacher/marks-entry.php" class="btn btn-warning w-100">
                                        <i class="ri-file-edit-line"></i> <?php echo __('enter_marks'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<?php include '../../includes/footer-scripts.php'; ?>

