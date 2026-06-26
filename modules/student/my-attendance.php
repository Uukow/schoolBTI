<?php
/**
 * My Attendance - Student Portal
 * 
 * View student's own attendance records
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Attendance';

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

// Get date filter
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-t'); // Last day of current month

// Get attendance records with subject information
if ($isPortalViewer) {
    $attendanceRecords = [];
    $attendanceStats = [
        'total_days' => 0,
        'present_days' => 0,
        'absent_days' => 0,
        'late_days' => 0,
        'leave_days' => 0,
        'attendance_percentage' => 0
    ];
    $subjectStats = [];
} else {
    // Get attendance records with subject details
    $sql = "SELECT sa.*, s.subject_name, s.subject_code
            FROM student_attendance sa
            LEFT JOIN subjects s ON sa.subject_id = s.id
            WHERE sa.student_id = ? AND sa.attendance_date BETWEEN ? AND ?
            ORDER BY sa.attendance_date DESC, s.subject_name";
    $stmt = executeQuery($sql, 'iss', [$studentId, $dateFrom, $dateTo]);
    $attendanceRecords = fetchAll($stmt);

    // Get overall attendance statistics (all subjects combined) - filtered by date range
    $sql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = 'Leave' THEN 1 ELSE 0 END) as leave_days
            FROM student_attendance 
            WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
    $stmt = executeQuery($sql, 'iss', [$studentId, $dateFrom, $dateTo]);
    $attendanceStats = fetchOne($stmt);
    
    $attendanceStats['attendance_percentage'] = 0;
    if ($attendanceStats['total_days'] > 0) {
        $attendanceStats['attendance_percentage'] = round(($attendanceStats['present_days'] / $attendanceStats['total_days']) * 100, 2);
    }
    
    // Weekly / Monthly / Yearly absence percentages (all subjects)
    $today = date('Y-m-d');

    // Current week (Monday - Sunday)
    $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($today)));
    $weekEnd   = date('Y-m-d', strtotime('sunday this week', strtotime($today)));

    // Current month
    $monthStart = date('Y-m-01', strtotime($today));
    $monthEnd   = date('Y-m-t', strtotime($today));

    // Current year
    $yearStart = date('Y-01-01', strtotime($today));
    $yearEnd   = date('Y-12-31', strtotime($today));

    $absenceTrends = [
        'week'  => ['label' => 'This Week',  'total' => 0, 'absent' => 0, 'percentage' => 0],
        'month' => ['label' => 'This Month', 'total' => 0, 'absent' => 0, 'percentage' => 0],
        'year'  => ['label' => 'This Year',  'total' => 0, 'absent' => 0, 'percentage' => 0],
    ];

    // Helper closure to calculate absence percentage for a period
    $calculateAbsence = function($start, $end) use ($studentId) {
        $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days
                FROM student_attendance
                WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
        $stmt = executeQuery($sql, 'iss', [$studentId, $start, $end]);
        $row = fetchOne($stmt);

        $total = (int)($row['total_days'] ?? 0);
        $absent = (int)($row['absent_days'] ?? 0);
        $percentage = $total > 0 ? round(($absent / $total) * 100, 2) : 0;

        return [$total, $absent, $percentage];
    };

    // Weekly
    list($totalWeek, $absentWeek, $percWeek) = $calculateAbsence($weekStart, $weekEnd);
    $absenceTrends['week']['total'] = $totalWeek;
    $absenceTrends['week']['absent'] = $absentWeek;
    $absenceTrends['week']['percentage'] = $percWeek;

    // Monthly
    list($totalMonth, $absentMonth, $percMonth) = $calculateAbsence($monthStart, $monthEnd);
    $absenceTrends['month']['total'] = $totalMonth;
    $absenceTrends['month']['absent'] = $absentMonth;
    $absenceTrends['month']['percentage'] = $percMonth;

    // Yearly
    list($totalYear, $absentYear, $percYear) = $calculateAbsence($yearStart, $yearEnd);
    $absenceTrends['year']['total'] = $totalYear;
    $absenceTrends['year']['absent'] = $absentYear;
    $absenceTrends['year']['percentage'] = $percYear;
    
    // Get per-subject attendance statistics - filtered by date range
    $subjectSql = "SELECT 
                   s.id, s.subject_name, s.subject_code,
                   COUNT(*) as total_days,
                   SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_days,
                   SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
                   SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late_days,
                   SUM(CASE WHEN sa.status = 'Leave' THEN 1 ELSE 0 END) as leave_days
                   FROM student_attendance sa
                   INNER JOIN subjects s ON sa.subject_id = s.id
                   WHERE sa.student_id = ? AND sa.attendance_date BETWEEN ? AND ?
                   GROUP BY s.id, s.subject_name, s.subject_code
                   ORDER BY s.subject_name";
    $subjectStmt = executeQuery($subjectSql, 'iss', [$studentId, $dateFrom, $dateTo]);
    $subjectStatsRaw = fetchAll($subjectStmt);
    
    // Calculate percentages for each subject
    $subjectStats = [];
    foreach ($subjectStatsRaw as $stat) {
        $percentage = 0;
        if ($stat['total_days'] > 0) {
            $percentage = round(($stat['present_days'] / $stat['total_days']) * 100, 2);
        }
        $stat['attendance_percentage'] = $percentage;
        $subjectStats[] = $stat;
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
                        <h4 class="page-title">My Attendance</h4>
                    </div>
                </div>
            </div>

            <?php if (!$isPortalViewer): ?>
            <!-- Overall Statistics Cards -->
            <div class="row">
                <div class="col-12">
                    <h4 class="header-title mb-3">Overall Attendance (All Subjects Combined)</h4>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-calendar-check-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Days</h5>
                            <h3 class="mt-3 mb-3"><?php echo $attendanceStats['total_days']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-checkbox-circle-line widget-icon text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Present</h5>
                            <h3 class="mt-3 mb-3"><?php echo $attendanceStats['present_days']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-close-circle-line widget-icon text-danger"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Absent</h5>
                            <h3 class="mt-3 mb-3"><?php echo $attendanceStats['absent_days']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-percent-line widget-icon text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Overall Attendance %</h5>
                            <h3 class="mt-3 mb-3 <?php echo $attendanceStats['attendance_percentage'] >= 75 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $attendanceStats['attendance_percentage']; ?>%
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly / Monthly / Yearly Absence Percentages -->
            <div class="row mt-3">
                <div class="col-12">
                    <h4 class="header-title mb-3">Absence Percentage (All Subjects)</h4>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-calendar-week-line widget-icon text-danger"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">This Week</h5>
                            <h3 class="mt-3 mb-1 text-danger">
                                <?php echo $absenceTrends['week']['percentage']; ?>%
                            </h3>
                            <p class="mb-0 text-muted">
                                <?php echo $absenceTrends['week']['absent']; ?> absences out of <?php echo $absenceTrends['week']['total']; ?> days
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-calendar-2-line widget-icon text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">This Month</h5>
                            <h3 class="mt-3 mb-1 text-danger">
                                <?php echo $absenceTrends['month']['percentage']; ?>%
                            </h3>
                            <p class="mb-0 text-muted">
                                <?php echo $absenceTrends['month']['absent']; ?> absences out of <?php echo $absenceTrends['month']['total']; ?> days
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-calendar-event-line widget-icon text-secondary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">This Year</h5>
                            <h3 class="mt-3 mb-1 text-danger">
                                <?php echo $absenceTrends['year']['percentage']; ?>%
                            </h3>
                            <p class="mb-0 text-muted">
                                <?php echo $absenceTrends['year']['absent']; ?> absences out of <?php echo $absenceTrends['year']['total']; ?> days
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Per-Subject Statistics -->
            <?php if (!empty($subjectStats)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Attendance by Subject</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Total Days</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                            <th>Late</th>
                                            <th>Leave</th>
                                            <th>Attendance %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjectStats as $stat): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($stat['subject_name']); ?></strong>
                                                <?php if (!empty($stat['subject_code'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($stat['subject_code']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $stat['total_days']; ?></td>
                                            <td><span class="badge bg-success"><?php echo $stat['present_days']; ?></span></td>
                                            <td><span class="badge bg-danger"><?php echo $stat['absent_days']; ?></span></td>
                                            <td><span class="badge bg-warning"><?php echo $stat['late_days']; ?></span></td>
                                            <td><span class="badge bg-info"><?php echo $stat['leave_days']; ?></span></td>
                                            <td>
                                                <strong class="<?php echo $stat['attendance_percentage'] >= 75 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $stat['attendance_percentage']; ?>%
                                                </strong>
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

            <!-- Filter Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Filter Attendance</h4>
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">From Date</label>
                                            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">To Date</label>
                                            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
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

            <!-- Attendance Records -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Attendance Records</h4>
                            
                            <?php if (empty($attendanceRecords)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No attendance records found for the selected period.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="attendance-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendanceRecords as $record): ?>
                                                <tr>
                                                    <td><?php echo formatDate($record['attendance_date']); ?></td>
                                                    <td><?php echo date('l', strtotime($record['attendance_date'])); ?></td>
                                                    <td>
                                                        <?php if (!empty($record['subject_name'])): ?>
                                                            <strong><?php echo htmlspecialchars($record['subject_name']); ?></strong>
                                                            <?php if (!empty($record['subject_code'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($record['subject_code']); ?></small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusClass = 'bg-success';
                                                        if ($record['status'] == 'Absent') $statusClass = 'bg-danger';
                                                        elseif ($record['status'] == 'Late') $statusClass = 'bg-warning';
                                                        elseif ($record['status'] == 'Leave') $statusClass = 'bg-info';
                                                        elseif ($record['status'] == 'Half Day') $statusClass = 'bg-secondary';
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($record['status']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($record['remarks'] ?? 'N/A'); ?></td>
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
    $('#attendance-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']]
    });
});
</script>

