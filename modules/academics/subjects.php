<?php
/**
 * Subjects Management
 * 
 * Manage school subjects
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Subjects Management';

// Get current user and check role
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

$teacher = null;
$teacherId = null;
$currentSession = null;

// Get teacher record if user is a teacher
if ($isTeacher && !$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if ($teacher) {
        $teacherId = $teacher['id'];
    }
    $currentSession = getCurrentSession();
}

// Get subjects based on role
if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
    // Teachers see only subjects assigned to them
    $sql = "SELECT DISTINCT s.*, 
            (SELECT COUNT(*) FROM class_subjects cs WHERE cs.subject_id = s.id AND cs.teacher_id = ? AND cs.session_id = ?) as assigned_classes
            FROM subjects s 
            INNER JOIN class_subjects cs ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? AND cs.session_id = ? AND s.is_active = 1
            ORDER BY s.subject_name";
    $stmt = executeQuery($sql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]);
    $subjects = fetchAll($stmt);
} else {
    // Super Admin and Admin see all subjects
    $sql = "SELECT s.*, 
            (SELECT COUNT(*) FROM class_subjects cs WHERE cs.subject_id = s.id) as assigned_classes
            FROM subjects s 
            ORDER BY s.subject_name";
    $subjects = fetchAll(executeQuery($sql));
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
                        <?php if (!$isTeacher || $isSuperAdmin): ?>
                        <div class="page-title-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                <i class="ri-book-line"></i> Add Subject
                            </button>
                        </div>
                        <?php endif; ?>
                        <h4 class="page-title">Subjects Management</h4>
                    </div>
                </div>
            </div>

            <!-- Subjects Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Subjects (<?php echo count($subjects); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Subject Code</th>
                                            <th>Type</th>
                                            <th>Assigned Classes</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                            <td>
                                                <?php
                                                $typeClass = $subject['subject_type'] == 'Core' ? 'primary' : 'info';
                                                ?>
                                                <span class="badge bg-<?php echo $typeClass; ?>">
                                                    <?php echo htmlspecialchars($subject['subject_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $subject['assigned_classes']; ?> Classes</span>
                                            </td>
                                            <td>
                                                <?php if ($subject['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewSubjectDetails(<?php echo $subject['id']; ?>)" 
                                                            class="btn btn-sm btn-primary" title="Subject Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button onclick="viewSubjectClasses(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['subject_name']); ?>')" 
                                                            class="btn btn-sm btn-info" title="Assigned Classes">
                                                        <i class="ri-list-check"></i>
                                                    </button>
                                                    <button onclick="viewSubjectAssignments(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['subject_name']); ?>')" 
                                                            class="btn btn-sm btn-success" title="Assignments">
                                                        <i class="ri-user-settings-line"></i>
                                                    </button>
                                                    <?php if (!$isTeacher || $isSuperAdmin): ?>
                                                    <button onclick="editSubject(<?php echo $subject['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteSubject(<?php echo $subject['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
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

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSubjectForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Subject Name</label>
                        <input type="text" class="form-control" name="subject_name" placeholder="e.g., Mathematics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Subject Code</label>
                        <input type="text" class="form-control" name="subject_code" placeholder="e.g., MATH101" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Type</label>
                        <select class="form-select" name="subject_type">
                            <option value="Core">Core</option>
                            <option value="Elective">Elective</option>
                            <option value="Optional">Optional</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSubjectForm">
                <input type="hidden" name="id" id="editSubjectId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Subject Name</label>
                        <input type="text" class="form-control" name="subject_name" id="editSubjectName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Subject Code</label>
                        <input type="text" class="form-control" name="subject_code" id="editSubjectCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Type</label>
                        <select class="form-select" name="subject_type" id="editSubjectType">
                            <option value="Core">Core</option>
                            <option value="Elective">Elective</option>
                            <option value="Optional">Optional</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editSubjectDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editSubjectIsActive" value="1">
                            <label class="form-check-label" for="editSubjectIsActive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subject Details Modal -->
<div class="modal fade" id="subjectDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectDetailsModalTitle">Subject Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="subjectDetailsContent">
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

<!-- Subject Classes Modal -->
<div class="modal fade" id="subjectClassesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectClassesModalTitle">Assigned Classes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subjectClassesList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Subject Assignments Modal -->
<div class="modal fade" id="subjectAssignmentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectAssignmentsModalTitle">Subject Assignments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <a href="<?php echo APP_URL; ?>modules/academics/assignments.php" class="btn btn-sm btn-primary" target="_blank">
                        <i class="ri-external-link-line"></i> Manage All Assignments
                    </a>
                </div>
                <div id="subjectAssignmentsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add subject
$('#addSubjectForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-subject.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addSubjectModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Edit subject
function editSubject(subjectId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-subject.php',
        type: 'GET',
        data: { id: subjectId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const subject = response.data;
                $('#editSubjectId').val(subject.id);
                $('#editSubjectName').val(subject.subject_name);
                $('#editSubjectCode').val(subject.subject_code);
                $('#editSubjectType').val(subject.subject_type);
                $('#editSubjectDescription').val(subject.description || '');
                $('#editSubjectIsActive').prop('checked', subject.is_active == 1);
                $('#editSubjectModal').modal('show');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to load subject details', 'error');
        }
    });
}

// Update subject
$('#editSubjectForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/update-subject.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#editSubjectModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete subject
function deleteSubject(subjectId) {
    confirmAction('Are you sure you want to delete this subject?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-subject.php',
            type: 'POST',
            data: { id: subjectId },
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

// View subject details
function viewSubjectDetails(subjectId) {
    $('#subjectDetailsModalTitle').text('Subject Details');
    $('#subjectDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#subjectDetailsModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-subject-details.php',
        type: 'GET',
        data: { id: subjectId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const subject = data.subject;
                
                let html = '<div class="row">';
                
                // Subject Information
                html += '<div class="col-md-12 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-information-line"></i> Subject Information</h5>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="30%">Subject Name</th><td><strong>' + escapeHtml(subject.subject_name) + '</strong></td></tr>';
                html += '<tr><th>Subject Code</th><td>' + escapeHtml(subject.subject_code) + '</td></tr>';
                html += '<tr><th>Subject Type</th><td><span class="badge bg-primary">' + escapeHtml(subject.subject_type) + '</span></td></tr>';
                html += '<tr><th>Description</th><td>' + (subject.description ? escapeHtml(subject.description) : '<em>No description</em>') + '</td></tr>';
                html += '<tr><th>Status</th><td>' + (subject.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>') + '</td></tr>';
                html += '<tr><th>Assigned Classes</th><td><span class="badge bg-info">' + data.assignedClassesCount + ' Classes</span></td></tr>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                // Assigned Classes
                html += '<div class="col-md-12 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-list-check"></i> Assigned Classes (' + data.assignedClasses.length + ')</h5>';
                if (data.assignedClasses.length > 0) {
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>Class</th><th>Code</th><th>Branch</th><th>Teacher</th></tr></thead><tbody>';
                    data.assignedClasses.forEach(function(item) {
                        const teacherName = (item.teacher_first_name && item.teacher_last_name) 
                            ? escapeHtml(item.teacher_first_name + ' ' + item.teacher_last_name)
                            : '<em class="text-muted">Not assigned</em>';
                        html += '<tr>';
                        html += '<td><strong>' + escapeHtml(item.class_name) + '</strong></td>';
                        html += '<td>' + escapeHtml(item.class_code) + '</td>';
                        html += '<td>' + escapeHtml(item.branch_name || 'N/A') + '</td>';
                        html += '<td>' + teacherName + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '</div>';
                } else {
                    html += '<p class="text-muted">This subject is not assigned to any class</p>';
                }
                html += '</div>';
                
                html += '</div>';
                
                $('#subjectDetailsContent').html(html);
            } else {
                $('#subjectDetailsContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#subjectDetailsContent').html('<div class="alert alert-danger">Failed to load subject details</div>');
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

// View subject classes
function viewSubjectClasses(subjectId, subjectName) {
    $('#subjectClassesModalTitle').text('Classes for ' + subjectName);
    $('#subjectClassesList').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#subjectClassesModal').modal('show');
    loadSubjectClasses(subjectId);
}

function loadSubjectClasses(subjectId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-subject-classes.php',
        type: 'GET',
        data: { subject_id: subjectId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-striped table-hover">';
                html += '<thead><tr><th>Class</th><th>Code</th><th>Branch</th><th>Teacher</th><th>Actions</th></tr></thead><tbody>';
                
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const teacherName = (item.teacher_first_name && item.teacher_last_name) 
                            ? escapeHtml(item.teacher_first_name + ' ' + item.teacher_last_name)
                            : '<em class="text-muted">Not assigned</em>';
                        html += `<tr>
                            <td><strong>${escapeHtml(item.class_name)}</strong></td>
                            <td>${escapeHtml(item.class_code)}</td>
                            <td>${escapeHtml(item.branch_name || 'N/A')}</td>
                            <td>${teacherName}</td>
                            <td>
                                <button onclick="deleteSubjectAssignment(${item.id}, ${subjectId})" class="btn btn-sm btn-danger" title="Remove">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="5" class="text-center text-muted">This subject is not assigned to any class</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#subjectClassesList').html(html);
            } else {
                $('#subjectClassesList').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#subjectClassesList').html('<div class="alert alert-danger">Failed to load classes</div>');
        }
    });
}

function deleteSubjectAssignment(assignmentId, subjectId) {
    confirmAction('Are you sure you want to remove this subject from the class?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-assignment.php',
            type: 'POST',
            data: { id: assignmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadSubjectClasses(subjectId);
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}

// View subject assignments
function viewSubjectAssignments(subjectId, subjectName) {
    $('#subjectAssignmentsModalTitle').text('Assignments for ' + subjectName);
    $('#subjectAssignmentsList').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#subjectAssignmentsModal').modal('show');
    loadSubjectAssignments(subjectId);
}

function loadSubjectAssignments(subjectId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-subject-assignments.php',
        type: 'GET',
        data: { subject_id: subjectId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-striped table-hover">';
                html += '<thead><tr><th>Class</th><th>Class Code</th><th>Branch</th><th>Teacher</th><th>Staff ID</th><th>Actions</th></tr></thead><tbody>';
                
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const teacherName = (item.teacher_first_name && item.teacher_last_name) 
                            ? escapeHtml(item.teacher_first_name + ' ' + item.teacher_last_name)
                            : '<em class="text-muted">Not assigned</em>';
                        const staffId = item.staff_id ? escapeHtml(item.staff_id) : '-';
                        html += `<tr>
                            <td><strong>${escapeHtml(item.class_name)}</strong></td>
                            <td>${escapeHtml(item.class_code)}</td>
                            <td>${escapeHtml(item.branch_name || 'N/A')}</td>
                            <td>${teacherName}</td>
                            <td>${staffId}</td>
                            <td>
                                <button onclick="editSubjectAssignment(${item.id})" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button onclick="deleteSubjectAssignmentFromList(${item.id}, ${subjectId})" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="6" class="text-center text-muted">No assignments found for this subject</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#subjectAssignmentsList').html(html);
            } else {
                $('#subjectAssignmentsList').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#subjectAssignmentsList').html('<div class="alert alert-danger">Failed to load assignments</div>');
        }
    });
}

function editSubjectAssignment(assignmentId) {
    // Close current modal and open assignments page in new tab
    $('#subjectAssignmentsModal').modal('hide');
    window.open('<?php echo APP_URL; ?>modules/academics/assignments.php', '_blank');
}

function deleteSubjectAssignmentFromList(assignmentId, subjectId) {
    confirmAction('Are you sure you want to delete this assignment?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-assignment.php',
            type: 'POST',
            data: { id: assignmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadSubjectAssignments(subjectId);
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}
</script>

