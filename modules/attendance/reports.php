<?php
/**
 * Attendance Reports
 * 
 * View and export attendance reports
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Attendance Reports';

// Get parameters
$reportType = $_GET['type'] ?? 'student_summary';
$classId = $_GET['class_id'] ?? '';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Initialize report data
$reportData = [];
$reportTitle = '';

switch ($reportType) {
    case 'student_summary':
        $reportTitle = 'Student Attendance Summary Report';
        
        $sql = "SELECT s.student_id, s.first_name, s.last_name, c.class_name, sec.section_name,
                COUNT(sa.id) as total_days,
                SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN sa.status = 'Leave' THEN 1 ELSE 0 END) as leave_days,
                ROUND((SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) as percentage
                FROM students s
                LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date BETWEEN ? AND ?
                LEFT JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                WHERE s.status = 'Active'";
        
        $params = [$startDate, $endDate];
        $types = 'ss';
        
        if (!empty($classId)) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY s.id HAVING total_days > 0 ORDER BY c.class_name, s.first_name";
        
        $stmt = executeQuery($sql, $types, $params);
        $reportData = fetchAll($stmt);
        break;
    
    case 'staff_summary':
        $reportTitle = 'Staff Attendance Summary Report';
        
        $sql = "SELECT s.staff_id, s.first_name, s.last_name, s.designation, b.branch_name,
                COUNT(sa.id) as total_days,
                SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN sa.status = 'Leave' THEN 1 ELSE 0 END) as leave_days,
                ROUND((SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) as percentage
                FROM staff s
                LEFT JOIN staff_attendance sa ON s.id = sa.staff_id AND sa.attendance_date BETWEEN ? AND ?
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE s.status = 'Active'
                GROUP BY s.id
                HAVING total_days > 0
                ORDER BY s.first_name";
        
        $stmt = executeQuery($sql, 'ss', [$startDate, $endDate]);
        $reportData = fetchAll($stmt);
        break;
    
    case 'daily':
        $reportTitle = 'Daily Attendance Report';
        $selectedDate = $_GET['specific_date'] ?? date('Y-m-d');
        
        $sql = "SELECT 
                COUNT(*) as total_students,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late
                FROM student_attendance
                WHERE attendance_date = ?";
        
        $stmt = executeQuery($sql, 's', [$selectedDate]);
        $reportData = [fetchOne($stmt)];
        break;
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
                        <div class="page-title-right no-print">
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportTableToExcel('reportTable', 'attendance_report')" class="btn btn-success ms-2">
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
                                    <label class="form-label">Report Type</label>
                                    <select class="form-select" name="type">
                                        <option value="student_summary" <?php echo ($reportType == 'student_summary') ? 'selected' : ''; ?>>Student Summary</option>
                                        <option value="staff_summary" <?php echo ($reportType == 'staff_summary') ? 'selected' : ''; ?>>Staff Summary</option>
                                        <option value="daily" <?php echo ($reportType == 'daily') ? 'selected' : ''; ?>>Daily Report</option>
                                    </select>
                                </div>
                                
                                <?php if ($reportType == 'student_summary'): ?>
                                <div class="col-md-2">
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
                                
                                <?php if ($reportType != 'daily'): ?>
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-bar-chart-line"></i> Generate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Display -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h3><?php echo $reportTitle; ?></h3>
                                <p class="text-muted">
                                    Period: <?php echo formatDate($startDate); ?> to <?php echo formatDate($endDate); ?>
                                </p>
                            </div>
                            
                            <?php if (!empty($reportData)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export" id="reportTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <?php if ($reportType == 'student_summary'): ?>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Total Days</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Leave</th>
                                                <th>Attendance %</th>
                                            <?php elseif ($reportType == 'staff_summary'): ?>
                                                <th>Staff ID</th>
                                                <th>Name</th>
                                                <th>Designation</th>
                                                <th>Branch</th>
                                                <th>Total Days</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Leave</th>
                                                <th>Attendance %</th>
                                            <?php elseif ($reportType == 'daily'): ?>
                                                <th>Total Students</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Attendance %</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <?php if ($reportType == 'student_summary'): ?>
                                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['section_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo $row['total_days']; ?></td>
                                                <td class="text-success"><strong><?php echo $row['present']; ?></strong></td>
                                                <td class="text-danger"><strong><?php echo $row['absent']; ?></strong></td>
                                                <td class="text-warning"><?php echo $row['late']; ?></td>
                                                <td class="text-info"><?php echo $row['leave_days']; ?></td>
                                                <td>
                                                    <?php
                                                    $percentage = $row['percentage'] ?? 0;
                                                    $badgeClass = $percentage >= 90 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>">
                                                        <?php echo number_format($percentage, 2); ?>%
                                                    </span>
                                                </td>
                                            <?php elseif ($reportType == 'staff_summary'): ?>
                                                <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                                <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                                <td><?php echo $row['total_days']; ?></td>
                                                <td class="text-success"><strong><?php echo $row['present']; ?></strong></td>
                                                <td class="text-danger"><strong><?php echo $row['absent']; ?></strong></td>
                                                <td class="text-warning"><?php echo $row['late']; ?></td>
                                                <td class="text-info"><?php echo $row['leave_days']; ?></td>
                                                <td>
                                                    <?php
                                                    $percentage = $row['percentage'] ?? 0;
                                                    $badgeClass = $percentage >= 95 ? 'success' : ($percentage >= 85 ? 'warning' : 'danger');
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>">
                                                        <?php echo number_format($percentage, 2); ?>%
                                                    </span>
                                                </td>
                                            <?php elseif ($reportType == 'daily'): ?>
                                                <td><?php echo $row['total_students']; ?></td>
                                                <td class="text-success"><strong><?php echo $row['present']; ?></strong></td>
                                                <td class="text-danger"><strong><?php echo $row['absent']; ?></strong></td>
                                                <td class="text-warning"><?php echo $row['late']; ?></td>
                                                <td>
                                                    <?php
                                                    $percentage = ($row['total_students'] > 0) ? 
                                                                 round(($row['present'] / $row['total_students']) * 100, 2) : 0;
                                                    ?>
                                                    <span class="badge bg-info"><?php echo $percentage; ?>%</span>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line"></i> No attendance data found for selected period.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

