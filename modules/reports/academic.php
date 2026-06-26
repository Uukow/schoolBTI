<?php
/**
 * Academic Reports
 * 
 * Generate academic performance reports
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Academic Reports';

// Get current user
$currentUser = getCurrentUser();
$currentSession = getCurrentSession();

// Get filters
$reportType = $_GET['type'] ?? 'performance';
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';
$examFilter = $_GET['exam_id'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Get classes
$classesSql = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get subjects
$subjectsSql = "SELECT * FROM subjects ORDER BY subject_name";
$subjects = fetchAll(executeQuery($subjectsSql));

// Get exams
$examsSql = "SELECT * FROM exams WHERE session_id = ? ORDER BY start_date DESC";
$exams = fetchAll(executeQuery($examsSql, 'i', [$currentSession['id']]));

// Initialize report data
$reportData = [];
$reportTitle = 'Academic Report';

switch ($reportType) {
    case 'performance':
        // Class performance summary
        $sql = "SELECT c.class_name,
                COUNT(DISTINCT sm.student_id) as students_count,
                AVG(sm.marks_obtained) as avg_marks,
                MAX(sm.marks_obtained) as max_marks,
                MIN(sm.marks_obtained) as min_marks,
                SUM(CASE WHEN sm.marks_obtained >= 50 THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN sm.marks_obtained < 50 THEN 1 ELSE 0 END) as failed
                FROM classes c
                LEFT JOIN students s ON c.id = s.current_class_id
                LEFT JOIN student_marks sm ON s.id = sm.student_id
                LEFT JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                LEFT JOIN exams e ON es.exam_id = e.id
                WHERE e.session_id = ?";
        
        $params = [$currentSession['id']];
        $types = 'i';
        
        if (!empty($classFilter)) {
            $sql .= " AND c.id = ?";
            $params[] = $classFilter;
            $types .= 'i';
        }
        
        if (!empty($examFilter)) {
            $sql .= " AND e.id = ?";
            $params[] = $examFilter;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY c.id, c.class_name ORDER BY c.class_order";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Class Performance Summary';
        break;
    
    case 'subject':
        // Subject-wise performance
        $sql = "SELECT sub.subject_name,
                COUNT(DISTINCT sm.student_id) as students_count,
                AVG(sm.marks_obtained) as avg_marks,
                MAX(sm.marks_obtained) as max_marks,
                MIN(sm.marks_obtained) as min_marks
                FROM subjects sub
                LEFT JOIN exam_schedule es ON sub.id = es.subject_id
                LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
                LEFT JOIN exams e ON es.exam_id = e.id
                WHERE e.session_id = ?";
        
        $params = [$currentSession['id']];
        $types = 'i';
        
        if (!empty($subjectFilter)) {
            $sql .= " AND sub.id = ?";
            $params[] = $subjectFilter;
            $types .= 'i';
        }
        
        if (!empty($examFilter)) {
            $sql .= " AND e.id = ?";
            $params[] = $examFilter;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY sub.id, sub.subject_name ORDER BY sub.subject_name";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Subject-wise Performance';
        break;
    
    case 'top_students':
        // Top performing students
        $sql = "SELECT s.student_id, s.first_name, s.last_name, c.class_name,
                AVG(sm.marks_obtained) as avg_marks,
                COUNT(sm.id) as exams_count
                FROM students s
                INNER JOIN student_marks sm ON s.id = sm.student_id
                LEFT JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                LEFT JOIN exams e ON es.exam_id = e.id
                WHERE e.session_id = ?";
        
        $params = [$currentSession['id']];
        $types = 'i';
        
        if (!empty($classFilter)) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classFilter;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY s.id, s.student_id, s.first_name, s.last_name, c.class_name
                  ORDER BY avg_marks DESC LIMIT 20";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Top Performing Students';
        break;
    
    case 'exam_results':
        // Exam results summary
        $sql = "SELECT e.exam_name, e.start_date as exam_date, et.exam_name as exam_type,
                COUNT(DISTINCT sm.student_id) as students_count,
                AVG(sm.marks_obtained) as avg_marks,
                MAX(sm.marks_obtained) as max_marks,
                MIN(sm.marks_obtained) as min_marks
                FROM exams e
                LEFT JOIN exam_types et ON e.exam_type_id = et.id
                LEFT JOIN exam_schedule es ON e.id = es.exam_id
                LEFT JOIN student_marks sm ON es.id = sm.exam_schedule_id
                WHERE e.session_id = ?";
        
        $params = [$currentSession['id']];
        $types = 'i';
        
        if (!empty($examFilter)) {
            $sql .= " AND e.id = ?";
            $params[] = $examFilter;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY e.id, e.exam_name, e.start_date, et.exam_name
                  ORDER BY e.start_date DESC";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Exam Results Summary';
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
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportTableToExcel('reportTable', 'academic_report')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export Excel
                            </button>
                        </div>
                        <h4 class="page-title">Academic Reports</h4>
                    </div>
                </div>
            </div>

            <!-- Report Type Tabs -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'performance' ? 'active' : ''; ?>" 
                                       href="?type=performance">
                                        <i class="ri-bar-chart-line"></i> Class Performance
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'subject' ? 'active' : ''; ?>" 
                                       href="?type=subject">
                                        <i class="ri-book-open-line"></i> Subject-wise
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'top_students' ? 'active' : ''; ?>" 
                                       href="?type=top_students">
                                        <i class="ri-trophy-line"></i> Top Students
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'exam_results' ? 'active' : ''; ?>" 
                                       href="?type=exam_results">
                                        <i class="ri-file-list-3-line"></i> Exam Results
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <input type="hidden" name="type" value="<?php echo $reportType; ?>">
                                <?php if ($reportType == 'performance' || $reportType == 'top_students'): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Class</label>
                                    <select class="form-select" name="class_id">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($reportType == 'subject'): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Subject</label>
                                    <select class="form-select" name="subject_id">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Exam</label>
                                    <select class="form-select" name="exam_id">
                                        <option value="">All Exams</option>
                                        <?php foreach ($exams as $exam): ?>
                                            <option value="<?php echo $exam['id']; ?>" <?php echo ($examFilter == $exam['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($exam['exam_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Generate Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Data -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo $reportTitle; ?></h4>
                            
                            <?php if (!empty($reportData)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="reportTable">
                                    <thead class="table-light">
                                        <?php if ($reportType == 'performance'): ?>
                                        <tr>
                                            <th>Class</th>
                                            <th>Students</th>
                                            <th>Average Marks</th>
                                            <th>Max Marks</th>
                                            <th>Min Marks</th>
                                            <th>Passed</th>
                                            <th>Failed</th>
                                            <th>Pass Rate</th>
                                        </tr>
                                        <?php elseif ($reportType == 'subject'): ?>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Students</th>
                                            <th>Average Marks</th>
                                            <th>Max Marks</th>
                                            <th>Min Marks</th>
                                        </tr>
                                        <?php elseif ($reportType == 'top_students'): ?>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Average Marks</th>
                                            <th>Exams</th>
                                        </tr>
                                        <?php elseif ($reportType == 'exam_results'): ?>
                                        <tr>
                                            <th>Exam</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Students</th>
                                            <th>Average Marks</th>
                                            <th>Max Marks</th>
                                            <th>Min Marks</th>
                                        </tr>
                                        <?php endif; ?>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($reportData as $row): 
                                        ?>
                                        <tr>
                                            <?php if ($reportType == 'performance'): ?>
                                                <td><strong><?php echo htmlspecialchars($row['class_name']); ?></strong></td>
                                                <td><?php echo $row['students_count'] ?? 0; ?></td>
                                                <td><?php echo number_format($row['avg_marks'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($row['max_marks'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($row['min_marks'] ?? 0, 2); ?></td>
                                                <td><span class="badge bg-success"><?php echo $row['passed'] ?? 0; ?></span></td>
                                                <td><span class="badge bg-danger"><?php echo $row['failed'] ?? 0; ?></span></td>
                                                <td>
                                                    <?php 
                                                    $total = ($row['passed'] ?? 0) + ($row['failed'] ?? 0);
                                                    $passRate = $total > 0 ? (($row['passed'] ?? 0) / $total) * 100 : 0;
                                                    ?>
                                                    <strong><?php echo number_format($passRate, 2); ?>%</strong>
                                                </td>
                                            <?php elseif ($reportType == 'subject'): ?>
                                                <td><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
                                                <td><?php echo $row['students_count'] ?? 0; ?></td>
                                                <td><?php echo number_format($row['avg_marks'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($row['max_marks'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($row['min_marks'] ?? 0, 2); ?></td>
                                            <?php elseif ($reportType == 'top_students'): ?>
                                                <td><strong>#<?php echo $rank++; ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                <td><strong><?php echo number_format($row['avg_marks'] ?? 0, 2); ?></strong></td>
                                                <td><?php echo $row['exams_count'] ?? 0; ?></td>
                                            <?php elseif ($reportType == 'exam_results'): ?>
                                                <td><strong><?php echo htmlspecialchars($row['exam_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['exam_type']); ?></td>
                                                <td><?php echo formatDate($row['exam_date']); ?></td>
                                                <td><?php echo $row['students_count'] ?? 0; ?></td>
                                                <td><?php echo number_format($row['avg_marks'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($row['max_marks'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($row['min_marks'] ?? 0, 2); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line font-24"></i>
                                <h5 class="mt-2">No Data Available</h5>
                                <p class="mb-0">No data found for the selected criteria.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

