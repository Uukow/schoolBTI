<?php
/**
 * My Timetable - Student Portal
 * 
 * Display student's weekly schedule
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Timetable';

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

// Check if student's class is graduated
$classGraduated = false;
if (!$isPortalViewer && $student && isset($student['current_class_id']) && $student['current_class_id']) {
    $classGraduated = isClassGraduated($student['current_class_id']);
}

// Get timetable
if ($isPortalViewer) {
    $timetable = [];
} else {
    if ($student && isset($student['current_class_id']) && isset($student['current_section_id']) && $student['current_class_id'] && $student['current_section_id']) {
        $sql = "SELECT t.*, c.class_name, c.graduation_status, sec.section_name, s.subject_name, s.subject_code,
                st.first_name as teacher_first_name, st.last_name as teacher_last_name
                FROM timetable t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN sections sec ON t.section_id = sec.id
                INNER JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN staff st ON t.teacher_id = st.id
                WHERE t.class_id = ? AND t.section_id = ? AND t.session_id = ?
                ORDER BY 
                    FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    t.start_time";
        $stmt = executeQuery($sql, 'iii', [$student['current_class_id'], $student['current_section_id'], $currentSession['id']]);
        $timetable = fetchAll($stmt);
    } else {
        $timetable = [];
    }
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
                        <h4 class="page-title">My Timetable</h4>
                        <div class="page-title-right">
                            <span class="text-muted">Session: <?php echo htmlspecialchars($currentSession['session_name'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable -->
            <div class="row">
                <div class="col-12">
                    <?php if (!$isPortalViewer && $classGraduated): ?>
                    <div class="alert alert-warning">
                        <h5><i class="ri-graduation-cap-line"></i> Class Graduated</h5>
                        <p>Your class has been graduated. This timetable is for reference only.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Weekly Schedule</h4>
                            
                            <?php if (empty($timetable)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No timetable assigned yet.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Day</th>
                                                <th>Time</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Room</th>
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
                                                            <td>
                                                                <?php echo date('H:i', strtotime($entry['start_time'])); ?> - 
                                                                <?php echo date('H:i', strtotime($entry['end_time'])); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo htmlspecialchars($entry['subject_name']); ?>
                                                                <small class="text-muted d-block"><?php echo htmlspecialchars($entry['subject_code']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars(($entry['teacher_first_name'] ?? 'N/A') . ' ' . ($entry['teacher_last_name'] ?? '')); ?></td>
                                                            <td><?php echo htmlspecialchars($entry['room_no'] ?? 'N/A'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($day); ?></strong></td>
                                                        <td colspan="4" class="text-muted text-center">No classes scheduled</td>
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

