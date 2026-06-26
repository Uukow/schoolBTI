<?php
/**
 * Class-Subject-Teacher Assignments
 * 
 * Assign teachers to classes and subjects
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Class-Subject Assignments';

// Get current user and session
$currentUser = getCurrentUser();
$currentSession = getCurrentSession();

// Get branch filter
$branchFilter = '';
$branchId = null;

if (hasRole(['Super Admin'])) {
    $branchId = $_GET['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
} else {
    $branchId = $currentUser['branch_id'];
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
}

// Get all assignments (excluding graduated classes)
$sql = "SELECT cs.*, 
        c.class_name, c.class_code,
        s.subject_name, s.subject_code, s.subject_type,
        st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
        b.branch_name
        FROM class_subjects cs
        INNER JOIN classes c ON cs.class_id = c.id
        INNER JOIN subjects s ON cs.subject_id = s.id
        LEFT JOIN staff st ON cs.teacher_id = st.id
        LEFT JOIN branches b ON c.branch_id = b.id
        WHERE cs.session_id = ? 
        AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
        $branchFilter
        ORDER BY c.class_order, s.subject_name";

$assignments = fetchAll(executeQuery($sql, 'i', [$currentSession['id']]));

// Get classes for form (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                $branchFilter 
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get subjects for form
$subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
$subjects = fetchAll(executeQuery($subjectsSql));

// Get teachers (staff with Teacher designation)
$teachersSql = "SELECT s.id, s.staff_id, s.first_name, s.last_name, s.designation, b.branch_name
                FROM staff s
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE s.status = 'Active' AND s.designation LIKE '%Teacher%'";
if (!hasRole(['Super Admin']) && $branchId) {
    $teachersSql .= " AND s.branch_id = $branchId";
}
$teachersSql .= " ORDER BY s.first_name, s.last_name";
$teachers = fetchAll(executeQuery($teachersSql));

// Get branches for filter
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                                <i class="ri-add-line"></i> Add Assignment
                            </button>
                        </div>
                        <h4 class="page-title">Class-Subject-Teacher Assignments</h4>
                    </div>
                </div>
            </div>

            <?php if (hasRole(['Super Admin'])): ?>
            <!-- Branch Filter -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Branch</label>
                                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignments List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Assignments (<?php echo count($assignments); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th>Branch</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($assignment['class_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($assignment['class_code']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($assignment['subject_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($assignment['subject_code']); ?></small>
                                                <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($assignment['subject_type']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($assignment['teacher_first_name']): ?>
                                                    <strong><?php echo htmlspecialchars($assignment['teacher_first_name'] . ' ' . $assignment['teacher_last_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($assignment['staff_id']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted"><em>Not assigned</em></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($assignment['branch_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewAssignmentDetails(<?php echo $assignment['id']; ?>)" 
                                                            class="btn btn-sm btn-primary" title="Assignment Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button onclick="viewClassFromAssignment(<?php echo $assignment['class_id']; ?>)" 
                                                            class="btn btn-sm btn-info" title="View Class">
                                                        <i class="ri-building-line"></i>
                                                    </button>
                                                    <button onclick="viewSubjectFromAssignment(<?php echo $assignment['subject_id']; ?>)" 
                                                            class="btn btn-sm btn-success" title="View Subject">
                                                        <i class="ri-book-open-line"></i>
                                                    </button>
                                                    <?php if ($assignment['teacher_id']): ?>
                                                    <button onclick="viewTeacherFromAssignment(<?php echo $assignment['teacher_id']; ?>)" 
                                                            class="btn btn-sm btn-secondary" title="View Teacher">
                                                        <i class="ri-user-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button onclick="editAssignment(<?php echo $assignment['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteAssignment(<?php echo $assignment['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
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

        </div>
    </div>

<!-- Add Assignment Modal -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAssignmentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Class</label>
                        <select class="form-select" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name'] . ' (' . $class['class_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <select class="form-select" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher</label>
                        <select class="form-select" name="teacher_id">
                            <option value="">Select Teacher (Optional)</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name'] . ' (' . $teacher['staff_id'] . ')'); ?>
                                    <?php if ($teacher['branch_name']): ?>
                                        - <?php echo htmlspecialchars($teacher['branch_name']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">You can assign a teacher later if needed</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Save Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAssignmentForm">
                <input type="hidden" name="id" id="editAssignmentId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Class</label>
                        <select class="form-select" name="class_id" id="editClassId" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name'] . ' (' . $class['class_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <select class="form-select" name="subject_id" id="editSubjectId" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher</label>
                        <select class="form-select" name="teacher_id" id="editTeacherId">
                            <option value="">Select Teacher (Optional)</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name'] . ' (' . $teacher['staff_id'] . ')'); ?>
                                    <?php if ($teacher['branch_name']): ?>
                                        - <?php echo htmlspecialchars($teacher['branch_name']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assignment Details Modal -->
<div class="modal fade" id="assignmentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentDetailsModalTitle">Assignment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add assignment
$('#addAssignmentForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-assignment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addAssignmentModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Edit assignment
function editAssignment(assignmentId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-assignment.php',
        type: 'GET',
        data: { id: assignmentId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#editAssignmentId').val(data.id);
                $('#editClassId').val(data.class_id);
                $('#editSubjectId').val(data.subject_id);
                $('#editTeacherId').val(data.teacher_id || '');
                $('#editAssignmentModal').modal('show');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to load assignment details', 'error');
        }
    });
}

// Update assignment
$('#editAssignmentForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/update-assignment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#editAssignmentModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete assignment
function deleteAssignment(assignmentId) {
    confirmAction('Are you sure you want to delete this assignment?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-assignment.php',
            type: 'POST',
            data: { id: assignmentId },
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
}

// View assignment details
function viewAssignmentDetails(assignmentId) {
    $('#assignmentDetailsModalTitle').text('Assignment Details');
    $('#assignmentDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#assignmentDetailsModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-assignment-details.php',
        type: 'GET',
        data: { id: assignmentId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const assignment = data.assignment;
                
                let html = '<div class="row">';
                
                // Assignment Information
                html += '<div class="col-md-12 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-information-line"></i> Assignment Information</h5>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="30%">Class</th><td><strong>' + escapeHtml(assignment.class_name) + '</strong> (' + escapeHtml(assignment.class_code) + ')</td></tr>';
                html += '<tr><th>Subject</th><td><strong>' + escapeHtml(assignment.subject_name) + '</strong> (' + escapeHtml(assignment.subject_code) + ') <span class="badge bg-secondary">' + escapeHtml(assignment.subject_type) + '</span></td></tr>';
                html += '<tr><th>Branch</th><td>' + escapeHtml(assignment.branch_name || 'N/A') + '</td></tr>';
                if (assignment.teacher_first_name) {
                    html += '<tr><th>Teacher</th><td><strong>' + escapeHtml(assignment.teacher_first_name + ' ' + assignment.teacher_last_name) + '</strong><br><small class="text-muted">Staff ID: ' + escapeHtml(assignment.staff_id || 'N/A') + '</small></td></tr>';
                } else {
                    html += '<tr><th>Teacher</th><td><em class="text-muted">Not assigned</em></td></tr>';
                }
                html += '<tr><th>Academic Session</th><td>' + escapeHtml(assignment.session_name || 'Current Session') + '</td></tr>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                html += '</div>';
                
                $('#assignmentDetailsContent').html(html);
            } else {
                $('#assignmentDetailsContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#assignmentDetailsContent').html('<div class="alert alert-danger">Failed to load assignment details</div>');
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// View class from assignment
function viewClassFromAssignment(classId) {
    window.open('<?php echo APP_URL; ?>modules/academics/classes.php', '_blank');
}

// View subject from assignment
function viewSubjectFromAssignment(subjectId) {
    window.open('<?php echo APP_URL; ?>modules/academics/subjects.php', '_blank');
}

// View teacher from assignment
function viewTeacherFromAssignment(teacherId) {
    window.open('<?php echo APP_URL; ?>modules/hr/staff.php', '_blank');
}
</script>

