<?php
/**
 * My Timetable - Teacher Portal
 * 
 * Display teacher's weekly schedule (fully isolated view)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher', 'Super Admin']);

$pageTitle = __('my_timetable');

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$teacher = null;
$teacherId = null;

if ($isSuperAdmin) {
    // Super Admin sees all timetables
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

// Get timetable
if ($isSuperAdmin) {
    $sql = "SELECT t.*, c.class_name, sec.section_name, s.subject_name, s.subject_code, st.first_name as teacher_first_name, st.last_name as teacher_last_name
            FROM timetable t
            INNER JOIN classes c ON t.class_id = c.id
            INNER JOIN sections sec ON t.section_id = sec.id
            INNER JOIN subjects s ON t.subject_id = s.id
            LEFT JOIN staff st ON t.teacher_id = st.id
            WHERE t.session_id = ?
            ORDER BY 
                FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                t.start_time";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $timetable = fetchAll($stmt);
} else {
    $sql = "SELECT t.*, c.class_name, sec.section_name, s.subject_name, s.subject_code
            FROM timetable t
            INNER JOIN classes c ON t.class_id = c.id
            INNER JOIN sections sec ON t.section_id = sec.id
            INNER JOIN subjects s ON t.subject_id = s.id
            WHERE t.teacher_id = ? AND t.session_id = ?
            ORDER BY 
                FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                t.start_time";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $timetable = fetchAll($stmt);
}

// Organize by day
$timetableByDay = [];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

foreach ($daysOfWeek as $day) {
    $timetableByDay[$day] = [];
}

foreach ($timetable as $entry) {
    $timetableByDay[$entry['day_of_week']][] = $entry;
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
                        <h4 class="page-title"><?php echo __('my_timetable'); ?></h4>
                        <div class="page-title-right">
                            <span class="text-muted">Session: <?php echo htmlspecialchars($currentSession['session_name'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('weekly_schedule'); ?></h4>
                            
                            <?php if (empty($timetable)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> <?php echo __('no_timetable_assigned'); ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('day'); ?></th>
                                                <?php if ($isSuperAdmin): ?>
                                                <th><?php echo __('teacher'); ?></th>
                                                <?php endif; ?>
                                                <th><?php echo __('time'); ?></th>
                                                <th><?php echo __('class'); ?></th>
                                                <th><?php echo __('subject'); ?></th>
                                                <th><?php echo __('room'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($daysOfWeek as $day): ?>
                                                <?php if (!empty($timetableByDay[$day])): ?>
                                                    <?php foreach ($timetableByDay[$day] as $index => $entry): ?>
                                                        <tr>
                                                            <?php if ($index === 0): ?>
                                                                <td rowspan="<?php echo count($timetableByDay[$day]); ?>" class="align-middle">
                                                                    <strong><?php echo htmlspecialchars($day); ?></strong>
                                                                </td>
                                                            <?php endif; ?>
                                                            <?php if ($isSuperAdmin): ?>
                                                            <td><?php echo htmlspecialchars(($entry['teacher_first_name'] ?? '') . ' ' . ($entry['teacher_last_name'] ?? '')); ?></td>
                                                            <?php endif; ?>
                                                            <td>
                                                                <?php echo date('H:i', strtotime($entry['start_time'])); ?> - 
                                                                <?php echo date('H:i', strtotime($entry['end_time'])); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($entry['class_name'] . ' - ' . $entry['section_name']); ?></td>
                                                            <td>
                                                                <?php echo htmlspecialchars($entry['subject_name']); ?>
                                                                <small class="text-muted d-block"><?php echo htmlspecialchars($entry['subject_code']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($entry['room_no'] ?? 'N/A'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($day); ?></strong></td>
                                                        <td colspan="<?php echo $isSuperAdmin ? '5' : '4'; ?>" class="text-muted text-center"><?php echo __('no_classes_scheduled'); ?></td>
                                                    </tr>
                                                <?php endif; ?>
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

