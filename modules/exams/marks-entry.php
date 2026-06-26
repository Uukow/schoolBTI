<?php
/**
 * Marks Entry Page
 * 
 * Enter exam marks for students
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Enter Exam Marks';

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

$teacher = null;
$teacherId = null;
$currentSession = getCurrentSession();

if ($isTeacher && !$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'modules/teacher/dashboard.php');
    }
    $teacherId = $teacher['id'];
}

// Get parameters
$examId = $_GET['exam_id'] ?? '';
$scheduleId = $_GET['schedule_id'] ?? '';

// Get exams - filtered for teachers
if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
    // Teachers see only exams for classes assigned to them
    $examsSql = "SELECT DISTINCT e.*, c.class_name 
                 FROM exams e 
                 INNER JOIN classes c ON e.class_id = c.id
                 INNER JOIN class_subjects cs ON c.id = cs.class_id
                 WHERE cs.teacher_id = ? AND e.session_id = ?
                 ORDER BY e.start_date DESC";
    $exams = fetchAll(executeQuery($examsSql, 'ii', [$teacherId, $currentSession['id']]));
} else {
    // Super Admin and Admin see all exams
    $examsSql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id ORDER BY e.start_date DESC";
    $exams = fetchAll(executeQuery($examsSql));
}

// Get exam schedule (subjects) if exam selected - filtered for teachers
$scheduleList = [];
if ($examId) {
    if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
        // Teachers see only schedules for subjects assigned to them
        $sql = "SELECT es.*, s.subject_name 
                FROM exam_schedule es 
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN class_subjects cs ON s.id = cs.subject_id AND e.class_id = cs.class_id
                WHERE es.exam_id = ? AND cs.teacher_id = ? AND e.session_id = ?
                ORDER BY es.exam_date";
        $stmt = executeQuery($sql, 'iii', [$examId, $teacherId, $currentSession['id']]);
        $scheduleList = fetchAll($stmt);
    } else {
        // Super Admin and Admin see all schedules
        $sql = "SELECT es.*, s.subject_name FROM exam_schedule es 
                LEFT JOIN subjects s ON es.subject_id = s.id 
                WHERE es.exam_id = ? ORDER BY es.exam_date";
        $stmt = executeQuery($sql, 'i', [$examId]);
        $scheduleList = fetchAll($stmt);
    }
}

// Get students and their marks if schedule selected
$students = [];
$scheduleInfo = null;

if ($scheduleId) {
    // Get schedule info - verify teacher assignment for teachers
    if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
        // Verify this schedule belongs to teacher's assigned subject/class
        $sql = "SELECT es.*, s.subject_name, e.exam_name, c.class_name
                FROM exam_schedule es
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN classes c ON e.class_id = c.id
                INNER JOIN class_subjects cs ON s.id = cs.subject_id AND c.id = cs.class_id
                WHERE es.id = ? AND cs.teacher_id = ? AND e.session_id = ?";
        $stmt = executeQuery($sql, 'iii', [$scheduleId, $teacherId, $currentSession['id']]);
        $scheduleInfo = fetchOne($stmt);
        
        if (!$scheduleInfo) {
            $_SESSION['error'] = 'You do not have permission to enter marks for this exam. This exam is not assigned to you.';
            redirect(APP_URL . 'modules/exams/marks-entry.php');
        }
    } else {
        // Super Admin and Admin can access any schedule
        $sql = "SELECT es.*, s.subject_name, e.exam_name, c.class_name
                FROM exam_schedule es
                LEFT JOIN subjects s ON es.subject_id = s.id
                LEFT JOIN exams e ON es.exam_id = e.id
                LEFT JOIN classes c ON e.class_id = c.id
                WHERE es.id = ?";
        $stmt = executeQuery($sql, 'i', [$scheduleId]);
        $scheduleInfo = fetchOne($stmt);
    }
    
    if ($scheduleInfo) {
        // Get students with their marks - only from the class of this exam
        $sql = "SELECT s.id, s.student_id, s.first_name, s.last_name,
                (SELECT marks_obtained FROM student_marks WHERE student_id = s.id AND exam_schedule_id = ? LIMIT 1) as marks,
                (SELECT is_absent FROM student_marks WHERE student_id = s.id AND exam_schedule_id = ? LIMIT 1) as is_absent,
                (SELECT remarks FROM student_marks WHERE student_id = s.id AND exam_schedule_id = ? LIMIT 1) as remarks
                FROM students s
                WHERE s.current_class_id = (SELECT class_id FROM exams WHERE id = ?)
                AND s.status = 'Active'
                ORDER BY s.first_name, s.last_name";
        
        $examIdFromSchedule = $scheduleInfo['exam_id'];
        $stmt = executeQuery($sql, 'iiii', [$scheduleId, $scheduleId, $scheduleId, $examIdFromSchedule]);
        $students = fetchAll($stmt);
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
                        <h4 class="page-title">Enter Exam Marks</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="ri-error-warning-line me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isTeacher && !$isSuperAdmin): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> 
                        <strong>Note:</strong> You are viewing exams and subjects assigned to you only. 
                        If you don't see an exam, it may not be assigned to your classes/subjects.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Selection Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label required">Select Exam</label>
                                    <select class="form-select" name="exam_id" id="examSelect" required>
                                        <option value="">Choose Exam</option>
                                        <?php if (empty($exams)): ?>
                                            <option value="" disabled>No exams available</option>
                                        <?php else: ?>
                                            <?php foreach ($exams as $e): ?>
                                                <option value="<?php echo $e['id']; ?>" <?php echo ($examId == $e['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($e['exam_name']); ?> - <?php echo htmlspecialchars($e['class_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (empty($exams) && $isTeacher && !$isSuperAdmin): ?>
                                        <small class="text-danger">
                                            <i class="ri-error-warning-line"></i> 
                                            No exams found for your assigned classes. Please contact administrator.
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($examId): ?>
                                <div class="col-md-5">
                                    <label class="form-label required">Select Subject</label>
                                    <?php if (empty($scheduleList)): ?>
                                        <select class="form-select" disabled>
                                            <option value="">No subjects available</option>
                                        </select>
                                        <small class="text-danger">
                                            <i class="ri-error-warning-line"></i> 
                                            <?php if ($isTeacher && !$isSuperAdmin): ?>
                                                No subjects assigned to you for this exam. Please contact administrator.
                                            <?php else: ?>
                                                No subjects scheduled for this exam.
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <select class="form-select" name="schedule_id" required>
                                            <option value="">Choose Subject</option>
                                            <?php foreach ($scheduleList as $item): ?>
                                                <option value="<?php echo $item['id']; ?>" <?php echo ($scheduleId == $item['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($item['subject_name']); ?> - <?php echo formatDate($item['exam_date']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Load
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marks Entry -->
            <?php if (!empty($students) && $scheduleInfo): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Subject:</strong> <?php echo htmlspecialchars($scheduleInfo['subject_name']); ?> | 
                                <strong>Total Marks:</strong> <?php echo $scheduleInfo['total_marks']; ?> | 
                                <strong>Passing:</strong> <?php echo $scheduleInfo['passing_marks']; ?>
                            </div>
                            
                            <form id="marksEntryForm">
                                <input type="hidden" name="schedule_id" value="<?php echo $scheduleId; ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Student ID</th>
                                                <th width="25%">Student Name</th>
                                                <th width="15%">Marks Obtained</th>
                                                <th width="10%">Absent</th>
                                                <th width="30%">Remarks</th>
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
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="students[<?php echo $student['id']; ?>][marks]" 
                                                           value="<?php echo $student['marks'] ?? ''; ?>"
                                                           min="0" max="<?php echo $scheduleInfo['total_marks']; ?>" step="0.01"
                                                           id="marks_<?php echo $student['id']; ?>">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input" 
                                                           name="students[<?php echo $student['id']; ?>][is_absent]" value="1"
                                                           <?php echo ($student['is_absent']) ? 'checked' : ''; ?>
                                                           onchange="toggleMarks(<?php echo $student['id']; ?>)">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="students[<?php echo $student['id']; ?>][remarks]" 
                                                           value="<?php echo htmlspecialchars($student['remarks'] ?? ''); ?>">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-save-line"></i> Save Marks
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Reload page when exam changes
$('#examSelect').change(function() {
    if ($(this).val()) {
        window.location.href = 'marks-entry.php?exam_id=' + $(this).val();
    }
});

// Toggle marks input when absent is checked
function toggleMarks(studentId) {
    const checkbox = $(`input[name="students[${studentId}][is_absent]"]`);
    const marksInput = $(`#marks_${studentId}`);
    
    if (checkbox.is(':checked')) {
        marksInput.val('').prop('disabled', true);
    } else {
        marksInput.prop('disabled', false);
    }
}

// Save marks
$('#marksEntryForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/exams/save-marks.php',
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
        }
    });
});
</script>

