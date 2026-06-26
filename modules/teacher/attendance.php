<?php
/**
 * Mark Attendance - Teacher Portal
 * 
 * Mark attendance for a specific class assigned to the teacher
 * STRICT: Class must be assigned to teacher via class_subjects table
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher', 'Super Admin']);

$pageTitle = 'Mark Attendance';

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$teacher = null;
$teacherId = null;

if ($isSuperAdmin) {
    // Super Admin can mark attendance for any class
    $teacherId = null;
} else {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'dashboard.php');
    }
    $teacherId = $teacher['id'];
}

$currentSession = getCurrentSession();

if (!$currentSession) {
    $_SESSION['error'] = 'No active academic session found. Please contact administrator.';
    redirect(APP_URL . 'modules/teacher/dashboard.php');
}

// Get class_id and subject_id from URL - BOTH REQUIRED
$classId = $_GET['class_id'] ?? '';
$subjectId = $_GET['subject_id'] ?? '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$dayOfWeek = date('l', strtotime($selectedDate));

if (empty($classId)) {
    $_SESSION['error'] = 'Class ID is required. Please select a class.';
    redirect(APP_URL . 'modules/teacher/attendance-classes.php');
}

if (empty($subjectId)) {
    $_SESSION['error'] = 'Subject ID is required. Please select a subject.';
    redirect(APP_URL . 'modules/teacher/attendance-classes.php?date=' . urlencode($selectedDate));
}

// STRICT VERIFICATION: Verify that this class AND subject are assigned to the teacher
if (!$isSuperAdmin) {
    // Verify via class_subjects
    $verifySql = "SELECT cs.id 
                  FROM class_subjects cs
                  WHERE cs.class_id = ? AND cs.subject_id = ? AND cs.teacher_id = ? AND cs.session_id = ?";
    $verifyStmt = executeQuery($verifySql, 'iiii', [$classId, $subjectId, $teacherId, $currentSession['id']]);
    $verification = fetchOne($verifyStmt);
    
    if (!$verification) {
        $_SESSION['error'] = 'Unauthorized: This subject is not assigned to you for this class.';
        redirect(APP_URL . 'modules/teacher/attendance-classes.php?date=' . urlencode($selectedDate));
    }
    
    // Also verify via timetable that teacher teaches this subject on this day
    $timetableVerifySql = "SELECT id FROM timetable 
                           WHERE class_id = ? AND subject_id = ? AND teacher_id = ? 
                           AND day_of_week = ? AND session_id = ?";
    $timetableVerifyStmt = executeQuery($timetableVerifySql, 'iiiss', [$classId, $subjectId, $teacherId, $dayOfWeek, $currentSession['id']]);
    $timetableVerification = fetchOne($timetableVerifyStmt);
    
    if (!$timetableVerification) {
        $_SESSION['error'] = 'Unauthorized: You do not teach this subject on ' . $dayOfWeek . '.';
        redirect(APP_URL . 'modules/teacher/attendance-classes.php?date=' . urlencode($selectedDate));
    }
}

// Get subject details
$subjectSql = "SELECT * FROM subjects WHERE id = ?";
$subject = fetchOne(executeQuery($subjectSql, 'i', [$subjectId]));

if (!$subject) {
    $_SESSION['error'] = 'Subject not found.';
    redirect(APP_URL . 'modules/teacher/attendance-classes.php?date=' . urlencode($selectedDate));
}

// Get class details
$classSql = "SELECT * FROM classes WHERE id = ?";
$class = fetchOne(executeQuery($classSql, 'i', [$classId]));

if (!$class) {
    $_SESSION['error'] = 'Class not found.';
    redirect(APP_URL . 'modules/teacher/attendance-classes.php');
}

// Get date filter - use from URL or default to today
$defaultDate = date('Y-m-d');
$sessionStartDate = $currentSession['start_date'] ?? null;
$sessionEndDate = $currentSession['end_date'] ?? null;

// Ensure default date is within session period
if ($sessionStartDate && $defaultDate < $sessionStartDate) {
    $defaultDate = $sessionStartDate;
} elseif ($sessionEndDate && $defaultDate > $sessionEndDate) {
    $defaultDate = $sessionEndDate;
}

$dateFilter = !empty($_GET['date']) ? $_GET['date'] : $defaultDate;

// Validate that selected date is within session period
if ($sessionStartDate && $sessionEndDate) {
    if ($dateFilter < $sessionStartDate || $dateFilter > $sessionEndDate) {
        $_SESSION['error'] = 'Selected date is outside the current academic session period (' . 
                             formatDate($sessionStartDate, 'd M Y') . ' to ' . formatDate($sessionEndDate, 'd M Y') . ').';
        $dateFilter = $defaultDate; // Reset to valid date
    }
}

// Get sections for this class
$sectionsSql = "SELECT * FROM sections WHERE class_id = ? AND is_active = 1 ORDER BY section_name";
$sections = fetchAll(executeQuery($sectionsSql, 'i', [$classId]));

// Get section filter
$sectionFilter = $_GET['section_id'] ?? '';

// STRICT QUERY: Get students ONLY from this class assigned to teacher
if ($isSuperAdmin) {
    // Super Admin can see all students in the class
    $sql = "SELECT s.*, sec.section_name,
            sa.status as attendance_status, sa.remarks, sa.id as attendance_id
            FROM students s
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = ? AND sa.subject_id = ?
            WHERE s.current_class_id = ? AND s.status = 'Active'";
    
    $params = [$dateFilter, $subjectId, $classId];
    $types = 'sii';
    
    if (!empty($sectionFilter)) {
        $sql .= " AND s.current_section_id = ?";
        $params[] = $sectionFilter;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY sec.section_name, s.first_name, s.last_name";
    
    $stmt = executeQuery($sql, $types, $params);
    $students = fetchAll($stmt);
} else {
    // STRICT: Only students in classes assigned to this teacher via class_subjects AND timetable
    $sql = "SELECT DISTINCT s.*, sec.section_name,
            sa.status as attendance_status, sa.remarks, sa.id as attendance_id
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id AND cs.subject_id = ?
            INNER JOIN timetable t ON t.class_id = cs.class_id AND t.subject_id = cs.subject_id AND t.teacher_id = ?
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = ? AND sa.subject_id = ?
            WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.current_class_id = ? 
            AND t.day_of_week = ? AND t.session_id = ? AND s.status = 'Active'";
    
    $params = [$subjectId, $teacherId, $dateFilter, $subjectId, $teacherId, $currentSession['id'], $classId, $dayOfWeek, $currentSession['id']];
    $types = 'iisiiissi';
    
    if (!empty($sectionFilter)) {
        $sql .= " AND s.current_section_id = ?";
        $params[] = $sectionFilter;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY sec.section_name, s.first_name, s.last_name";
    
    $stmt = executeQuery($sql, $types, $params);
    $students = fetchAll($stmt);
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
                        <h4 class="page-title">Mark Attendance - <?php echo htmlspecialchars($class['class_name']); ?> - <?php echo htmlspecialchars($subject['subject_name']); ?></h4>
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/teacher/attendance-classes.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Classes
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($classId); ?>">
                                <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subjectId); ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           name="date" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($dateFilter); ?>" 
                                           min="<?php echo htmlspecialchars($sessionStartDate ?? ''); ?>"
                                           max="<?php echo htmlspecialchars($sessionEndDate ?? ''); ?>"
                                           required>
                                    <?php if ($sessionStartDate && $sessionEndDate): ?>
                                    <small class="text-muted">
                                        <i class="ri-calendar-line"></i> 
                                        Session Period: <?php echo formatDate($sessionStartDate, 'd M Y'); ?> to <?php echo formatDate($sessionEndDate, 'd M Y'); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($sections)): ?>
                                <div class="col-md-4">
                                    <label class="form-label">Section</label>
                                    <select name="section_id" class="form-select">
                                        <option value="">All Sections</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?php echo $section['id']; ?>" <?php echo ($sectionFilter == $section['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($section['section_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line"></i> Load Students
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Form -->
            <?php if (!empty($students)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                Mark Attendance for <?php echo htmlspecialchars($class['class_name']); ?> 
                                - <?php echo htmlspecialchars($subject['subject_name']); ?>
                                - <?php echo formatDate($dateFilter); ?> 
                                (<?php echo count($students); ?> students)
                            </h4>
                            
                            <?php 
                            // Check if any students don't have sections assigned
                            $studentsWithoutSections = array_filter($students, function($s) {
                                return empty($s['current_section_id']) || empty($s['section_name']);
                            });
                            if (!empty($studentsWithoutSections) && count($studentsWithoutSections) > 0): 
                            ?>
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line"></i> 
                                <strong>Note:</strong> <?php echo count($studentsWithoutSections); ?> student(s) are not assigned to a section. 
                                Please contact administrator to assign students to sections for better organization.
                            </div>
                            <?php endif; ?>
                            
                            <form id="attendance-form">
                                <input type="hidden" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($classId); ?>">
                                <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subjectId); ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Student ID</th>
                                                <th width="25%">Name</th>
                                                <th width="10%">Section</th>
                                                <th width="20%">Status</th>
                                                <th width="25%">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $counter = 1; foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td>
                                                        <?php 
                                                        if (!empty($student['section_name'])) {
                                                            echo htmlspecialchars($student['section_name']);
                                                        } elseif (!empty($student['current_section_id'])) {
                                                            echo '<span class="text-muted">Section ID: ' . $student['current_section_id'] . '</span>';
                                                        } else {
                                                            echo '<span class="text-muted">Not Assigned</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <select name="attendance[<?php echo $student['id']; ?>][status]" class="form-select form-select-sm" required>
                                                            <option value="Present" <?php echo ($student['attendance_status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                                            <option value="Absent" <?php echo ($student['attendance_status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                                            <option value="Late" <?php echo ($student['attendance_status'] == 'Late') ? 'selected' : ''; ?>>Late</option>
                                                            <option value="Half Day" <?php echo ($student['attendance_status'] == 'Half Day') ? 'selected' : ''; ?>>Half Day</option>
                                                            <option value="Leave" <?php echo ($student['attendance_status'] == 'Leave') ? 'selected' : ''; ?>>Leave</option>
                                                        </select>
                                                        <?php if (!empty($student['attendance_id'])): ?>
                                                            <input type="hidden" name="attendance[<?php echo $student['id']; ?>][attendance_id]" value="<?php echo $student['attendance_id']; ?>">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="attendance[<?php echo $student['id']; ?>][remarks]" 
                                                               class="form-control form-control-sm" 
                                                               value="<?php echo htmlspecialchars($student['remarks'] ?? ''); ?>" 
                                                               placeholder="Optional remarks">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="ri-save-line"></i> Save Attendance
                                    </button>
                                    <a href="<?php echo APP_URL; ?>modules/teacher/attendance-classes.php" class="btn btn-secondary">
                                        <i class="ri-close-line"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php elseif (!empty($classId)): ?>
                <div class="alert alert-info">
                    <i class="ri-information-line"></i> No students found in this class for the selected criteria.
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<?php include '../../includes/footer-scripts.php'; ?>

<script>
$(document).ready(function() {
    $('#attendance-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading alert
        Swal.fire({
            title: 'Saving Attendance...',
            text: 'Please wait while we save the attendance records.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/teacher/save-attendance.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Attendance Saved!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745',
                        timer: 3000,
                        timerProgressBar: true
                    }).then((result) => {
                        if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while saving attendance.';
                
                // Try to parse error response
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // If response is not JSON, use default message
                    errorMessage = 'An error occurred while saving attendance. Please check if the database migration has been run.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                    width: '600px'
                });
            }
        });
    });
});
</script>
