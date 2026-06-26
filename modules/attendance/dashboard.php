<?php
/**
 * Attendance Dashboard - Admin Portal
 * 
 * View attendance statistics per class and per day:
 * - How many subjects were scheduled
 * - How many attendance records were taken
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Attendance Dashboard';

// Get current user
$currentUser = getCurrentUser();
$currentSession = getCurrentSession();

if (!$currentSession) {
    $_SESSION['error'] = 'No active academic session found. Please contact administrator.';
    redirect(APP_URL . 'dashboard.php');
}

// Get filters
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$classId = $_GET['class_id'] ?? '';
$dayOfWeek = date('l', strtotime($selectedDate));

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get attendance statistics
$attendanceStats = [];

if (!empty($classId)) {
    // Get scheduled subjects for this class on this day
    $scheduledSql = "SELECT DISTINCT 
                     t.subject_id, 
                     s.subject_name, 
                     s.subject_code,
                     t.section_id,
                     sec.section_name,
                     t.teacher_id,
                     st.first_name as teacher_first_name,
                     st.last_name as teacher_last_name
                     FROM timetable t
                     INNER JOIN subjects s ON t.subject_id = s.id
                     LEFT JOIN sections sec ON t.section_id = sec.id
                     LEFT JOIN staff st ON t.teacher_id = st.id
                     WHERE t.class_id = ? 
                     AND t.day_of_week = ? 
                     AND t.session_id = ?
                     ORDER BY s.subject_name, sec.section_name";
    
    $scheduledStmt = executeQuery($scheduledSql, 'isi', [$classId, $dayOfWeek, $currentSession['id']]);
    $scheduledSubjects = fetchAll($scheduledStmt);
    
    // Get attendance records taken for this class on this day
    $attendanceSql = "SELECT DISTINCT 
                     sa.subject_id,
                     s.subject_name,
                     s.subject_code,
                     COUNT(DISTINCT sa.student_id) as students_marked,
                     COUNT(*) as total_records,
                     SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_count,
                     SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                     sa.marked_by,
                     u.username as marked_by_username
                     FROM student_attendance sa
                     INNER JOIN subjects s ON sa.subject_id = s.id
                     LEFT JOIN users u ON sa.marked_by = u.id
                     WHERE sa.class_id = ? 
                     AND sa.attendance_date = ?
                     GROUP BY sa.subject_id, s.subject_name, s.subject_code, sa.marked_by, u.username
                     ORDER BY s.subject_name";
    
    $attendanceStmt = executeQuery($attendanceSql, 'is', [$classId, $selectedDate]);
    $attendanceRecords = fetchAll($attendanceStmt);
    
    // Create a map of attendance records by subject_id
    $attendanceMap = [];
    foreach ($attendanceRecords as $record) {
        $attendanceMap[$record['subject_id']] = $record;
    }
    
    // Combine scheduled subjects with attendance records
    foreach ($scheduledSubjects as $subject) {
        $subjectId = $subject['subject_id'];
        $attendanceData = $attendanceMap[$subjectId] ?? null;
        
        $attendanceStats[] = [
            'subject_id' => $subjectId,
            'subject_name' => $subject['subject_name'],
            'subject_code' => $subject['subject_code'],
            'section_name' => $subject['section_name'] ?? 'All Sections',
            'teacher_name' => ($subject['teacher_first_name'] ?? '') . ' ' . ($subject['teacher_last_name'] ?? ''),
            'is_scheduled' => true,
            'attendance_taken' => $attendanceData !== null,
            'students_marked' => $attendanceData['students_marked'] ?? 0,
            'total_records' => $attendanceData['total_records'] ?? 0,
            'present_count' => $attendanceData['present_count'] ?? 0,
            'absent_count' => $attendanceData['absent_count'] ?? 0,
            'marked_by' => $attendanceData['marked_by_username'] ?? null
        ];
    }
    
    // Get total students in class
    $totalStudentsSql = "SELECT COUNT(*) as total FROM students 
                        WHERE current_class_id = ? AND status = 'Active'";
    $totalStudentsStmt = executeQuery($totalStudentsSql, 'i', [$classId]);
    $totalStudents = fetchOne($totalStudentsStmt)['total'] ?? 0;
    
    // Calculate summary statistics
    $totalScheduled = count($scheduledSubjects);
    $totalTaken = count($attendanceRecords);
    $completionRate = $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100, 2) : 0;
} else {
    // Get summary for all classes (excluding graduated classes)
    $summarySql = "SELECT 
                   c.id as class_id,
                   c.class_name,
                   COUNT(DISTINCT t.subject_id) as scheduled_subjects,
                   COUNT(DISTINCT CASE WHEN sa.id IS NOT NULL THEN sa.subject_id END) as attendance_taken_subjects
                   FROM classes c
                   LEFT JOIN timetable t ON c.id = t.class_id 
                   AND t.day_of_week = ? 
                   AND t.session_id = ?
                   LEFT JOIN student_attendance sa ON c.id = sa.class_id 
                   AND sa.attendance_date = ?
                   WHERE c.is_active = 1
                   AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
                   GROUP BY c.id, c.class_name
                   ORDER BY c.class_order";
    
    $summaryStmt = executeQuery($summarySql, 'sis', [$dayOfWeek, $currentSession['id'], $selectedDate]);
    $attendanceStats = fetchAll($summaryStmt);
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
                            <button onclick="exportToExcel()" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export Excel
                            </button>
                        </div>
                        <h4 class="page-title">Attendance Dashboard</h4>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Date</label>
                                    <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
                                    <small class="text-muted">Day: <?php echo $dayOfWeek; ?></small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Class</label>
                                    <select class="form-select" name="class_id" id="classSelect">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> View Statistics
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($classId)): ?>
                <!-- Class-specific Statistics -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-calendar-schedule-line widget-icon text-primary"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Scheduled Subjects</h5>
                                <h3 class="mt-3 mb-3"><?php echo $totalScheduled ?? 0; ?></h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-1"><i class="ri-arrow-up-line"></i></span>
                                    <span class="text-nowrap">For <?php echo $dayOfWeek; ?></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-checkbox-circle-line widget-icon text-success"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Attendance Taken</h5>
                                <h3 class="mt-3 mb-3"><?php echo $totalTaken ?? 0; ?></h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-1"><i class="ri-arrow-up-line"></i></span>
                                    <span class="text-nowrap"><?php echo $completionRate ?? 0; ?>% Complete</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-user-line widget-icon text-info"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Total Students</h5>
                                <h3 class="mt-3 mb-3"><?php echo $totalStudents ?? 0; ?></h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-muted">In this class</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-alert-line widget-icon <?php echo ($totalScheduled - $totalTaken) > 0 ? 'text-warning' : 'text-success'; ?>"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Pending</h5>
                                <h3 class="mt-3 mb-3 <?php echo ($totalScheduled - $totalTaken) > 0 ? 'text-warning' : 'text-success'; ?>">
                                    <?php echo max(0, ($totalScheduled ?? 0) - ($totalTaken ?? 0)); ?>
                                </h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-muted">Subjects not marked</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Subject-wise Statistics -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">
                                    Subject-wise Attendance - <?php 
                                    $selectedClass = null;
                                    foreach ($classes as $class) {
                                        if ($class['id'] == $classId) {
                                            $selectedClass = $class;
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($selectedClass['class_name'] ?? 'Unknown Class'); 
                                    ?>
                                    <small class="text-muted">(<?php echo formatDate($selectedDate); ?> - <?php echo $dayOfWeek; ?>)</small>
                                </h4>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="attendance-stats-table">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Section</th>
                                                <th>Teacher</th>
                                                <th>Scheduled</th>
                                                <th>Attendance Taken</th>
                                                <th>Students Marked</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Marked By</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendanceStats as $stat): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($stat['subject_name']); ?></strong>
                                                        <?php if (!empty($stat['subject_code'])): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($stat['subject_code']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($stat['section_name'] ?? 'All'); ?></td>
                                                    <td><?php echo htmlspecialchars($stat['teacher_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <i class="ri-calendar-schedule-line"></i> Yes
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken']): ?>
                                                            <span class="badge bg-success">
                                                                <i class="ri-checkbox-circle-line"></i> Yes
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">
                                                                <i class="ri-close-circle-line"></i> No
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken']): ?>
                                                            <strong><?php echo $stat['students_marked']; ?></strong> / <?php echo $totalStudents; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken']): ?>
                                                            <span class="badge bg-success"><?php echo $stat['present_count']; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken']): ?>
                                                            <span class="badge bg-danger"><?php echo $stat['absent_count']; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken'] && !empty($stat['marked_by'])): ?>
                                                            <small><?php echo htmlspecialchars($stat['marked_by']); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken']): ?>
                                                            <span class="badge bg-success">Complete</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($stat['attendance_taken']): ?>
                                                            <a href="<?php echo APP_URL; ?>modules/attendance/view-students.php?date=<?php echo urlencode($selectedDate); ?>&class_id=<?php echo $classId; ?>&subject_id=<?php echo $stat['subject_id']; ?>" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="ri-eye-line"></i> View Students
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
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
            <?php else: ?>
                <!-- All Classes Summary -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">
                                    Attendance Summary - All Classes
                                    <small class="text-muted">(<?php echo formatDate($selectedDate); ?> - <?php echo $dayOfWeek; ?>)</small>
                                </h4>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="attendance-summary-table">
                                        <thead>
                                            <tr>
                                                <th>Class</th>
                                                <th>Scheduled Subjects</th>
                                                <th>Attendance Taken</th>
                                                <th>Completion Rate</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendanceStats as $stat): 
                                                $completionRate = $stat['scheduled_subjects'] > 0 
                                                    ? round(($stat['attendance_taken_subjects'] / $stat['scheduled_subjects']) * 100, 2) 
                                                    : 0;
                                            ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($stat['class_name']); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $stat['scheduled_subjects']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $stat['attendance_taken_subjects']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?php echo $completionRate == 100 ? 'bg-success' : ($completionRate >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $completionRate; ?>%"
                                                                 aria-valuenow="<?php echo $completionRate; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                <?php echo $completionRate; ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($completionRate == 100): ?>
                                                            <span class="badge bg-success">Complete</span>
                                                        <?php elseif ($completionRate >= 50): ?>
                                                            <span class="badge bg-warning">Partial</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?date=<?php echo urlencode($selectedDate); ?>&class_id=<?php echo $stat['class_id']; ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="ri-eye-line"></i> View Details
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
</div>

<?php include '../../includes/footer-scripts.php'; ?>

<script>
$(document).ready(function() {
    $('#attendance-stats-table, #attendance-summary-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function exportToExcel() {
    const table = document.querySelector('table');
    if (!table) {
        Swal.fire({
            icon: 'error',
            title: 'No Data',
            text: 'Please select a class and date first.'
        });
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText.replace(/,/g, ';'));
        }
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'attendance_dashboard_' + '<?php echo $selectedDate; ?>' + '_' + new Date().getTime() + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

