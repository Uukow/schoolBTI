<?php
/**
 * Marks Entry - Teacher Portal
 * 
 * Enter exam marks for students in teacher's subjects (fully isolated)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(teacherPortalRoles());

$pageTitle = __('enter_marks');

// Get current user and teacher record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$teacher = null;
$teacherId = null;

if ($isPortalViewer) {
    // Super Admin can enter marks for any subject
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

// Get filter parameters
$examFilter = $_GET['exam_id'] ?? '';
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';

// Get subjects for filter
if ($isPortalViewer) {
    $subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql));
    
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql));
} else {
    $subjectsSql = "SELECT DISTINCT s.* 
                    FROM subjects s
                    INNER JOIN class_subjects cs ON s.id = cs.subject_id
                    WHERE cs.teacher_id = ? AND cs.session_id = ?
                    ORDER BY s.subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql, 'ii', [$teacherId, $currentSession['id']]));

    $classesSql = "SELECT DISTINCT c.* 
                   FROM classes c
                   INNER JOIN class_subjects cs ON c.id = cs.class_id
                   WHERE cs.teacher_id = ? AND cs.session_id = ?
                   ORDER BY c.class_order";
    $classes = fetchAll(executeQuery($classesSql, 'ii', [$teacherId, $currentSession['id']]));
}

// Get exam schedules
$examSchedules = [];
if (!empty($subjectFilter)) {
    if ($isPortalViewer) {
        // Super Admin sees all exam schedules
        $sql = "SELECT es.*, e.exam_name, s.subject_name, c.class_name
                FROM exam_schedule es
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN classes c ON e.class_id = c.id
                WHERE e.session_id = ?";
        
        $params = [$currentSession['id']];
        $types = 'i';
        
        if (!empty($examFilter)) {
            $sql .= " AND es.exam_id = ?";
            $params[] = $examFilter;
            $types .= 'i';
        }
        
        if (!empty($subjectFilter)) {
            $sql .= " AND es.subject_id = ?";
            $params[] = $subjectFilter;
            $types .= 'i';
        }
        
        if (!empty($classFilter)) {
            $sql .= " AND e.class_id = ?";
            $params[] = $classFilter;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY es.exam_date DESC";
        
        $stmt = executeQuery($sql, $types, $params);
        $examSchedules = fetchAll($stmt);
    } else {
        // Teacher sees only their exam schedules
        $sql = "SELECT es.*, e.exam_name, s.subject_name, c.class_name
                FROM exam_schedule es
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN classes c ON e.class_id = c.id
                INNER JOIN class_subjects cs ON s.id = cs.subject_id AND c.id = cs.class_id
                WHERE cs.teacher_id = ? AND e.session_id = ?";
        
        $params = [$teacherId, $currentSession['id']];
        $types = 'ii';
        
        if (!empty($examFilter)) {
            $sql .= " AND es.exam_id = ?";
            $params[] = $examFilter;
            $types .= 'i';
        }
        
        if (!empty($subjectFilter)) {
            $sql .= " AND es.subject_id = ?";
            $params[] = $subjectFilter;
            $types .= 'i';
        }
        
        if (!empty($classFilter)) {
            $sql .= " AND e.class_id = ?";
            $params[] = $classFilter;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY es.exam_date DESC";
        
        $stmt = executeQuery($sql, $types, $params);
        $examSchedules = fetchAll($stmt);
    }
}

// Get students and marks if exam schedule selected
$students = [];
$examScheduleId = $_GET['exam_schedule_id'] ?? '';
$selectedSchedule = null;

if (!empty($examScheduleId)) {
    // Get exam schedule details
    if ($isPortalViewer) {
        $sql = "SELECT es.*, e.exam_name, s.subject_name, c.class_name
                FROM exam_schedule es
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN classes c ON e.class_id = c.id
                WHERE es.id = ?";
        $stmt = executeQuery($sql, 'i', [$examScheduleId]);
        $selectedSchedule = fetchOne($stmt);
    } else {
        $sql = "SELECT es.*, e.exam_name, s.subject_name, c.class_name
                FROM exam_schedule es
                INNER JOIN exams e ON es.exam_id = e.id
                INNER JOIN subjects s ON es.subject_id = s.id
                INNER JOIN classes c ON e.class_id = c.id
                INNER JOIN class_subjects cs ON s.id = cs.subject_id AND c.id = cs.class_id
                WHERE es.id = ? AND cs.teacher_id = ?";
        $stmt = executeQuery($sql, 'ii', [$examScheduleId, $teacherId]);
        $selectedSchedule = fetchOne($stmt);
    }
    
    if ($selectedSchedule) {
        // Get students for this class
        $sql = "SELECT s.*, sec.section_name, sm.marks_obtained, sm.is_absent, sm.remarks
                FROM students s
                INNER JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                LEFT JOIN student_marks sm ON s.id = sm.student_id AND sm.exam_schedule_id = ?
                WHERE s.current_class_id = ? AND s.status = 'Active'
                ORDER BY sec.section_name, s.first_name, s.last_name";
        $stmt = executeQuery($sql, 'ii', [$examScheduleId, $selectedSchedule['class_id']]);
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
                        <h4 class="page-title"><?php echo __('enter_marks'); ?></h4>
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
                                    <label class="form-label"><?php echo __('class'); ?></label>
                                    <select name="class_id" class="form-select">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><?php echo __('subject'); ?></label>
                                    <select name="subject_id" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line"></i> <?php echo __('load_exams'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Schedules List -->
            <?php if (!empty($examSchedules)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('select_exam_schedule'); ?></h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('exam'); ?></th>
                                            <th><?php echo __('subject'); ?></th>
                                            <th><?php echo __('class'); ?></th>
                                            <th><?php echo __('date'); ?></th>
                                            <th><?php echo __('time'); ?></th>
                                            <th><?php echo __('total_marks'); ?></th>
                                            <th><?php echo __('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($examSchedules as $schedule): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['exam_name']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                                                <td><?php echo formatDate($schedule['exam_date']); ?></td>
                                                <td><?php echo date('H:i', strtotime($schedule['start_time'])); ?> - <?php echo date('H:i', strtotime($schedule['end_time'])); ?></td>
                                                <td><?php echo $schedule['total_marks']; ?></td>
                                                <td>
                                                    <a href="?exam_schedule_id=<?php echo $schedule['id']; ?>&class_id=<?php echo $classFilter; ?>&subject_id=<?php echo $subjectFilter; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="ri-file-edit-line"></i> Enter Marks
                                                    </a>
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
            <?php endif; ?>

            <!-- Marks Entry Form -->
            <?php if (!empty($students) && $selectedSchedule): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <?php echo __('enter_marks'); ?>: <?php echo htmlspecialchars($selectedSchedule['exam_name']); ?> - 
                                <?php echo htmlspecialchars($selectedSchedule['subject_name']); ?>
                                (<?php echo __('total_marks'); ?>: <?php echo $selectedSchedule['total_marks']; ?>)
                            </h4>
                            
                            <form id="marks-form">
                                <input type="hidden" name="exam_schedule_id" value="<?php echo $selectedSchedule['id']; ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('student_id'); ?></th>
                                                <th><?php echo __('name'); ?></th>
                                                <th><?php echo __('section'); ?></th>
                                                <th><?php echo __('marks_obtained'); ?></th>
                                                <th><?php echo __('absent'); ?></th>
                                                <th><?php echo __('remarks'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <input type="number" 
                                                               name="marks[<?php echo $student['id']; ?>][marks_obtained]" 
                                                               class="form-control form-control-sm" 
                                                               value="<?php echo $student['marks_obtained'] ?? ''; ?>" 
                                                               min="0" 
                                                               max="<?php echo $selectedSchedule['total_marks']; ?>"
                                                               step="0.01"
                                                               placeholder="0-<?php echo $selectedSchedule['total_marks']; ?>">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" 
                                                               name="marks[<?php echo $student['id']; ?>][is_absent]" 
                                                               value="1"
                                                               <?php echo ($student['is_absent'] ?? false) ? 'checked' : ''; ?>
                                                               class="form-check-input">
                                                    </td>
                                                    <td>
                                                        <input type="text" 
                                                               name="marks[<?php echo $student['id']; ?>][remarks]" 
                                                               class="form-control form-control-sm" 
                                                               value="<?php echo htmlspecialchars($student['remarks'] ?? ''); ?>" 
                                                               placeholder="Optional">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="ri-save-line"></i> <?php echo __('save_marks'); ?>
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
</div>

<?php include '../../includes/footer-scripts.php'; ?>

<script>
$(document).ready(function() {
    $('#marks-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/teacher/save-marks.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
            }
        });
    });
    
    // Disable marks input if absent is checked
    $('input[name*="[is_absent]"]').on('change', function() {
        var row = $(this).closest('tr');
        var marksInput = row.find('input[name*="[marks_obtained]"]');
        
        if ($(this).is(':checked')) {
            marksInput.val('').prop('disabled', true);
        } else {
            marksInput.prop('disabled', false);
        }
    });
    
    // Trigger on page load
    $('input[name*="[is_absent]"]').trigger('change');
});
</script>

