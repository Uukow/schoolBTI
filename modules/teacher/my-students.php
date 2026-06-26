<?php
/**
 * My Students - Teacher Portal
 * 
 * Display students in teacher's assigned classes (fully isolated view)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(teacherPortalRoles());

$pageTitle = __('my_students');

// Get current user and teacher record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$teacher = null;
$teacherId = null;

if ($isPortalViewer) {
    // Super Admin sees all students
    $teacherId = null;
} else {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'modules/teacher/dashboard.php');
    }
    $teacherId = $teacher['id'];
}

$currentSession = getCurrentSession();

// Check if session exists
if (!$currentSession) {
    $_SESSION['error'] = 'No active session found. Please contact administrator.';
    redirect(APP_URL . 'modules/teacher/dashboard.php');
}

// Get filter parameters
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';

// Build query
if ($isPortalViewer) {
    // Super Admin sees all students
    $sql = "SELECT DISTINCT s.*, c.class_name, sec.section_name
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            WHERE s.status = 'Active'";

    $params = [];
    $types = '';

    // Apply filters
    if (!empty($classFilter)) {
        $sql .= " AND s.current_class_id = ?";
        $params[] = $classFilter;
        $types .= 'i';
    }

    $sql .= " ORDER BY c.class_order, sec.section_name, s.first_name, s.last_name";

    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $students = fetchAll($stmt);

    // Get all classes for filter
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql));

    // Get all subjects for filter
    $subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql));
} else {
    // STRICT: Teacher sees ONLY students in classes assigned to them
    // WHERE teacher_id = logged_in_teacher_id is REQUIRED
    $sql = "SELECT DISTINCT s.*, c.class_name, sec.section_name
            FROM students s
            INNER JOIN class_subjects cs ON s.current_class_id = cs.class_id
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.status = 'Active'";

    $params = [$teacherId, $currentSession['id']];
    $types = 'ii';

    // Apply filters
    if (!empty($classFilter)) {
        // STRICT: Filter by class (already verified via class_subjects join)
        $sql .= " AND s.current_class_id = ?";
        $params[] = $classFilter;
        $types .= 'i';
    }

    if (!empty($subjectFilter)) {
        $sql .= " AND cs.subject_id = ?";
        $params[] = $subjectFilter;
        $types .= 'i';
    }

    $sql .= " ORDER BY c.class_order, sec.section_name, s.first_name, s.last_name";

    $stmt = executeQuery($sql, $types, $params);
    $students = fetchAll($stmt);

    // STRICT: Get classes for filter (only teacher's classes) - WHERE teacher_id = logged_in_teacher_id
    $classesSql = "SELECT DISTINCT c.* 
                   FROM classes c
                   INNER JOIN class_subjects cs ON c.id = cs.class_id
                   WHERE cs.teacher_id = ? AND cs.session_id = ? AND c.is_active = 1
                   ORDER BY c.class_order";
    $classes = fetchAll(executeQuery($classesSql, 'ii', [$teacherId, $currentSession['id']]));

    // STRICT: Get subjects for filter (only teacher's subjects) - WHERE teacher_id = logged_in_teacher_id
    $subjectsSql = "SELECT DISTINCT s.* 
                    FROM subjects s
                    INNER JOIN class_subjects cs ON s.id = cs.subject_id
                    WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.is_active = 1
                    ORDER BY s.subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql, 'ii', [$teacherId, $currentSession['id']]));
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
                        <h4 class="page-title"><?php echo __('my_students'); ?></h4>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <label class="form-label"><?php echo __('subject'); ?></label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line"></i> <?php echo __('filter'); ?>
                                        </button>
                                        <a href="<?php echo APP_URL; ?>modules/teacher/my-students.php" class="btn btn-secondary">
                                            <i class="ri-refresh-line"></i> <?php echo __('reset'); ?>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo __('students_list'); ?> (<?php echo count($students); ?> <?php echo __('students'); ?>)</h4>
                            
                            <?php if (empty($students)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> <?php echo __('no_students_found'); ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="students-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('photo'); ?></th>
                                                <th><?php echo __('student_id'); ?></th>
                                                <th><?php echo __('name'); ?></th>
                                                <th><?php echo __('class'); ?></th>
                                                <th><?php echo __('section'); ?></th>
                                                <th><?php echo __('email'); ?></th>
                                                <th><?php echo __('phone'); ?></th>
                                                <th><?php echo __('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($student['photo'])): ?>
                                                            <img src="<?php echo APP_URL . $student['photo']; ?>" alt="Photo" class="rounded-circle" width="40" height="40">
                                                        <?php else: ?>
                                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                                                <span class="text-white"><?php echo strtoupper(substr($student['first_name'], 0, 1)); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/teacher/view-student.php?id=<?php echo $student['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="View Details">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="<?php echo APP_URL; ?>modules/teacher/attendance.php?class_id=<?php echo $student['current_class_id']; ?>" 
                                                           class="btn btn-sm btn-success" title="Mark Attendance">
                                                            <i class="ri-calendar-check-line"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<?php include '../../includes/footer-scripts.php'; ?>

<script>
$(document).ready(function() {
    $('#students-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
});
</script>

