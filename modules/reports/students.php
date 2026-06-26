<?php
/**
 * Student Reports
 * 
 * Generate various student reports
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Student Reports';

// Get current user
$currentUser = getCurrentUser();

// Get report type
$reportType = $_GET['type'] ?? 'summary';

// Get classes for filter
$classesSql = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Initialize data
$reportData = [];
$reportTitle = 'Student Report';

switch ($reportType) {
    case 'summary':
        // Student summary by class
        $sql = "SELECT c.class_name, 
                COUNT(s.id) as total_students,
                SUM(CASE WHEN s.gender = 'Male' THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN s.gender = 'Female' THEN 1 ELSE 0 END) as female_count,
                SUM(CASE WHEN s.status = 'Active' THEN 1 ELSE 0 END) as active_count
                FROM classes c
                LEFT JOIN students s ON c.id = s.current_class_id
                GROUP BY c.id, c.class_name
                ORDER BY c.class_order";
        
        $reportData = fetchAll(executeQuery($sql));
        $reportTitle = 'Student Summary by Class';
        break;
    
    case 'attendance':
        // Attendance summary
        $sql = "SELECT s.student_id, s.first_name, s.last_name, c.class_name,
                COUNT(sa.id) as total_days,
                SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
                ROUND((SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) / COUNT(sa.id)) * 100, 2) as attendance_percentage
                FROM students s
                LEFT JOIN student_attendance sa ON s.id = sa.student_id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.status = 'Active'
                GROUP BY s.id
                HAVING total_days > 0
                ORDER BY c.class_name, s.first_name";
        
        $reportData = fetchAll(executeQuery($sql));
        $reportTitle = 'Student Attendance Report';
        break;
    
    case 'demographics':
        // Demographics report
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_hostel = 1 THEN 1 ELSE 0 END) as hostel,
                SUM(CASE WHEN is_transport = 1 THEN 1 ELSE 0 END) as transport,
                ROUND(AVG(YEAR(CURDATE()) - YEAR(date_of_birth)), 1) as avg_age
                FROM students";
        
        $reportData = [fetchOne(executeQuery($sql))];
        $reportTitle = 'Student Demographics Report';
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
                        <div class="page-title-right">
                            <button onclick="window.print()" class="btn btn-secondary no-print">
                                <i class="ri-printer-line"></i> Print Report
                            </button>
                            <button onclick="exportTableToExcel('reportTable', 'student_report')" class="btn btn-success no-print ms-2">
                                <i class="ri-file-excel-line"></i> Export to Excel
                            </button>
                        </div>
                        <h4 class="page-title">Student Reports</h4>
                    </div>
                </div>
            </div>

            <!-- Report Type Selection -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Select Report Type</label>
                                    <select class="form-select" onchange="window.location.href='students.php?type='+this.value">
                                        <option value="summary" <?php echo ($reportType == 'summary') ? 'selected' : ''; ?>>Class-wise Summary</option>
                                        <option value="attendance" <?php echo ($reportType == 'attendance') ? 'selected' : ''; ?>>Attendance Report</option>
                                        <option value="demographics" <?php echo ($reportType == 'demographics') ? 'selected' : ''; ?>>Demographics Report</option>
                                    </select>
                                </div>
                            </div>
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
                                <p class="text-muted">Generated on: <?php echo date('d F Y'); ?></p>
                            </div>
                            
                            <div class="table-responsive">
                                <?php if ($reportType == 'summary'): ?>
                                <table class="table table-bordered table-hover" id="reportTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Total Students</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Active</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalStudents = 0;
                                        $totalMale = 0;
                                        $totalFemale = 0;
                                        $totalActive = 0;
                                        
                                        foreach ($reportData as $row): 
                                            $totalStudents += $row['total_students'];
                                            $totalMale += $row['male_count'];
                                            $totalFemale += $row['female_count'];
                                            $totalActive += $row['active_count'];
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                            <td><strong><?php echo $row['total_students']; ?></strong></td>
                                            <td><?php echo $row['male_count']; ?></td>
                                            <td><?php echo $row['female_count']; ?></td>
                                            <td><?php echo $row['active_count']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-warning">
                                            <td><strong>TOTAL</strong></td>
                                            <td><strong><?php echo $totalStudents; ?></strong></td>
                                            <td><strong><?php echo $totalMale; ?></strong></td>
                                            <td><strong><?php echo $totalFemale; ?></strong></td>
                                            <td><strong><?php echo $totalActive; ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <?php elseif ($reportType == 'attendance'): ?>
                                <table class="table table-bordered table-hover datatable-export" id="reportTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Total Days</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                            <th>Attendance %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                            <td><?php echo $row['total_days']; ?></td>
                                            <td class="text-success"><strong><?php echo $row['present_days']; ?></strong></td>
                                            <td class="text-danger"><strong><?php echo $row['absent_days']; ?></strong></td>
                                            <td>
                                                <?php
                                                $percentage = $row['attendance_percentage'];
                                                $badgeClass = $percentage >= 90 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo $percentage; ?>%
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <?php elseif ($reportType == 'demographics'): ?>
                                <table class="table table-bordered" id="reportTable">
                                    <tbody>
                                        <?php $data = $reportData[0]; ?>
                                        <tr>
                                            <th width="40%">Total Students</th>
                                            <td><h4 class="mb-0"><?php echo $data['total']; ?></h4></td>
                                        </tr>
                                        <tr>
                                            <th>Male Students</th>
                                            <td><span class="badge bg-primary"><?php echo $data['male']; ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Female Students</th>
                                            <td><span class="badge bg-info"><?php echo $data['female']; ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Active Students</th>
                                            <td><span class="badge bg-success"><?php echo $data['active']; ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Hostel Students</th>
                                            <td><?php echo $data['hostel']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Transport Students</th>
                                            <td><?php echo $data['transport']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Average Age</th>
                                            <td><?php echo $data['avg_age']; ?> years</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

