<?php
/**
 * Student Reports
 * 
 * Various student-related reports
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Student Reports';

// Get current user
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

// Get teacher record if user is a teacher
$teacher = null;
$teacherId = null;
$assignedClassIds = [];

if ($isTeacher && !$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if ($teacher) {
        $teacherId = $teacher['id'];
        
        // Get assigned classes for this teacher
        $currentSession = getCurrentSession();
        if ($currentSession) {
            $assignedClassesSql = "SELECT DISTINCT cs.class_id 
                                  FROM class_subjects cs 
                                  WHERE cs.teacher_id = ? AND cs.session_id = ?";
            $assignedStmt = executeQuery($assignedClassesSql, 'ii', [$teacherId, $currentSession['id']]);
            $assignedClasses = fetchAll($assignedStmt);
            $assignedClassIds = array_column($assignedClasses, 'class_id');
        }
    }
}

// Get report parameters
$reportType = $_GET['report_type'] ?? 'class_wise';
$classId = $_GET['class_id'] ?? '';
$sectionId = $_GET['section_id'] ?? '';
$status = $_GET['status'] ?? 'Active';

// Get current session
$currentSession = getCurrentSession();

// Get classes for filter - restricted for teachers (excluding graduated classes)
if ($isTeacher && !$isSuperAdmin && !empty($assignedClassIds)) {
    $placeholders = implode(',', array_fill(0, count($assignedClassIds), '?'));
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    AND id IN ($placeholders) 
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql, str_repeat('i', count($assignedClassIds)), $assignedClassIds));
} else if ($isTeacher && !$isSuperAdmin && empty($assignedClassIds)) {
    // Teacher has no assigned classes
    $classes = [];
} else {
    // Admin/Super Admin see all classes (excluding graduated)
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql));
}

// Initialize report data
$reportData = [];
$reportTitle = '';

switch ($reportType) {
    case 'class_wise':
        $reportTitle = 'Class-wise Student Report';
        
        $sql = "SELECT c.class_name, c.class_code,
                COUNT(s.id) as total_students,
                SUM(CASE WHEN s.gender = 'Male' THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN s.gender = 'Female' THEN 1 ELSE 0 END) as female_count,
                SUM(CASE WHEN s.status = 'Active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN s.is_hostel = 1 THEN 1 ELSE 0 END) as hostel_count,
                SUM(CASE WHEN s.is_transport = 1 THEN 1 ELSE 0 END) as transport_count
                FROM classes c
                LEFT JOIN students s ON c.id = s.current_class_id
                WHERE c.is_active = 1";
        
        // Filter by assigned classes for teachers
        if ($isTeacher && !$isSuperAdmin && !empty($assignedClassIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedClassIds), '?'));
            $sql .= " AND c.id IN ($placeholders)";
            $params = $assignedClassIds;
            $types = str_repeat('i', count($assignedClassIds));
            $sql .= " GROUP BY c.id, c.class_name, c.class_code ORDER BY c.class_order";
            $reportData = fetchAll(executeQuery($sql, $types, $params));
        } else if ($isTeacher && !$isSuperAdmin && empty($assignedClassIds)) {
            // Teacher has no assigned classes
            $reportData = [];
        } else {
            // Admin/Super Admin see all classes
            $sql .= " GROUP BY c.id, c.class_name, c.class_code ORDER BY c.class_order";
            $reportData = fetchAll(executeQuery($sql));
        }
        break;
    
    case 'detailed':
        $reportTitle = 'Detailed Student Report';
        
        $sql = "SELECT s.student_id, s.admission_no, s.first_name, s.last_name, s.gender,
                s.date_of_birth, s.phone, s.email, s.status,
                c.class_name, sec.section_name, b.branch_name,
                p.first_name as parent_first_name, p.last_name as parent_last_name, p.phone as parent_phone
                FROM students s
                LEFT JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                LEFT JOIN branches b ON s.branch_id = b.id
                LEFT JOIN student_parents sp ON s.id = sp.student_id AND sp.is_primary = 1
                LEFT JOIN parents p ON sp.parent_id = p.id
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Filter by assigned classes for teachers
        if ($isTeacher && !$isSuperAdmin && !empty($assignedClassIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedClassIds), '?'));
            $sql .= " AND s.current_class_id IN ($placeholders)";
            $params = array_merge($params, $assignedClassIds);
            $types .= str_repeat('i', count($assignedClassIds));
        } else if ($isTeacher && !$isSuperAdmin && empty($assignedClassIds)) {
            // Teacher has no assigned classes - return empty
            $reportData = [];
            break;
        }
        
        if (!empty($classId)) {
            // Verify teacher can access this class
            if ($isTeacher && !$isSuperAdmin && !in_array($classId, $assignedClassIds)) {
                $_SESSION['error'] = 'You do not have access to this class.';
                $reportData = [];
                break;
            }
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
            $types .= 'i';
        }
        
        if (!empty($sectionId)) {
            $sql .= " AND s.current_section_id = ?";
            $params[] = $sectionId;
            $types .= 'i';
        }
        
        if (!empty($status)) {
            $sql .= " AND s.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        // Branch filter
        if (!hasRole(['Super Admin'])) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $currentUser['branch_id'];
            $types .= 'i';
        }
        
        $sql .= " ORDER BY c.class_name, s.first_name";
        
        $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
        $reportData = fetchAll($stmt);
        break;
    
    case 'attendance_summary':
        $reportTitle = 'Student Attendance Summary';
        
        $sql = "SELECT s.student_id, s.first_name, s.last_name, c.class_name,
                COUNT(sa.id) as total_marked_days,
                SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN sa.status = 'Leave' THEN 1 ELSE 0 END) as leave_days,
                ROUND((SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) as attendance_percentage
                FROM students s
                LEFT JOIN student_attendance sa ON s.id = sa.student_id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.status = 'Active'";
        
        $params = [];
        $types = '';
        
        // Filter by assigned classes for teachers
        if ($isTeacher && !$isSuperAdmin && !empty($assignedClassIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedClassIds), '?'));
            $sql .= " AND s.current_class_id IN ($placeholders)";
            $params = $assignedClassIds;
            $types = str_repeat('i', count($assignedClassIds));
        } else if ($isTeacher && !$isSuperAdmin && empty($assignedClassIds)) {
            // Teacher has no assigned classes - return empty
            $reportData = [];
            break;
        }
        
        if (!empty($classId)) {
            // Verify teacher can access this class
            if ($isTeacher && !$isSuperAdmin && !in_array($classId, $assignedClassIds)) {
                $_SESSION['error'] = 'You do not have access to this class.';
                $reportData = [];
                break;
            }
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY s.id
                 HAVING total_marked_days > 0
                 ORDER BY attendance_percentage DESC";
        
        $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
        $reportData = fetchAll($stmt);
        break;
    
    case 'fee_status':
        $reportTitle = 'Student Fee Status Report';
        
        $sql = "SELECT s.student_id, s.first_name, s.last_name, c.class_name,
                COUNT(fi.id) as total_invoices,
                COALESCE(SUM(fi.net_amount), 0) as total_amount,
                COALESCE(SUM(fi.paid_amount), 0) as paid_amount,
                COALESCE(SUM(fi.due_amount), 0) as due_amount,
                CASE 
                    WHEN COALESCE(SUM(fi.due_amount), 0) = 0 THEN 'Paid'
                    WHEN COALESCE(SUM(fi.paid_amount), 0) = 0 THEN 'Unpaid'
                    ELSE 'Partially Paid'
                END as payment_status
                FROM students s
                LEFT JOIN fee_invoices fi ON s.id = fi.student_id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.status = 'Active'";
        
        $params = [];
        $types = '';
        
        // Filter by assigned classes for teachers
        if ($isTeacher && !$isSuperAdmin && !empty($assignedClassIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedClassIds), '?'));
            $sql .= " AND s.current_class_id IN ($placeholders)";
            $params = $assignedClassIds;
            $types = str_repeat('i', count($assignedClassIds));
        } else if ($isTeacher && !$isSuperAdmin && empty($assignedClassIds)) {
            // Teacher has no assigned classes - return empty
            $reportData = [];
            break;
        }
        
        if (!empty($classId)) {
            // Verify teacher can access this class
            if ($isTeacher && !$isSuperAdmin && !in_array($classId, $assignedClassIds)) {
                $_SESSION['error'] = 'You do not have access to this class.';
                $reportData = [];
                break;
            }
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
            $types .= 'i';
        }
        
        $sql .= " GROUP BY s.id
                 ORDER BY due_amount DESC";
        
        $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
        $reportData = fetchAll($stmt);
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
                            <button onclick="exportTableToExcel('reportTable', 'student_report')" class="btn btn-success ms-2">
                                <i class="ri-file-excel-line"></i> Export Excel
                            </button>
                        </div>
                        <h4 class="page-title">Student Reports</h4>
                    </div>
                </div>
            </div>

            <!-- Report Type & Filter Card -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Report Type</label>
                                    <select class="form-select" name="report_type" onchange="this.form.submit()">
                                        <option value="class_wise" <?php echo ($reportType == 'class_wise') ? 'selected' : ''; ?>>Class-wise Summary</option>
                                        <option value="detailed" <?php echo ($reportType == 'detailed') ? 'selected' : ''; ?>>Detailed Student List</option>
                                        <option value="attendance_summary" <?php echo ($reportType == 'attendance_summary') ? 'selected' : ''; ?>>Attendance Summary</option>
                                        <option value="fee_status" <?php echo ($reportType == 'fee_status') ? 'selected' : ''; ?>>Fee Status Report</option>
                                    </select>
                                </div>
                                
                                <?php if (in_array($reportType, ['detailed', 'attendance_summary', 'fee_status'])): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Filter by Class</label>
                                    <select class="form-select" name="class_id">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-refresh-line"></i> Generate Report
                                    </button>
                                </div>
                                <?php endif; ?>
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
                                <p class="text-muted">Generated on: <?php echo date('d F Y, h:i A'); ?></p>
                                <?php if ($currentSession): ?>
                                <p class="text-muted">Academic Session: <strong><?php echo htmlspecialchars($currentSession['session_name']); ?></strong></p>
                                <?php endif; ?>
                                <?php if ($isTeacher && !$isSuperAdmin && !empty($assignedClassIds)): ?>
                                <p class="text-info"><small><i class="ri-information-line"></i> Showing data for your assigned classes only</small></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($isTeacher && !$isSuperAdmin && empty($assignedClassIds)): ?>
                            <div class="alert alert-warning text-center">
                                <i class="ri-error-warning-line font-24"></i>
                                <h5 class="mt-2">No Classes Assigned</h5>
                                <p class="mb-0">You don't have any classes or subjects assigned to you yet. Please contact administrator to get assigned to classes.</p>
                            </div>
                            <?php elseif (!empty($reportData)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="reportTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <?php if ($reportType == 'class_wise'): ?>
                                                <th>Class Name</th>
                                                <th>Class Code</th>
                                                <th>Total Students</th>
                                                <th>Male</th>
                                                <th>Female</th>
                                                <th>Active</th>
                                                <th>Hostel</th>
                                                <th>Transport</th>
                                            <?php elseif ($reportType == 'detailed'): ?>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Phone</th>
                                                <th>Parent Name</th>
                                                <th>Parent Phone</th>
                                                <th>Status</th>
                                            <?php elseif ($reportType == 'attendance_summary'): ?>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Class</th>
                                                <th>Total Days</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Attendance %</th>
                                            <?php elseif ($reportType == 'fee_status'): ?>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Class</th>
                                                <th>Invoices</th>
                                                <th>Total Amount</th>
                                                <th>Paid</th>
                                                <th>Due</th>
                                                <th>Status</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <?php if ($reportType == 'class_wise'): ?>
                                                <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['class_code']); ?></td>
                                                <td><strong><?php echo $row['total_students']; ?></strong></td>
                                                <td><?php echo $row['male_count']; ?></td>
                                                <td><?php echo $row['female_count']; ?></td>
                                                <td><?php echo $row['active_count']; ?></td>
                                                <td><?php echo $row['hostel_count']; ?></td>
                                                <td><?php echo $row['transport_count']; ?></td>
                                            <?php elseif ($reportType == 'detailed'): ?>
                                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                <td><?php echo htmlspecialchars($row['class_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['section_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars(($row['parent_first_name'] ?? '') . ' ' . ($row['parent_last_name'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars($row['parent_phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($row['status'] == 'Active') ? 'success' : 'warning'; ?>">
                                                        <?php echo $row['status']; ?>
                                                    </span>
                                                </td>
                                            <?php elseif ($reportType == 'attendance_summary'): ?>
                                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                <td><?php echo $row['total_marked_days']; ?></td>
                                                <td class="text-success"><strong><?php echo $row['present_days']; ?></strong></td>
                                                <td class="text-danger"><strong><?php echo $row['absent_days']; ?></strong></td>
                                                <td class="text-warning"><?php echo $row['late_days']; ?></td>
                                                <td>
                                                    <?php
                                                    $percentage = $row['attendance_percentage'] ?? 0;
                                                    $badgeClass = $percentage >= 90 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>">
                                                        <?php echo number_format($percentage, 2); ?>%
                                                    </span>
                                                </td>
                                            <?php elseif ($reportType == 'fee_status'): ?>
                                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                <td><?php echo $row['total_invoices']; ?></td>
                                                <td><?php echo formatCurrency($row['total_amount']); ?></td>
                                                <td class="text-success"><strong><?php echo formatCurrency($row['paid_amount']); ?></strong></td>
                                                <td class="text-danger"><strong><?php echo formatCurrency($row['due_amount']); ?></strong></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'secondary';
                                                    switch($row['payment_status']) {
                                                        case 'Paid': $statusClass = 'success'; break;
                                                        case 'Unpaid': $statusClass = 'danger'; break;
                                                        case 'Partially Paid': $statusClass = 'warning'; break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                                        <?php echo $row['payment_status']; ?>
                                                    </span>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    
                                    <?php if ($reportType == 'class_wise'): ?>
                                    <tfoot class="table-warning">
                                        <tr>
                                            <th colspan="2">TOTAL</th>
                                            <th><?php echo array_sum(array_column($reportData, 'total_students')); ?></th>
                                            <th><?php echo array_sum(array_column($reportData, 'male_count')); ?></th>
                                            <th><?php echo array_sum(array_column($reportData, 'female_count')); ?></th>
                                            <th><?php echo array_sum(array_column($reportData, 'active_count')); ?></th>
                                            <th><?php echo array_sum(array_column($reportData, 'hostel_count')); ?></th>
                                            <th><?php echo array_sum(array_column($reportData, 'transport_count')); ?></th>
                                        </tr>
                                    </tfoot>
                                    <?php elseif ($reportType == 'fee_status'): ?>
                                    <tfoot class="table-warning">
                                        <tr>
                                            <th colspan="4">TOTAL</th>
                                            <th><?php echo formatCurrency(array_sum(array_column($reportData, 'total_amount'))); ?></th>
                                            <th><?php echo formatCurrency(array_sum(array_column($reportData, 'paid_amount'))); ?></th>
                                            <th><?php echo formatCurrency(array_sum(array_column($reportData, 'due_amount'))); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line font-24"></i>
                                <h5 class="mt-2">No data found</h5>
                                <p class="mb-0">Try adjusting the filters or add some data first.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

