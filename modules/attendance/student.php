<?php
/**
 * Student Attendance Management
 * 
 * Mark and manage student attendance
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Student Attendance';

// Get current user
$currentUser = getCurrentUser();

// Teacher Portal rule: teachers must use the dedicated teacher attendance pages,
// not this admin-level screen (which is class-wide, not subject-specific).
// If a logged-in user is only a Teacher (not Super Admin/Admin), redirect them.
if (hasRole(['Teacher']) && !hasRole(['Super Admin', 'Admin'])) {
    $_SESSION['error'] = 'Please use the Teacher Portal attendance page to mark subject-wise attendance.';
    redirect(APP_URL . 'modules/teacher/attendance-classes.php');
}

// Get date (default: today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$classId = $_GET['class_id'] ?? '';
$sectionId = $_GET['section_id'] ?? '';

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get students for selected class/section
$students = [];
if (!empty($classId) && !empty($sectionId)) {
    $sql = "SELECT s.*, 
            (SELECT status FROM student_attendance WHERE student_id = s.id AND attendance_date = ? LIMIT 1) as attendance_status,
            (SELECT remarks FROM student_attendance WHERE student_id = s.id AND attendance_date = ? LIMIT 1) as attendance_remarks
            FROM students s 
            WHERE s.current_class_id = ? 
            AND s.current_section_id = ? 
            AND s.status = 'Active'
            ORDER BY s.first_name, s.last_name";
    
    $stmt = executeQuery($sql, 'ssii', [$selectedDate, $selectedDate, $classId, $sectionId]);
    $students = fetchAll($stmt);
}

// Get attendance statistics for selected date
$statsSql = "SELECT 
    COUNT(DISTINCT student_id) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
    SUM(CASE WHEN status = 'Leave' THEN 1 ELSE 0 END) as leave_count
    FROM student_attendance 
    WHERE attendance_date = ?";

$statsStmt = executeQuery($statsSql, 's', [$selectedDate]);
$stats = fetchOne($statsStmt);

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
                            <button type="button" class="btn btn-success" onclick="markAllPresent()" <?php echo empty($students) ? 'disabled' : ''; ?>>
                                <i class="ri-checkbox-multiple-line"></i> Mark All Present
                            </button>
                        </div>
                        <h4 class="page-title">Student Attendance</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-user-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total</h5>
                                    <h2 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Present</h5>
                                    <h2 class="mb-0"><?php echo $stats['present'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-close-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Absent</h5>
                                    <h2 class="mb-0"><?php echo $stats['absent'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-time-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Late</h5>
                                    <h2 class="mb-0"><?php echo $stats['late'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selection Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3" id="attendanceFilterForm">
                                <div class="col-md-3">
                                    <label class="form-label required">Date</label>
                                    <input type="date" class="form-control" name="date" value="<?php echo $selectedDate; ?>" required>
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
                                    <label class="form-label required">Section</label>
                                    <select class="form-select" name="section_id" id="sectionSelect" required>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Load Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance List -->
            <?php if (!empty($students)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                Mark Attendance - <?php echo formatDate($selectedDate); ?>
                                <span class="badge bg-info ms-2"><?php echo count($students); ?> Students</span>
                            </h4>
                            
                            <form id="attendanceForm">
                                <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                                <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
                                <input type="hidden" name="section_id" value="<?php echo $sectionId; ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Student ID</th>
                                                <th width="25%">Student Name</th>
                                                <th width="30%">Status</th>
                                                <th width="25%">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $index => $student): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($student['student_id']); ?></strong>
                                                    <input type="hidden" name="students[<?php echo $student['id']; ?>][id]" value="<?php echo $student['id']; ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <input type="radio" class="btn-check" name="students[<?php echo $student['id']; ?>][status]" 
                                                               id="present_<?php echo $student['id']; ?>" value="Present" 
                                                               <?php echo ($student['attendance_status'] == 'Present') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-outline-success" for="present_<?php echo $student['id']; ?>">
                                                            <i class="ri-checkbox-circle-line"></i> Present
                                                        </label>
                                                        
                                                        <input type="radio" class="btn-check" name="students[<?php echo $student['id']; ?>][status]" 
                                                               id="absent_<?php echo $student['id']; ?>" value="Absent"
                                                               <?php echo ($student['attendance_status'] == 'Absent') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-outline-danger" for="absent_<?php echo $student['id']; ?>">
                                                            <i class="ri-close-circle-line"></i> Absent
                                                        </label>
                                                        
                                                        <input type="radio" class="btn-check" name="students[<?php echo $student['id']; ?>][status]" 
                                                               id="late_<?php echo $student['id']; ?>" value="Late"
                                                               <?php echo ($student['attendance_status'] == 'Late') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-outline-warning" for="late_<?php echo $student['id']; ?>">
                                                            <i class="ri-time-line"></i> Late
                                                        </label>
                                                        
                                                        <input type="radio" class="btn-check" name="students[<?php echo $student['id']; ?>][status]" 
                                                               id="leave_<?php echo $student['id']; ?>" value="Leave"
                                                               <?php echo ($student['attendance_status'] == 'Leave') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-outline-info" for="leave_<?php echo $student['id']; ?>">
                                                            <i class="ri-shield-check-line"></i> Leave
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="students[<?php echo $student['id']; ?>][remarks]" 
                                                           value="<?php echo htmlspecialchars($student['attendance_remarks'] ?? ''); ?>"
                                                           placeholder="Optional remarks">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-save-line"></i> Save Attendance
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($students) && !empty($classId)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> No students found in this class/section.
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Load sections when page loads (if class is selected)
$(document).ready(function() {
    <?php if (!empty($classId)): ?>
    loadSections(<?php echo $classId; ?>, <?php echo $sectionId; ?>);
    <?php endif; ?>
});

// Load sections when class changes
$('#classSelect').change(function() {
    const classId = $(this).val();
    loadSections(classId);
});

function loadSections(classId, selectedSectionId = null) {
    if (!classId) return;
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/get-sections.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Section</option>';
                response.data.forEach(function(section) {
                    const selected = (selectedSectionId && section.id == selectedSectionId) ? 'selected' : '';
                    options += `<option value="${section.id}" ${selected}>${section.section_name}</option>`;
                });
                $('#sectionSelect').html(options);
            }
        }
    });
}

// Mark all present
function markAllPresent() {
    $('input[type="radio"][value="Present"]').prop('checked', true);
    showToast('All students marked as present', 'success');
}

// Save attendance
$('#attendanceForm').on('submit', function(e) {
    e.preventDefault();
    
    // Check if all students have status selected
    let allSelected = true;
    $('input[type="radio"]:checked').length;
    const totalStudents = <?php echo count($students); ?>;
    const selectedCount = $('input[type="radio"]:checked').length;
    
    if (selectedCount < totalStudents) {
        showToast('Please mark attendance for all students', 'warning');
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/attendance/save-student-attendance.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to save attendance', 'error');
        }
    });
});
</script>

