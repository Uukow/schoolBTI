<?php
/**
 * Attendance Reports
 * 
 * Generate attendance reports for students and staff
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Attendance Reports';

// Get current user
$currentUser = getCurrentUser();

// Get filters
$reportType = $_GET['report_type'] ?? 'student-daily';
$attendanceType = $_GET['attendance_type'] ?? 'student'; // student or staff
$classId = $_GET['class_id'] ?? '';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Get classes
$classesSql = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Initialize report data
$reportData = [];
$reportTitle = 'Attendance Report';

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
                        <h4 class="page-title">Attendance Reports</h4>
                    </div>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label required">Attendance Type</label>
                                    <select class="form-select" name="attendance_type" id="attendanceType" required>
                                        <option value="student" <?php echo $attendanceType == 'student' ? 'selected' : ''; ?>>Student Attendance</option>
                                        <option value="staff" <?php echo $attendanceType == 'staff' ? 'selected' : ''; ?>>Staff Attendance</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">Report Type</label>
                                    <select class="form-select" name="report_type" id="reportType" required>
                                        <?php if ($attendanceType == 'student'): ?>
                                            <option value="student-daily" <?php echo $reportType == 'student-daily' ? 'selected' : ''; ?>>Daily Attendance</option>
                                            <option value="student-monthly" <?php echo $reportType == 'student-monthly' ? 'selected' : ''; ?>>Monthly Summary</option>
                                            <option value="student-class" <?php echo $reportType == 'student-class' ? 'selected' : ''; ?>>Class-wise Summary</option>
                                            <option value="student-individual" <?php echo $reportType == 'student-individual' ? 'selected' : ''; ?>>Individual Student</option>
                                        <?php else: ?>
                                            <option value="staff-daily" <?php echo $reportType == 'staff-daily' ? 'selected' : ''; ?>>Daily Attendance</option>
                                            <option value="staff-monthly" <?php echo $reportType == 'staff-monthly' ? 'selected' : ''; ?>>Monthly Summary</option>
                                            <option value="staff-department" <?php echo $reportType == 'staff-department' ? 'selected' : ''; ?>>Department-wise</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if ($attendanceType == 'student'): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Class</label>
                                    <select class="form-select" name="class_id">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Generate Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo $reportTitle; ?></h4>
                            
                            <div id="reportContent">
                                <?php
                                // Generate report based on type
                                if ($attendanceType == 'student') {
                                    // Student attendance reports
                                    $sql = "SELECT sa.*, s.student_id, s.first_name, s.last_name, c.class_name,
                                            COUNT(*) as total_days,
                                            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_days,
                                            SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
                                            SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late_days
                                            FROM student_attendance sa
                                            INNER JOIN students s ON sa.student_id = s.id
                                            LEFT JOIN classes c ON s.current_class_id = c.id
                                            WHERE sa.attendance_date BETWEEN ? AND ?";
                                    
                                    $params = [$startDate, $endDate];
                                    $types = 'ss';
                                    
                                    if (!empty($classId)) {
                                        $sql .= " AND s.current_class_id = ?";
                                        $params[] = $classId;
                                        $types .= 'i';
                                    }
                                    
                                    if ($reportType == 'student-monthly' || $reportType == 'student-class') {
                                        $sql .= " GROUP BY s.id, s.student_id, s.first_name, s.last_name, c.class_name";
                                    }
                                    
                                    $sql .= " ORDER BY c.class_name, s.first_name";
                                    
                                    $reportData = fetchAll(executeQuery($sql, $types, $params));
                                    
                                    if ($reportType == 'student-daily') {
                                        // Daily attendance report
                                        ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Student</th>
                                                        <th>Class</th>
                                                        <th>Status</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reportData as $row): ?>
                                                    <tr>
                                                        <td><?php echo formatDate($row['attendance_date']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $row['status'] == 'Present' ? 'success' : ($row['status'] == 'Absent' ? 'danger' : 'warning'); ?>">
                                                                <?php echo htmlspecialchars($row['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['remarks'] ?? '-'); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                    } elseif ($reportType == 'student-monthly' || $reportType == 'student-class') {
                                        // Monthly/Class summary
                                        ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Student ID</th>
                                                        <th>Student Name</th>
                                                        <th>Class</th>
                                                        <th>Total Days</th>
                                                        <th>Present</th>
                                                        <th>Absent</th>
                                                        <th>Late</th>
                                                        <th>Attendance %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reportData as $row): 
                                                        $attendancePercent = $row['total_days'] > 0 ? 
                                                            round(($row['present_days'] / $row['total_days']) * 100, 2) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                        <td><?php echo $row['total_days']; ?></td>
                                                        <td><span class="badge bg-success"><?php echo $row['present_days']; ?></span></td>
                                                        <td><span class="badge bg-danger"><?php echo $row['absent_days']; ?></span></td>
                                                        <td><span class="badge bg-warning"><?php echo $row['late_days']; ?></span></td>
                                                        <td>
                                                            <strong class="<?php echo $attendancePercent >= 75 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $attendancePercent; ?>%
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    // Staff attendance reports
                                    $sql = "SELECT sa.*, s.staff_id, s.first_name, s.last_name, s.designation,
                                            COUNT(*) as total_days,
                                            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_days,
                                            SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
                                            SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late_days
                                            FROM staff_attendance sa
                                            INNER JOIN staff s ON sa.staff_id = s.id
                                            WHERE sa.attendance_date BETWEEN ? AND ?";
                                    
                                    $params = [$startDate, $endDate];
                                    $types = 'ss';
                                    
                                    if ($reportType == 'staff-monthly') {
                                        $sql .= " GROUP BY s.id, s.staff_id, s.first_name, s.last_name, s.designation";
                                    }
                                    
                                    $sql .= " ORDER BY s.first_name";
                                    
                                    $reportData = fetchAll(executeQuery($sql, $types, $params));
                                    
                                    if ($reportType == 'staff-daily') {
                                        ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Staff</th>
                                                        <th>Designation</th>
                                                        <th>Check In</th>
                                                        <th>Check Out</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reportData as $row): ?>
                                                    <tr>
                                                        <td><?php echo formatDate($row['attendance_date']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                                        <td><?php echo $row['check_in'] ? date('H:i', strtotime($row['check_in'])) : '-'; ?></td>
                                                        <td><?php echo $row['check_out'] ? date('H:i', strtotime($row['check_out'])) : '-'; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $row['status'] == 'Present' ? 'success' : ($row['status'] == 'Absent' ? 'danger' : 'warning'); ?>">
                                                                <?php echo htmlspecialchars($row['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Staff ID</th>
                                                        <th>Staff Name</th>
                                                        <th>Designation</th>
                                                        <th>Total Days</th>
                                                        <th>Present</th>
                                                        <th>Absent</th>
                                                        <th>Late</th>
                                                        <th>Attendance %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reportData as $row): 
                                                        $attendancePercent = $row['total_days'] > 0 ? 
                                                            round(($row['present_days'] / $row['total_days']) * 100, 2) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                                        <td><?php echo $row['total_days']; ?></td>
                                                        <td><span class="badge bg-success"><?php echo $row['present_days']; ?></span></td>
                                                        <td><span class="badge bg-danger"><?php echo $row['absent_days']; ?></span></td>
                                                        <td><span class="badge bg-warning"><?php echo $row['late_days']; ?></span></td>
                                                        <td>
                                                            <strong class="<?php echo $attendancePercent >= 75 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $attendancePercent; ?>%
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
function exportToExcel() {
    const table = document.querySelector('table');
    if (!table) {
        Swal.fire({
            icon: 'error',
            title: 'No Data',
            text: 'Please generate a report first.'
        });
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText);
        }
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'attendance_report_' + new Date().getTime() + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

