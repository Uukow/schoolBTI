<?php
/**
 * My Classes - Student Portal
 * 
 * Display classes and subjects for the student
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Classes';

// Get current user and student record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$student = null;
$studentId = null;

if ($isPortalViewer) {
    $studentId = null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found. Please contact administrator to link your user account to a student record.';
        $studentId = null;
    } else {
        $studentId = $student['id'];
    }
}

$currentSession = getCurrentSession();

if (!$currentSession) {
    $_SESSION['error'] = 'No active session found. Please contact administrator.';
    // Don't redirect - show error on page
}

// Get student's classes and subjects
if ($isPortalViewer) {
    $assignedClasses = [];
} else {
    if ($student && isset($student['current_class_id']) && isset($student['current_section_id']) && $student['current_class_id'] && $student['current_section_id']) {
        // Check if class is graduated
        $classGraduated = isClassGraduated($student['current_class_id']);
        
        $sql = "SELECT cs.*, c.class_name, c.graduation_status, sec.section_name, s.subject_name, s.subject_code, s.subject_type,
                st.first_name as teacher_first_name, st.last_name as teacher_last_name
                FROM class_subjects cs
                INNER JOIN classes c ON cs.class_id = c.id
                INNER JOIN sections sec ON sec.class_id = cs.class_id AND sec.id = ?
                INNER JOIN subjects s ON cs.subject_id = s.id
                LEFT JOIN staff st ON cs.teacher_id = st.id
                WHERE cs.class_id = ? AND cs.session_id = ?
                ORDER BY s.subject_name";
        $stmt = executeQuery($sql, 'iii', [$student['current_section_id'], $student['current_class_id'], $currentSession['id']]);
        $assignedClasses = fetchAll($stmt);
    } else {
        $assignedClasses = [];
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
                        <h4 class="page-title">My Classes & Subjects</h4>
                        <?php if (!$isPortalViewer && $student): ?>
                        <div class="page-title-right">
                            <span class="text-muted">Class: <?php echo htmlspecialchars($student['current_class_id'] ? 'Class ' . $student['current_class_id'] : 'N/A'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Classes List -->
            <div class="row">
                <div class="col-12">
                    <?php if (!$isPortalViewer && $student && isset($classGraduated) && $classGraduated): ?>
                    <div class="alert alert-warning">
                        <h5><i class="ri-graduation-cap-line"></i> Class Graduated</h5>
                        <p>Your class has been graduated. You can view your subjects and historical data, but no new academic activities can be performed.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">My Subjects</h4>
                            
                            <?php if (!$isPortalViewer && !$student): ?>
                                <div class="alert alert-danger">
                                    <h5><i class="ri-error-warning-line"></i> Student Profile Not Found</h5>
                                    <p>Your user account is not linked to a student record. Please contact your administrator.</p>
                                </div>
                            <?php elseif (!$currentSession): ?>
                                <div class="alert alert-warning">
                                    <h5><i class="ri-error-warning-line"></i> No Active Session</h5>
                                    <p>No active academic session found. Please contact administrator.</p>
                                </div>
                            <?php elseif (empty($assignedClasses)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No subjects assigned yet. Please contact administrator.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="classes-table">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Subject Code</th>
                                                <th>Type</th>
                                                <th>Teacher</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignedClasses as $class): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($class['subject_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($class['subject_code']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $class['subject_type'] == 'Core' ? 'primary' : 'info'; ?>">
                                                            <?php echo htmlspecialchars($class['subject_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars(($class['teacher_first_name'] ?? 'N/A') . ' ' . ($class['teacher_last_name'] ?? '')); ?></td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/student/my-marks.php?subject_id=<?php echo $class['subject_id']; ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="ri-file-edit-line"></i> View Marks
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

<script>
$(document).ready(function() {
    $('#classes-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>

