<?php
/**
 * View Present/Absent Students - Admin Portal
 * 
 * View detailed list of present and absent students for a specific class, subject, and date
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'View Students Attendance';

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
$subjectId = $_GET['subject_id'] ?? '';
$statusFilter = $_GET['status'] ?? 'all'; // all, present, absent, late, leave

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get subjects for selected class
$subjects = [];
if (!empty($classId)) {
    $subjectsSql = "SELECT DISTINCT s.id, s.subject_name, s.subject_code
                    FROM subjects s
                    INNER JOIN class_subjects cs ON s.id = cs.subject_id
                    WHERE cs.class_id = ? AND cs.session_id = ?
                    ORDER BY s.subject_name";
    $subjectsStmt = executeQuery($subjectsSql, 'ii', [$classId, $currentSession['id']]);
    $subjects = fetchAll($subjectsStmt);
}

// Get students with attendance status
$students = [];
$stats = [
    'total' => 0,
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'leave' => 0,
    'half_day' => 0
];

if (!empty($classId) && !empty($subjectId)) {
    // Get all students in the class
    $studentsSql = "SELECT s.*, sec.section_name,
                    sa.status as attendance_status,
                    sa.remarks as attendance_remarks,
                    sa.marked_by,
                    u.username as marked_by_username,
                    st.first_name as teacher_first_name,
                    st.last_name as teacher_last_name
                    FROM students s
                    LEFT JOIN sections sec ON s.current_section_id = sec.id
                    LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                    AND sa.attendance_date = ? 
                    AND sa.subject_id = ?
                    LEFT JOIN users u ON sa.marked_by = u.id
                    LEFT JOIN staff st ON u.id = st.user_id
                    WHERE s.current_class_id = ? 
                    AND s.status = 'Active'";
    
    $params = [$selectedDate, $subjectId, $classId];
    $types = 'sii';
    
    // Apply status filter
    if ($statusFilter != 'all') {
        $studentsSql .= " AND sa.status = ?";
        $params[] = ucfirst($statusFilter);
        $types .= 's';
    }
    
    $studentsSql .= " ORDER BY sec.section_name, s.first_name, s.last_name";
    
    $studentsStmt = executeQuery($studentsSql, $types, $params);
    $students = fetchAll($studentsStmt);
    
    // Calculate statistics
    foreach ($students as $student) {
        $stats['total']++;
        if (!empty($student['attendance_status'])) {
            $status = $student['attendance_status'];
            if ($status == 'Present') $stats['present']++;
            elseif ($status == 'Absent') $stats['absent']++;
            elseif ($status == 'Late') $stats['late']++;
            elseif ($status == 'Leave') $stats['leave']++;
            elseif ($status == 'Half Day') $stats['half_day']++;
        } else {
            $stats['absent']++; // Count unmarked as absent
        }
    }
}

// Get subject details
$subjectDetails = null;
if (!empty($subjectId)) {
    $subjectSql = "SELECT * FROM subjects WHERE id = ?";
    $subjectStmt = executeQuery($subjectSql, 'i', [$subjectId]);
    $subjectDetails = fetchOne($subjectStmt);
}

// Get class details
$classDetails = null;
if (!empty($classId)) {
    $classSql = "SELECT * FROM classes WHERE id = ?";
    $classStmt = executeQuery($classSql, 'i', [$classId]);
    $classDetails = fetchOne($classStmt);
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
                            <a href="<?php echo APP_URL; ?>modules/attendance/dashboard.php?date=<?php echo urlencode($selectedDate); ?>&class_id=<?php echo $classId; ?>" class="btn btn-primary ms-2 no-print">
                                <i class="ri-arrow-left-line"></i> Back to Dashboard
                            </a>
                        </div>
                        <h4 class="page-title">View Students Attendance</h4>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label required">Date</label>
                                    <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">Class</label>
                                    <select class="form-select" name="class_id" id="classSelect" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">Subject</label>
                                    <select class="form-select" name="subject_id" id="subjectSelect" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectId == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Filter by Status</label>
                                    <select class="form-select" name="status">
                                        <option value="all" <?php echo ($statusFilter == 'all') ? 'selected' : ''; ?>>All Students</option>
                                        <option value="present" <?php echo ($statusFilter == 'present') ? 'selected' : ''; ?>>Present Only</option>
                                        <option value="absent" <?php echo ($statusFilter == 'absent') ? 'selected' : ''; ?>>Absent Only</option>
                                        <option value="late" <?php echo ($statusFilter == 'late') ? 'selected' : ''; ?>>Late Only</option>
                                        <option value="leave" <?php echo ($statusFilter == 'leave') ? 'selected' : ''; ?>>Leave Only</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> View Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($classId) && !empty($subjectId) && !empty($students)): ?>
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-user-line widget-icon text-primary"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Total</h5>
                                <h3 class="mt-3 mb-3"><?php echo $stats['total']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-checkbox-circle-line widget-icon text-success"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Present</h5>
                                <h3 class="mt-3 mb-3 text-success"><?php echo $stats['present']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-close-circle-line widget-icon text-danger"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Absent</h5>
                                <h3 class="mt-3 mb-3 text-danger"><?php echo $stats['absent']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-time-line widget-icon text-warning"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Late</h5>
                                <h3 class="mt-3 mb-3 text-warning"><?php echo $stats['late']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-shield-check-line widget-icon text-info"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Leave</h5>
                                <h3 class="mt-3 mb-3 text-info"><?php echo $stats['leave']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="ri-percent-line widget-icon text-secondary"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Attendance %</h5>
                                <h3 class="mt-3 mb-3 <?php echo ($stats['total'] > 0 && ($stats['present'] / $stats['total'] * 100) >= 75) ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 1) : 0; ?>%
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">
                                    Students List - <?php echo htmlspecialchars($classDetails['class_name'] ?? ''); ?> 
                                    - <?php echo htmlspecialchars($subjectDetails['subject_name'] ?? ''); ?>
                                    <small class="text-muted">(<?php echo formatDate($selectedDate); ?>)</small>
                                </h4>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="students-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Section</th>
                                                <th>Status</th>
                                                <th>Remarks</th>
                                                <th>Marked By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $counter = 1;
                                            foreach ($students as $student): 
                                                $status = $student['attendance_status'] ?? 'Not Marked';
                                                $statusClass = 'bg-secondary';
                                                if ($status == 'Present') $statusClass = 'bg-success';
                                                elseif ($status == 'Absent') $statusClass = 'bg-danger';
                                                elseif ($status == 'Late') $statusClass = 'bg-warning';
                                                elseif ($status == 'Leave') $statusClass = 'bg-info';
                                                elseif ($status == 'Half Day') $statusClass = 'bg-secondary';
                                            ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo htmlspecialchars($status); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['attendance_remarks'] ?? '-'); ?></td>
                                                    <td>
                                                        <?php if (!empty($student['marked_by_username'])): ?>
                                                            <small><?php echo htmlspecialchars($student['marked_by_username']); ?></small>
                                                            <?php if (!empty($student['teacher_first_name'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($student['teacher_first_name'] . ' ' . $student['teacher_last_name']); ?></small>
                                                            <?php endif; ?>
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
            <?php elseif (!empty($classId) && !empty($subjectId) && empty($students)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="ri-information-line"></i> No students found for the selected criteria.
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
    // Load subjects when class changes
    $('#classSelect').change(function() {
        const classId = $(this).val();
        if (classId) {
            loadSubjects(classId);
        } else {
            $('#subjectSelect').html('<option value="">Select Subject</option>');
        }
    });
    
    // Initialize DataTable
    $('#students-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'asc']], // Sort by name
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function loadSubjects(classId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-class-subjects.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                let options = '<option value="">Select Subject</option>';
                response.data.forEach(function(subject) {
                    // The API returns subject_id from class_subjects table
                    const subjectId = subject.subject_id;
                    const subjectName = subject.subject_name;
                    options += `<option value="${subjectId}">${subjectName}</option>`;
                });
                $('#subjectSelect').html(options);
            } else {
                // Fallback: reload page with class_id to get subjects
                const url = new URL(window.location);
                url.searchParams.set('class_id', classId);
                url.searchParams.delete('subject_id');
                window.location.href = url.toString();
            }
        },
        error: function() {
            // Fallback: reload page with class_id to get subjects
            const url = new URL(window.location);
            url.searchParams.set('class_id', classId);
            url.searchParams.delete('subject_id');
            window.location.href = url.toString();
        }
    });
}

function exportToExcel() {
    const table = document.querySelector('#students-table');
    if (!table) {
        Swal.fire({
            icon: 'error',
            title: 'No Data',
            text: 'Please select a class and subject first.'
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
    link.setAttribute('download', 'students_attendance_' + '<?php echo $selectedDate; ?>' + '_' + new Date().getTime() + '.csv');
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

