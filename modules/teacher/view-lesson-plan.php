<?php
/**
 * View Lesson Plan - Teacher Portal
 * 
 * View lesson plan details (only lesson plans created by teacher)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher', 'Super Admin', 'Admin']);

$pageTitle = 'Lesson Plan Details';

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);

$teacher = null;
$teacherId = null;

if (!$isSuperAdmin && !$isAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
        redirect(APP_URL . 'modules/teacher/dashboard.php');
    }
    $teacherId = $teacher['id'];
}

$currentSession = getCurrentSession();

$lessonPlanId = $_GET['id'] ?? 0;

if (empty($lessonPlanId)) {
    $_SESSION['error'] = 'Invalid lesson plan ID';
    redirect(APP_URL . 'modules/teacher/lesson-plans.php');
}

// Get lesson plan details
if ($isSuperAdmin || $isAdmin) {
    // Super Admin and Admin can view any lesson plan
    $sql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
            st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
            b.branch_name, ses.session_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            LEFT JOIN staff st ON lp.teacher_id = st.id
            LEFT JOIN branches b ON c.branch_id = b.id
            LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
            WHERE lp.id = ?";
    $stmt = executeQuery($sql, 'i', [$lessonPlanId]);
    $lessonPlan = fetchOne($stmt);
} else {
    // Teachers can only view their own lesson plans
    $sql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
            st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
            b.branch_name, ses.session_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            LEFT JOIN staff st ON lp.teacher_id = st.id
            LEFT JOIN branches b ON c.branch_id = b.id
            LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
            WHERE lp.id = ? AND lp.teacher_id = ?";
    $stmt = executeQuery($sql, 'ii', [$lessonPlanId, $teacherId]);
    $lessonPlan = fetchOne($stmt);
}

if (!$lessonPlan) {
    $_SESSION['error'] = 'Lesson plan not found or you do not have permission to view it.';
    redirect(APP_URL . 'modules/teacher/lesson-plans.php');
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
                        <h4 class="page-title">Lesson Plan Details</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>modules/teacher/dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>modules/teacher/lesson-plans.php">Lesson Plans</a></li>
                                <li class="breadcrumb-item active">View Lesson Plan</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lesson Plan Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($lessonPlan['lesson_title']); ?></h5>
                                <span class="badge bg-<?php echo $lessonPlan['status'] == 'Published' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($lessonPlan['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Class</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($lessonPlan['class_name']); ?> (<?php echo htmlspecialchars($lessonPlan['class_code'] ?? 'N/A'); ?>)</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Subject</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($lessonPlan['subject_name']); ?> (<?php echo htmlspecialchars($lessonPlan['subject_code'] ?? 'N/A'); ?>)</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Lesson Date</label>
                                        <p class="mb-0"><?php echo date('F d, Y', strtotime($lessonPlan['lesson_date'])); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Academic Session</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($lessonPlan['session_name'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <?php if ($isSuperAdmin || $isAdmin): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Teacher</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($lessonPlan['teacher_first_name'] . ' ' . $lessonPlan['teacher_last_name']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Branch</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($lessonPlan['branch_name'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <?php if (!empty($lessonPlan['objectives'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Learning Objectives</h6>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($lessonPlan['objectives'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($lessonPlan['content'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Content</h6>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($lessonPlan['content'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($lessonPlan['methodology'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Teaching Methodology</h6>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($lessonPlan['methodology'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($lessonPlan['resources'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Resources Required</h6>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($lessonPlan['resources'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($lessonPlan['assessment'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Assessment</h6>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($lessonPlan['assessment'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($lessonPlan['file_path'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Attached File</h6>
                                <a href="<?php echo APP_URL . htmlspecialchars($lessonPlan['file_path']); ?>" 
                                   class="btn btn-sm btn-primary" target="_blank">
                                    <i class="ri-download-line"></i> Download File
                                </a>
                            </div>
                            <?php endif; ?>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>Created:</strong> <?php echo date('F d, Y h:i A', strtotime($lessonPlan['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <small class="text-muted">
                                        <strong>Last Updated:</strong> <?php echo date('F d, Y h:i A', strtotime($lessonPlan['updated_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="<?php echo APP_URL; ?>modules/teacher/lesson-plans.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Lesson Plans
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

