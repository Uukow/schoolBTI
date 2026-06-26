<?php
/**
 * Lesson Plans - Teacher Portal
 * 
 * Manage lesson plans for assigned classes (fully isolated)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher', 'Super Admin']);

$pageTitle = 'Lesson Plans';

// Get current user and teacher record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$teacher = null;
$teacherId = null;

if ($isSuperAdmin) {
    // Super Admin sees all lesson plans
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

// Get lesson plans
if ($isSuperAdmin) {
    $sql = "SELECT lp.*, c.class_name, s.subject_name, st.first_name as teacher_first_name, st.last_name as teacher_last_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            LEFT JOIN staff st ON lp.teacher_id = st.id
            WHERE lp.session_id = ?
            ORDER BY lp.lesson_date DESC";
    $stmt = executeQuery($sql, 'i', [$currentSession['id']]);
    $lessonPlans = fetchAll($stmt);
    
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql));
    
    $subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql));
} else {
    $sql = "SELECT lp.*, c.class_name, s.subject_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            WHERE lp.teacher_id = ? AND lp.session_id = ?
            ORDER BY lp.lesson_date DESC";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $lessonPlans = fetchAll($stmt);

    // Get classes from assignments AND from existing lesson plans
    $classesSql = "SELECT DISTINCT c.* 
                   FROM classes c
                   WHERE c.is_active = 1 
                   AND (
                       c.id IN (
                           SELECT DISTINCT cs.class_id 
                           FROM class_subjects cs 
                   WHERE cs.teacher_id = ? AND cs.session_id = ?
                       )
                       OR c.id IN (
                           SELECT DISTINCT lp.class_id 
                           FROM lesson_plans lp 
                           WHERE lp.teacher_id = ? AND lp.session_id = ?
                       )
                   )
                   ORDER BY c.class_order";
    $classes = fetchAll(executeQuery($classesSql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]));

    // Get subjects from assignments AND from existing lesson plans
    $subjectsSql = "SELECT DISTINCT s.* 
                    FROM subjects s
                    WHERE s.is_active = 1 
                    AND (
                        s.id IN (
                            SELECT DISTINCT cs.subject_id 
                            FROM class_subjects cs 
                    WHERE cs.teacher_id = ? AND cs.session_id = ?
                        )
                        OR s.id IN (
                            SELECT DISTINCT lp.subject_id 
                            FROM lesson_plans lp 
                            WHERE lp.teacher_id = ? AND lp.session_id = ?
                        )
                    )
                    ORDER BY s.subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]));
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
                        <h4 class="page-title">Lesson Plans</h4>
                        <div class="page-title-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLessonPlanModal">
                                <i class="ri-add-line"></i> Add Lesson Plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lesson Plans List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">My Lesson Plans</h4>
                            
                            <?php if (empty($lessonPlans)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No lesson plans created yet.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="lesson-plans-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <?php if ($isSuperAdmin): ?>
                                                <th>Teacher</th>
                                                <?php endif; ?>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lessonPlans as $plan): ?>
                                                <tr>
                                                    <td><?php echo formatDate($plan['lesson_date']); ?></td>
                                                    <?php if ($isSuperAdmin): ?>
                                                    <td><?php echo htmlspecialchars(($plan['teacher_first_name'] ?? '') . ' ' . ($plan['teacher_last_name'] ?? '')); ?></td>
                                                    <?php endif; ?>
                                                    <td><?php echo htmlspecialchars($plan['class_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($plan['subject_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($plan['lesson_title']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $plan['status'] == 'Published' ? 'success' : 'warning'; ?>">
                                                            <?php echo htmlspecialchars($plan['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/teacher/view-lesson-plan.php?id=<?php echo $plan['id']; ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="ri-eye-line"></i> View
                                                        </a>
                                                        <?php if ($isSuperAdmin || $plan['teacher_id'] == $teacherId): ?>
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="editLessonPlan(<?php echo $plan['id']; ?>)">
                                                            <i class="ri-edit-line"></i> Edit
                                                        </button>
                                                        <?php endif; ?>
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
</div>

<!-- Add/Edit Lesson Plan Modal -->
<div class="modal fade" id="addLessonPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonPlanModalTitle">Add Lesson Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-lesson-plan-form">
                <input type="hidden" name="id" id="lesson_plan_id">
                <input type="hidden" name="session_id" value="<?php echo $currentSession['id']; ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($isSuperAdmin): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                <?php 
                                // Get teachers by designation or role (more flexible)
                                $teachersSql = "SELECT DISTINCT s.* 
                                               FROM staff s 
                                               LEFT JOIN users u ON s.user_id = u.id 
                                               LEFT JOIN roles r ON u.role_id = r.id 
                                               WHERE s.status = 'Active' 
                                               AND (s.designation LIKE '%Teacher%' 
                                                    OR s.designation LIKE '%teacher%' 
                                                    OR r.role_name = 'Teacher')
                                               ORDER BY s.first_name, s.last_name";
                                $allTeachers = fetchAll(executeQuery($teachersSql));
                                if (!empty($allTeachers)) {
                                    foreach ($allTeachers as $t): ?>
                                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></option>
                                    <?php endforeach;
                                } else {
                                    // Fallback: get all active staff if no teachers found
                                    $fallbackSql = "SELECT * FROM staff WHERE status = 'Active' ORDER BY first_name, last_name";
                                    $allTeachers = fetchAll(executeQuery($fallbackSql));
                                foreach ($allTeachers as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></option>
                                    <?php endforeach;
                                }
                                ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                        <?php endif; ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lesson Date <span class="text-danger">*</span></label>
                            <input type="date" name="lesson_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                            <input type="text" name="lesson_title" class="form-control" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Objectives</label>
                            <textarea name="objectives" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Methodology</label>
                            <textarea name="methodology" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Resources</label>
                            <textarea name="resources" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Assessment</label>
                            <textarea name="assessment" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Draft">Draft</option>
                                <option value="Published">Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Lesson Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Prepare additional JavaScript
ob_start();
?>
<script>
$(document).ready(function() {
    // Wait for DataTables to be available
    if (typeof $.fn.DataTable !== 'undefined') {
    $('#lesson-plans-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']]
    });
    } else {
        console.warn('DataTables is not loaded');
    }
    
    $('#add-lesson-plan-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin"></i> Saving...');
        
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/teacher/save-lesson-plan.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                if (response.success) {
                    if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                    } else if (typeof showToast !== 'undefined') {
                        showToast(response.message, 'success');
                    } else {
                        alert(response.message);
                    }
                    $('#addLessonPlanModal').modal('hide');
                    $('#add-lesson-plan-form')[0].reset();
                    $('#lesson_plan_id').val('');
                    $('#lessonPlanModalTitle').text('Add Lesson Plan');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    if (typeof toastr !== 'undefined') {
                    toastr.error(response.message);
                    } else if (typeof showToast !== 'undefined') {
                        showToast(response.message, 'error');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                var errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {
                        errorMsg = 'Server error: ' + xhr.status;
                    }
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else if (typeof showToast !== 'undefined') {
                    showToast(errorMsg, 'error');
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        });
    });
    
    // Reset form when modal is hidden
    $('#addLessonPlanModal').on('hidden.bs.modal', function() {
        $('#add-lesson-plan-form')[0].reset();
        $('#lesson_plan_id').val('');
        $('#lessonPlanModalTitle').text('Add Lesson Plan');
    });
});

// Function to edit lesson plan (must be in global scope for onclick)
window.editLessonPlan = function(id) {
    console.log('editLessonPlan called with id:', id);
    
    if (!id) {
        alert('Invalid lesson plan ID');
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/teacher/get-lesson-plan.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            console.log('Response received:', response);
            
            if (response.success && response.data) {
                var plan = response.data;
                console.log('Plan data:', plan);
                
                // Populate form fields
                var form = $('#add-lesson-plan-form');
                form.find('[name="id"]').val(plan.id);
                $('#lessonPlanModalTitle').text('Edit Lesson Plan');
                form.find('[name="class_id"]').val(plan.class_id);
                form.find('[name="subject_id"]').val(plan.subject_id);
                form.find('[name="lesson_date"]').val(plan.lesson_date);
                form.find('[name="lesson_title"]').val(plan.lesson_title);
                form.find('[name="objectives"]').val(plan.objectives || '');
                form.find('[name="content"]').val(plan.content || '');
                form.find('[name="methodology"]').val(plan.methodology || '');
                form.find('[name="resources"]').val(plan.resources || '');
                form.find('[name="assessment"]').val(plan.assessment || '');
                form.find('[name="status"]').val(plan.status || 'Draft');
                
                // Show modal using Bootstrap 5
                var modalElement = document.getElementById('addLessonPlanModal');
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                var errorMsg = response.message || 'Failed to load lesson plan';
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else if (typeof showToast !== 'undefined') {
                    showToast(errorMsg, 'error');
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr, status, error, xhr.responseText);
            var errorMsg = 'An error occurred while loading lesson plan.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = 'Server error: ' + xhr.status + ' - ' + error;
                }
            }
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMsg);
            } else if (typeof showToast !== 'undefined') {
                showToast(errorMsg, 'error');
            } else {
                alert('Error: ' + errorMsg);
            }
        }
    });
}
</script>
<?php
$additionalJS = ob_get_clean();
include '../../includes/footer.php';
?>

