<?php
/**
 * Student Portal Dashboard
 * 
 * Student-specific dashboard with isolated data view
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Student', 'Super Admin'], APP_URL . 'dashboard.php');

$pageTitle = 'Student Dashboard';

// Get current user and student record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$student = null;
$studentId = null;

if ($isSuperAdmin) {
    // Super Admin can view all data - no student filtering
    $studentId = null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found. Please contact administrator to link your user account to a student record.';
        // Don't redirect - show error on student dashboard
        $studentId = null;
    } else {
        $studentId = $student['id'];
        // Note: Attendance queries work for all students regardless of status (Active, Graduated, etc.)
        // The student_attendance table stores historical data that should always be accessible
    }
}

$currentSession = getCurrentSession();

// Get student statistics
$stats = [];

if ($isSuperAdmin) {
    // Super Admin sees all data
    $sql = "SELECT COUNT(DISTINCT s.id) as count 
            FROM students s
            WHERE s.status = 'Active'";
    $stmt = executeQuery($sql);
    $stats['total_students'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(*) as count 
            FROM student_attendance 
            WHERE attendance_date = CURDATE()";
    $stmt = executeQuery($sql);
    $stats['today_attendance'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(*) as count 
            FROM assignments a
            WHERE a.due_date >= CURDATE() AND a.session_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $stats['upcoming_assignments'] = fetchOne($stmt)['count'] ?? 0;

    $sql = "SELECT COUNT(*) as count 
            FROM exam_schedule es
            WHERE es.exam_date >= CURDATE()";
    $stmt = executeQuery($sql);
    $stats['upcoming_exams'] = fetchOne($stmt)['count'] ?? 0;
} else {
    // Student sees only their data
    if ($student && $studentId) {
        // Get attendance statistics (works for all students including graduated)
        // Historical attendance data is preserved regardless of student status
        $sql = "SELECT 
                COUNT(*) as total_days,
                COALESCE(SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END), 0) as present_days,
                COALESCE(SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END), 0) as absent_days,
                COALESCE(SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END), 0) as late_days
                FROM student_attendance sa
                WHERE sa.student_id = ?";
        $stmt = executeQuery($sql, 'i', [$studentId]);
        $attendanceData = fetchOne($stmt);
        
        // Ensure we have valid data even if query returns null
        $stats['total_days'] = isset($attendanceData['total_days']) ? (int)$attendanceData['total_days'] : 0;
        $stats['present_days'] = isset($attendanceData['present_days']) ? (int)$attendanceData['present_days'] : 0;
        $stats['absent_days'] = isset($attendanceData['absent_days']) ? (int)$attendanceData['absent_days'] : 0;
        $stats['attendance_percentage'] = 0;
        if ($stats['total_days'] > 0) {
            $stats['attendance_percentage'] = round(($stats['present_days'] / $stats['total_days']) * 100, 2);
        }

        // Today's attendance status (works for all students including graduated)
        $sql = "SELECT sa.status 
                FROM student_attendance sa
                WHERE sa.student_id = ? AND sa.attendance_date = CURDATE()";
        $stmt = executeQuery($sql, 'i', [$studentId]);
        $todayAttendance = fetchOne($stmt);
        $stats['today_status'] = $todayAttendance['status'] ?? 'Not Marked';

        // Check if class is graduated
        $classGraduated = false;
        if (isset($student['current_class_id']) && $student['current_class_id']) {
            $classGraduated = isClassGraduated($student['current_class_id']);
        }
        
        // Upcoming assignments (only if class is not graduated)
        if (isset($student['current_class_id']) && $student['current_class_id'] && !$classGraduated) {
            $sql = "SELECT COUNT(*) as count 
                    FROM assignments a
                    INNER JOIN classes c ON a.class_id = c.id
                    WHERE a.class_id = ? AND a.due_date >= CURDATE() AND a.session_id = ?
                    AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
            $stmt = executeQuery($sql, 'ii', [$student['current_class_id'], $currentSession['id']]);
            $stats['upcoming_assignments'] = fetchOne($stmt)['count'] ?? 0;

            // Upcoming exams (only if class is not graduated)
            $sql = "SELECT COUNT(*) as count 
                    FROM exam_schedule es
                    INNER JOIN exams e ON es.exam_id = e.id
                    INNER JOIN classes c ON e.class_id = c.id
                    WHERE e.class_id = ? AND es.exam_date >= CURDATE()
                    AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
            $stmt = executeQuery($sql, 'i', [$student['current_class_id']]);
            $stats['upcoming_exams'] = fetchOne($stmt)['count'] ?? 0;
        } else {
            $stats['upcoming_assignments'] = 0;
            $stats['upcoming_exams'] = 0;
        }

        // Recent marks
        $sql = "SELECT COUNT(*) as count 
                FROM student_marks sm
                INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                WHERE sm.student_id = ?
                ORDER BY es.exam_date DESC
                LIMIT 5";
        $stmt = executeQuery($sql, 'i', [$studentId]);
        $stats['recent_marks_count'] = fetchOne($stmt)['count'] ?? 0;
        
        // Finance statistics
        $sql = "SELECT 
                COUNT(*) as total_invoices,
                COALESCE(SUM(due_amount), 0) as total_due,
                SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue_count
                FROM fee_invoices
                WHERE student_id = ?";
        $stmt = executeQuery($sql, 'i', [$studentId]);
        $financeStats = fetchOne($stmt);
        $stats['total_invoices'] = $financeStats['total_invoices'] ?? 0;
        $stats['total_due'] = $financeStats['total_due'] ?? 0;
        $stats['unpaid_invoices'] = $financeStats['unpaid_count'] ?? 0;
        $stats['overdue_invoices'] = $financeStats['overdue_count'] ?? 0;
    } else {
        // No student record - set default values
        $stats['total_days'] = 0;
        $stats['present_days'] = 0;
        $stats['absent_days'] = 0;
        $stats['attendance_percentage'] = 0;
        $stats['today_status'] = 'Not Marked';
        $stats['upcoming_assignments'] = 0;
        $stats['upcoming_exams'] = 0;
        $stats['recent_marks_count'] = 0;
        $stats['total_invoices'] = 0;
        $stats['total_due'] = 0;
        $stats['unpaid_invoices'] = 0;
        $stats['overdue_invoices'] = 0;
    }
}

// Get today's timetable
if ($isSuperAdmin) {
    $todayClasses = [];
} else {
    if ($student && isset($student['current_class_id']) && isset($student['current_section_id']) && $student['current_class_id'] && $student['current_section_id']) {
        $sql = "SELECT t.*, c.class_name, sec.section_name, s.subject_name, s.subject_code, st.first_name as teacher_first_name, st.last_name as teacher_last_name
                FROM timetable t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN sections sec ON t.section_id = sec.id
                INNER JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN staff st ON t.teacher_id = st.id
                WHERE t.class_id = ? AND t.section_id = ? AND t.session_id = ? 
                AND t.day_of_week = UPPER(DAYNAME(CURDATE()))
                ORDER BY t.start_time";
        $stmt = executeQuery($sql, 'iii', [$student['current_class_id'], $student['current_section_id'], $currentSession['id']]);
        $todayClasses = fetchAll($stmt);
    } else {
        $todayClasses = [];
    }
}

// Recent announcements
$sql = "SELECT * FROM announcements 
        WHERE (target_audience = 'All' OR target_audience = 'Students')
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY created_at DESC LIMIT 5";
$announcements = fetchAll(executeQuery($sql));

// Recent marks (for student)
$recentMarks = [];
if (!$isSuperAdmin && $studentId) {
    $sql = "SELECT sm.*, es.exam_date, s.subject_name, e.exam_name, es.total_marks
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            INNER JOIN subjects s ON es.subject_id = s.id
            WHERE sm.student_id = ?
            ORDER BY es.exam_date DESC
            LIMIT 5";
    $stmt = executeQuery($sql, 'i', [$studentId]);
    $recentMarks = fetchAll($stmt);
}

// Upcoming assignments (for student - only if class is not graduated)
$upcomingAssignments = [];
if (!$isSuperAdmin && $studentId && $student && isset($student['current_class_id']) && $student['current_class_id'] && !$classGraduated) {
    $sql = "SELECT a.*, c.class_name, c.graduation_status, s.subject_name,
            (SELECT id FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as submission_id
            FROM assignments a
            LEFT JOIN classes c ON a.class_id = c.id
            LEFT JOIN subjects s ON a.subject_id = s.id
            WHERE a.class_id = ? AND a.due_date >= CURDATE() AND a.session_id = ?
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            ORDER BY a.due_date ASC
            LIMIT 5";
    $stmt = executeQuery($sql, 'iii', [$studentId, $student['current_class_id'], $currentSession['id']]);
    $upcomingAssignments = fetchAll($stmt);
}

// Get outstanding fees (for student)
$outstandingFees = [];
$totalOutstanding = 0;
if (!$isSuperAdmin && $studentId) {
    // Get outstanding monthly fee assignments
    $feesSql = "SELECT mfa.*, ft.fee_name, ft.fee_code, c.class_name
                FROM monthly_fee_assignments mfa
                INNER JOIN fee_types ft ON mfa.fee_type_id = ft.id
                LEFT JOIN classes c ON mfa.class_id = c.id
                WHERE mfa.student_id = ? AND mfa.due_amount > 0
                ORDER BY mfa.due_date ASC, mfa.month ASC";
    $feesStmt = executeQuery($feesSql, 'i', [$studentId]);
    $monthlyFees = fetchAll($feesStmt);
    
    // Get outstanding invoices
    $invoiceSql = "SELECT i.*, sess.session_name
                   FROM fee_invoices i
                   LEFT JOIN academic_sessions sess ON i.session_id = sess.id
                   WHERE i.student_id = ? AND i.due_amount > 0
                   ORDER BY i.due_date ASC";
    $invoiceStmt = executeQuery($invoiceSql, 'i', [$studentId]);
    $invoices = fetchAll($invoiceStmt);
    
    // Combine and format outstanding fees
    foreach ($monthlyFees as $fee) {
        $outstandingFees[] = [
            'type' => 'monthly',
            'fee_name' => $fee['fee_name'],
            'fee_code' => $fee['fee_code'],
            'month' => $fee['month'],
            'amount' => (float)$fee['due_amount'],
            'due_date' => $fee['due_date'],
            'status' => $fee['status'],
            'id' => $fee['id']
        ];
        $totalOutstanding += (float)$fee['due_amount'];
    }
    
    foreach ($invoices as $invoice) {
        $outstandingFees[] = [
            'type' => 'invoice',
            'fee_name' => 'Invoice Fees',
            'fee_code' => 'INV',
            'month' => date('Y-m', strtotime($invoice['created_at'])),
            'amount' => (float)$invoice['due_amount'],
            'due_date' => $invoice['due_date'],
            'status' => $invoice['status'],
            'id' => $invoice['id'],
            'invoice_no' => $invoice['invoice_no']
        ];
        $totalOutstanding += (float)$invoice['due_amount'];
    }
    
    // Sort by due date
    usort($outstandingFees, function($a, $b) {
        if ($a['due_date'] == $b['due_date']) return 0;
        if (empty($a['due_date'])) return 1;
        if (empty($b['due_date'])) return -1;
        return strtotime($a['due_date']) - strtotime($b['due_date']);
    });
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
                        <h4 class="page-title">Student Dashboard</h4>
                        <div class="page-title-right">
                            <?php if ($isSuperAdmin): ?>
                                <span class="text-muted">Super Admin View - All Students</span>
                            <?php elseif ($student): ?>
                                <span class="text-muted">Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">Student Account</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$isSuperAdmin && $student && isset($classGraduated) && $classGraduated): ?>
            <!-- Graduation Notice -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h5><i class="ri-graduation-cap-line"></i> Class Graduated</h5>
                        <p>Your class has been graduated. You can view your academic records and historical data, but no new academic activities can be performed.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$isSuperAdmin && !$student): ?>
            <!-- Error Message if Student Record Not Found -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h5><i class="ri-error-warning-line"></i> Student Profile Not Found</h5>
                        <p>Your user account is not linked to a student record. Please contact your administrator to:</p>
                        <ul>
                            <li>Create a student record for you</li>
                            <li>Link your user account to the student record in <strong>Settings → User Management</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <?php if (!$isSuperAdmin): ?>
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-calendar-check-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Attendance Percentage">Attendance</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['attendance_percentage']; ?>%</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2"><i class="ri-arrow-up-line"></i> <?php echo $stats['present_days']; ?> Present</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-list-3-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Upcoming Assignments">Assignments</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['upcoming_assignments']; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-info me-2">Due Soon</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-edit-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Upcoming Exams">Exams</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['upcoming_exams']; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-warning me-2">Scheduled</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-checkbox-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Today's Status">Today</h5>
                            <h3 class="mt-3 mb-3">
                                <?php 
                                $statusClass = 'text-success';
                                if ($stats['today_status'] == 'Absent') $statusClass = 'text-danger';
                                elseif ($stats['today_status'] == 'Late') $statusClass = 'text-warning';
                                elseif ($stats['today_status'] == 'Not Marked') $statusClass = 'text-muted';
                                ?>
                                <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($stats['today_status']); ?></span>
                            </h3>
                            <p class="mb-0 text-muted">
                                <span class="text-muted">Attendance Status</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Outstanding Fees Statistics Card -->
            <?php if (!empty($outstandingFees)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card widget-flat border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">
                                        <i class="ri-alert-line text-warning"></i> Outstanding Fees
                                    </h5>
                                    <h2 class="mt-2 mb-2 text-danger"><?php echo formatCurrency($totalOutstanding); ?></h2>
                                    <p class="mb-0 text-muted">
                                        <span class="text-warning me-2"><i class="ri-file-list-3-line"></i> <?php echo count($outstandingFees); ?> item(s) pending</span>
                                    </p>
                                </div>
                                <div>
                                    <a href="<?php echo APP_URL; ?>modules/student/my-fees.php" class="btn btn-warning">
                                        <i class="ri-money-dollar-circle-line"></i> View & Pay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Outstanding Fees Alert -->
            <?php if (!$isSuperAdmin && !empty($outstandingFees)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">
                            <i class="ri-alert-line"></i> Outstanding Fees
                        </h5>
                        <p class="mb-2">You have <strong><?php echo count($outstandingFees); ?> outstanding fee(s)</strong> totaling <strong><?php echo formatCurrency($totalOutstanding); ?></strong></p>
                        <a href="<?php echo APP_URL; ?>modules/student/my-fees.php" class="btn btn-warning btn-sm">
                            <i class="ri-money-dollar-circle-line"></i> View All Fees
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Outstanding Fees Section -->
            <?php if (!$isSuperAdmin && !empty($outstandingFees)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">
                                    <i class="ri-money-dollar-circle-line text-warning"></i> Outstanding Fees
                                </h4>
                                <a href="<?php echo APP_URL; ?>modules/student/my-fees.php" class="btn btn-sm btn-primary">
                                    View All Fees <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Month/Period</th>
                                            <th>Due Date</th>
                                            <th>Amount Due</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($outstandingFees, 0, 5) as $fee): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($fee['fee_name']); ?></strong>
                                                <?php if (!empty($fee['fee_code'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($fee['fee_code']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fee['type'] == 'monthly'): ?>
                                                    <?php echo date('F Y', strtotime($fee['month'] . '-01')); ?>
                                                <?php else: ?>
                                                    <?php echo date('F Y', strtotime($fee['month'] . '-01')); ?>
                                                    <?php if (!empty($fee['invoice_no'])): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($fee['invoice_no']); ?></small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($fee['due_date'])): ?>
                                                    <?php echo formatDate($fee['due_date']); ?>
                                                    <?php if (strtotime($fee['due_date']) < strtotime('today')): ?>
                                                        <br><span class="badge bg-danger">Overdue</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-danger"><?php echo formatCurrency($fee['amount']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'warning';
                                                if ($fee['status'] == 'Overdue') $statusClass = 'danger';
                                                elseif ($fee['status'] == 'Partially Paid') $statusClass = 'info';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($fee['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($fee['type'] == 'invoice'): ?>
                                                    <a href="<?php echo APP_URL; ?>modules/student/view-invoice.php?id=<?php echo $fee['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View Invoice">
                                                        <i class="ri-eye-line"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo APP_URL; ?>modules/student/my-fees.php" 
                                                       class="btn btn-sm btn-primary" title="Pay Fee">
                                                        <i class="ri-money-dollar-circle-line"></i> Pay
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <?php if (count($outstandingFees) > 5): ?>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <a href="<?php echo APP_URL; ?>modules/student/my-fees.php" class="btn btn-sm btn-link">
                                                    View all <?php echo count($outstandingFees); ?> outstanding fees <i class="ri-arrow-right-line"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tfoot>
                                    <?php endif; ?>
                                </table>
                            </div>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h5 class="mb-0 text-muted">Total Outstanding</h5>
                                        <h3 class="mb-0 text-danger"><?php echo formatCurrency($totalOutstanding); ?></h3>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-0 text-muted">Outstanding Items</h5>
                                        <h3 class="mb-0"><?php echo count($outstandingFees); ?></h3>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="<?php echo APP_URL; ?>modules/fees/flexible-payment.php?student_id=<?php echo $studentId; ?>" 
                                           class="btn btn-primary">
                                            <i class="ri-money-dollar-circle-line"></i> Make Payment
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Today's Classes -->
                <?php if (!$isSuperAdmin): ?>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Today's Classes</h4>
                            <?php if (empty($todayClasses)): ?>
                                <p class="text-muted">No classes scheduled for today.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Room</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todayClasses as $class): ?>
                                                <tr>
                                                    <td><?php echo date('H:i', strtotime($class['start_time'])); ?> - <?php echo date('H:i', strtotime($class['end_time'])); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($class['subject_name']); ?>
                                                        <small class="text-muted d-block"><?php echo htmlspecialchars($class['subject_code']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars(($class['teacher_first_name'] ?? '') . ' ' . ($class['teacher_last_name'] ?? 'N/A')); ?></td>
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
                <?php endif; ?>

                <!-- Recent Announcements -->
                <div class="col-xl-<?php echo $isSuperAdmin ? '12' : '6'; ?>">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Recent Announcements</h4>
                            <?php if (empty($announcements)): ?>
                                <p class="text-muted">No announcements available.</p>
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

            <?php if (!$isSuperAdmin): ?>
            <!-- Recent Marks -->
            <?php if (!empty($recentMarks)): ?>
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Recent Exam Results</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Exam</th>
                                            <th>Subject</th>
                                            <th>Marks</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMarks as $mark): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($mark['exam_name']); ?></td>
                                                <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                                <td>
                                                    <strong><?php echo $mark['marks_obtained']; ?></strong> / <?php echo $mark['total_marks']; ?>
                                                    <?php 
                                                    $percentage = calculatePercentage($mark['marks_obtained'], $mark['total_marks']);
                                                    $grade = getGrade($percentage);
                                                    ?>
                                                    <span class="badge bg-<?php echo $percentage >= 50 ? 'success' : 'danger'; ?> ms-1"><?php echo $grade; ?></span>
                                                </td>
                                                <td><?php echo formatDate($mark['exam_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Assignments -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Upcoming Assignments</h4>
                            <?php if (empty($upcomingAssignments)): ?>
                                <p class="text-muted">No upcoming assignments.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($upcomingAssignments as $assignment): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($assignment['subject_name']); ?></p>
                                                    <small class="text-muted">Due: <?php echo formatDateTime($assignment['due_date'], 'd M Y, h:i A'); ?></small>
                                                </div>
                                                <?php if ($assignment['submission_id']): ?>
                                                    <span class="badge bg-success">Submitted</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Quick Actions</h4>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/student/my-classes.php" class="btn btn-primary w-100">
                                        <i class="ri-book-open-line"></i> My Classes
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/student/my-timetable.php" class="btn btn-info w-100">
                                        <i class="ri-calendar-line"></i> My Timetable
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/student/my-attendance.php" class="btn btn-success w-100">
                                        <i class="ri-calendar-check-line"></i> My Attendance
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo APP_URL; ?>modules/student/my-marks.php" class="btn btn-warning w-100">
                                        <i class="ri-file-edit-line"></i> My Marks
                                    </a>
                                </div>
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

