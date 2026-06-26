<?php
/**
 * Classes Management Page
 * 
 * Manage classes and sections
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Classes Management';

// Get branch filter
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

$branchFilter = '';
$branchId = null;
$teacher = null;
$teacherId = null;

// Get teacher record if user is a teacher
if ($isTeacher && !$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if ($teacher) {
        $teacherId = $teacher['id'];
    }
}

$currentSession = getCurrentSession();

if ($isSuperAdmin) {
    $branchId = $_GET['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
} else {
    $branchId = $currentUser['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
}

// Get classes with section count (excluding graduated classes)
if ($isTeacher && !$isSuperAdmin && $teacherId && $currentSession) {
    // Teachers see only classes assigned to them (excluding graduated)
    $sql = "SELECT DISTINCT c.*, b.branch_name,
            (SELECT COUNT(*) FROM sections s WHERE s.class_id = c.id AND s.is_active = 1) as section_count,
            (SELECT COUNT(*) FROM students st WHERE st.current_class_id = c.id AND st.status = 'Active') as student_count
            FROM classes c 
            INNER JOIN class_subjects cs ON c.id = cs.class_id
            LEFT JOIN branches b ON c.branch_id = b.id 
            WHERE cs.teacher_id = ? AND cs.session_id = ? AND c.is_active = 1 
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            $branchFilter
            ORDER BY c.class_order, c.class_name";
    $stmt = executeQuery($sql, 'ii', [$teacherId, $currentSession['id']]);
    $classes = fetchAll($stmt);
} else {
    // Super Admin and Admin see all active classes (excluding graduated)
    $sql = "SELECT c.*, b.branch_name,
            (SELECT COUNT(*) FROM sections s WHERE s.class_id = c.id AND s.is_active = 1) as section_count,
            (SELECT COUNT(*) FROM students st WHERE st.current_class_id = c.id AND st.status = 'Active') as student_count
            FROM classes c 
            LEFT JOIN branches b ON c.branch_id = b.id 
            WHERE (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            $branchFilter
            ORDER BY c.class_order, c.class_name";
    $classes = fetchAll(executeQuery($sql));
}

// Get branches for filter and form
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
                        <?php if (!$isTeacher || $isSuperAdmin): ?>
                        <div class="page-title-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                <i class="ri-add-line"></i> Add Class
                            </button>
                        </div>
                        <?php endif; ?>
                        <h4 class="page-title">Classes Management</h4>
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

            <!-- Classes List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Classes (<?php echo count($classes); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Class Code</th>
                                            <th>Branch</th>
                                            <th>Sections</th>
                                            <th>Students</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($class['class_code']); ?></td>
                                            <td><?php echo htmlspecialchars($class['branch_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $class['section_count']; ?> Sections</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $class['student_count']; ?> Students</span>
                                            </td>
                                            <td>
                                                <?php if (isset($class['graduation_status']) && $class['graduation_status'] === 'Graduated'): ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="ri-graduation-cap-line"></i> Graduated
                                                    </span>
                                                <?php elseif ($class['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewClassDetails(<?php echo $class['id']; ?>)" 
                                                            class="btn btn-sm btn-primary" title="Class Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button onclick="viewSections(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['class_name']); ?>')" 
                                                            class="btn btn-sm btn-info" title="View Sections">
                                                        <i class="ri-list-check"></i>
                                                    </button>
                                                    <button onclick="viewClassSubjects(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['class_name']); ?>')" 
                                                            class="btn btn-sm btn-success" title="Class Subjects">
                                                        <i class="ri-book-open-line"></i>
                                                    </button>
                                                    <button onclick="viewClassAssignments(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['class_name']); ?>')" 
                                                            class="btn btn-sm btn-secondary" title="Assignments">
                                                        <i class="ri-user-settings-line"></i>
                                                    </button>
                                                    <?php if (!$isTeacher || $isSuperAdmin): ?>
                                                    <button onclick="editClass(<?php echo $class['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteClass(<?php echo $class['id']; ?>)" 
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

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addClassForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Class Name</label>
                        <input type="text" class="form-control" name="class_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Class Code</label>
                        <input type="text" class="form-control" name="class_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Branch</label>
                        <select class="form-select" name="branch_id" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['id']; ?>">
                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="class_order" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Save Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editClassForm">
                <input type="hidden" name="id" id="editClassId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Class Name</label>
                        <input type="text" class="form-control" name="class_name" id="editClassName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Class Code</label>
                        <input type="text" class="form-control" name="class_code" id="editClassCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Branch</label>
                        <select class="form-select" name="branch_id" id="editBranchId" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['id']; ?>">
                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="class_order" id="editClassOrder" value="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                            <label class="form-check-label" for="editIsActive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sections Modal -->
<div class="modal fade" id="sectionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionsModalTitle">Sections</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="showAddSection()">
                        <i class="ri-add-line"></i> Add Section
                    </button>
                </div>
                <div id="addSectionForm" style="display:none;" class="mb-3">
                    <form id="sectionForm" class="row g-2">
                        <input type="hidden" name="class_id" id="sectionClassId">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="section_name" placeholder="Section Name" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" name="capacity" placeholder="Capacity" value="40">
                        </div>
                        <div class="col-md-5">
                            <button type="submit" class="btn btn-success btn-sm me-2">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="hideAddSection()">Cancel</button>
                        </div>
                    </form>
                </div>
                <div id="sectionsList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Class Details Modal -->
<div class="modal fade" id="classDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classDetailsModalTitle">Class Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="classDetailsContent">
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

<!-- Class Subjects Modal -->
<div class="modal fade" id="classSubjectsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classSubjectsModalTitle">Class Subjects</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="showAddClassSubject()">
                        <i class="ri-add-line"></i> Add Subject
                    </button>
                </div>
                <div id="addClassSubjectForm" style="display:none;" class="mb-3 p-3 bg-light rounded">
                    <form id="classSubjectForm" class="row g-2">
                        <input type="hidden" name="class_id" id="classSubjectClassId">
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <select class="form-select" name="subject_id" id="classSubjectSelect" required>
                                <option value="">Select Subject</option>
                                <?php 
                                $allSubjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
                                $allSubjects = fetchAll(executeQuery($allSubjectsSql));
                                foreach ($allSubjects as $subj): ?>
                                    <option value="<?php echo $subj['id']; ?>">
                                        <?php echo htmlspecialchars($subj['subject_name'] . ' (' . $subj['subject_code'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teacher</label>
                            <select class="form-select" name="teacher_id" id="classSubjectTeacherSelect">
                                <option value="">Select Teacher (Optional)</option>
                                <?php 
                                $allTeachersSql = "SELECT s.id, s.staff_id, s.first_name, s.last_name 
                                                   FROM staff s 
                                                   WHERE s.status = 'Active' AND s.designation LIKE '%Teacher%'";
                                if (!hasRole(['Super Admin']) && $branchId) {
                                    $allTeachersSql .= " AND s.branch_id = $branchId";
                                }
                                $allTeachersSql .= " ORDER BY s.first_name";
                                $allTeachers = fetchAll(executeQuery($allTeachersSql));
                                foreach ($allTeachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>">
                                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-success btn-sm me-1">Save</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="hideAddClassSubject()">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="classSubjectsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Class Assignments Modal -->
<div class="modal fade" id="classAssignmentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classAssignmentsModalTitle">Class Assignments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <a href="<?php echo APP_URL; ?>modules/academics/assignments.php" class="btn btn-sm btn-primary" target="_blank">
                        <i class="ri-external-link-line"></i> Manage All Assignments
                    </a>
                </div>
                <div id="classAssignmentsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add class
$('#addClassForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-class.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addClassModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Edit class
function editClass(classId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-class.php',
        type: 'GET',
        data: { id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const classData = response.data;
                $('#editClassId').val(classData.id);
                $('#editClassName').val(classData.class_name);
                $('#editClassCode').val(classData.class_code);
                $('#editBranchId').val(classData.branch_id);
                $('#editDescription').val(classData.description || '');
                $('#editClassOrder').val(classData.class_order || 0);
                $('#editIsActive').prop('checked', classData.is_active == 1);
                $('#editClassModal').modal('show');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to load class details', 'error');
        }
    });
}

// Update class
$('#editClassForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/update-class.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#editClassModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// View class details
function viewClassDetails(classId) {
    $('#classDetailsModalTitle').text('Class Details');
    $('#classDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#classDetailsModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-class-details.php',
        type: 'GET',
        data: { id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const classInfo = data.class;
                
                let html = '<div class="row">';
                
                // Class Information
                html += '<div class="col-md-12 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-information-line"></i> Class Information</h5>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="30%">Class Name</th><td><strong>' + escapeHtml(classInfo.class_name) + '</strong></td></tr>';
                html += '<tr><th>Class Code</th><td>' + escapeHtml(classInfo.class_code) + '</td></tr>';
                html += '<tr><th>Branch</th><td>' + escapeHtml(classInfo.branch_name || 'N/A') + '</td></tr>';
                html += '<tr><th>Description</th><td>' + (classInfo.description ? escapeHtml(classInfo.description) : '<em>No description</em>') + '</td></tr>';
                html += '<tr><th>Display Order</th><td>' + (classInfo.class_order || 0) + '</td></tr>';
                html += '<tr><th>Status</th><td>' + (classInfo.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>') + '</td></tr>';
                html += '<tr><th>Total Students</th><td><span class="badge bg-primary">' + data.totalStudents + ' Students</span></td></tr>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                // Sections
                html += '<div class="col-md-6 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-list-check"></i> Sections (' + data.sections.length + ')</h5>';
                if (data.sections.length > 0) {
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>Section</th><th>Capacity</th><th>Students</th></tr></thead><tbody>';
                    data.sections.forEach(function(section) {
                        const studentCount = data.studentCountBySection[section.id] || 0;
                        html += '<tr>';
                        html += '<td>' + escapeHtml(section.section_name) + '</td>';
                        html += '<td>' + section.capacity + '</td>';
                        html += '<td><span class="badge bg-info">' + studentCount + '</span></td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '</div>';
                } else {
                    html += '<p class="text-muted">No sections assigned</p>';
                }
                html += '</div>';
                
                // Subjects
                html += '<div class="col-md-6 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-book-open-line"></i> Subjects (' + data.subjects.length + ')</h5>';
                if (data.subjects.length > 0) {
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>Subject</th><th>Code</th><th>Type</th><th>Teacher</th></tr></thead><tbody>';
                    data.subjects.forEach(function(subject) {
                        const teacherName = (subject.teacher_first_name && subject.teacher_last_name) 
                            ? escapeHtml(subject.teacher_first_name + ' ' + subject.teacher_last_name)
                            : '<em>Not assigned</em>';
                        html += '<tr>';
                        html += '<td>' + escapeHtml(subject.subject_name) + '</td>';
                        html += '<td>' + escapeHtml(subject.subject_code) + '</td>';
                        html += '<td><span class="badge bg-secondary">' + escapeHtml(subject.subject_type) + '</span></td>';
                        html += '<td>' + teacherName + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '</div>';
                } else {
                    html += '<p class="text-muted">No subjects assigned</p>';
                }
                html += '</div>';
                
                html += '</div>';
                
                $('#classDetailsContent').html(html);
            } else {
                $('#classDetailsContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#classDetailsContent').html('<div class="alert alert-danger">Failed to load class details</div>');
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

// View sections
function viewSections(classId, className) {
    $('#sectionsModalTitle').text('Sections for ' + className);
    $('#sectionClassId').val(classId);
    loadSections(classId);
    $('#sectionsModal').modal('show');
}

function loadSections(classId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/get-sections.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<table class="table table-sm">';
                html += '<thead><tr><th>Section Name</th><th>Capacity</th><th>Actions</th></tr></thead><tbody>';
                
                response.data.forEach(function(section) {
                    html += `<tr>
                        <td>${section.section_name}</td>
                        <td>${section.capacity}</td>
                        <td>
                            <button onclick="deleteSection(${section.id})" class="btn btn-sm btn-danger">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                $('#sectionsList').html(html);
            }
        }
    });
}

function showAddSection() {
    $('#addSectionForm').show();
}

function hideAddSection() {
    $('#addSectionForm').hide();
    $('#sectionForm')[0].reset();
}

// Add section
$('#sectionForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-section.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                hideAddSection();
                loadSections($('#sectionClassId').val());
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete class
function deleteClass(classId) {
    confirmAction('Are you sure you want to delete this class?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-class.php',
            type: 'POST',
            data: { id: classId },
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

// Delete section
function deleteSection(sectionId) {
    confirmAction('Are you sure you want to delete this section?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-section.php',
            type: 'POST',
            data: { id: sectionId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadSections($('#sectionClassId').val());
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}

// View class subjects
function viewClassSubjects(classId, className) {
    $('#classSubjectsModalTitle').text('Subjects for ' + className);
    $('#classSubjectClassId').val(classId);
    $('#classSubjectsList').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#classSubjectsModal').modal('show');
    loadClassSubjects(classId);
}

function loadClassSubjects(classId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-class-subjects.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-striped table-hover">';
                html += '<thead><tr><th>Subject</th><th>Code</th><th>Type</th><th>Teacher</th><th>Actions</th></tr></thead><tbody>';
                
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const teacherName = (item.teacher_first_name && item.teacher_last_name) 
                            ? escapeHtml(item.teacher_first_name + ' ' + item.teacher_last_name)
                            : '<em class="text-muted">Not assigned</em>';
                        html += `<tr>
                            <td><strong>${escapeHtml(item.subject_name)}</strong></td>
                            <td>${escapeHtml(item.subject_code)}</td>
                            <td><span class="badge bg-secondary">${escapeHtml(item.subject_type)}</span></td>
                            <td>${teacherName}</td>
                            <td>
                                <button onclick="deleteClassSubject(${item.id}, ${classId})" class="btn btn-sm btn-danger" title="Remove">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="5" class="text-center text-muted">No subjects assigned to this class</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#classSubjectsList').html(html);
            } else {
                $('#classSubjectsList').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#classSubjectsList').html('<div class="alert alert-danger">Failed to load subjects</div>');
        }
    });
}

function showAddClassSubject() {
    $('#addClassSubjectForm').show();
}

function hideAddClassSubject() {
    $('#addClassSubjectForm').hide();
    $('#classSubjectForm')[0].reset();
}

// Add class subject
$('#classSubjectForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-assignment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                hideAddClassSubject();
                loadClassSubjects($('#classSubjectClassId').val());
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

function deleteClassSubject(assignmentId, classId) {
    confirmAction('Are you sure you want to remove this subject from the class?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-assignment.php',
            type: 'POST',
            data: { id: assignmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadClassSubjects(classId);
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}

// View class assignments
function viewClassAssignments(classId, className) {
    $('#classAssignmentsModalTitle').text('Assignments for ' + className);
    $('#classAssignmentsList').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#classAssignmentsModal').modal('show');
    loadClassAssignments(classId);
}

function loadClassAssignments(classId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-class-assignments.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-striped table-hover">';
                html += '<thead><tr><th>Subject</th><th>Code</th><th>Type</th><th>Teacher</th><th>Staff ID</th><th>Actions</th></tr></thead><tbody>';
                
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const teacherName = (item.teacher_first_name && item.teacher_last_name) 
                            ? escapeHtml(item.teacher_first_name + ' ' + item.teacher_last_name)
                            : '<em class="text-muted">Not assigned</em>';
                        const staffId = item.staff_id ? escapeHtml(item.staff_id) : '-';
                        html += `<tr>
                            <td><strong>${escapeHtml(item.subject_name)}</strong></td>
                            <td>${escapeHtml(item.subject_code)}</td>
                            <td><span class="badge bg-secondary">${escapeHtml(item.subject_type)}</span></td>
                            <td>${teacherName}</td>
                            <td>${staffId}</td>
                            <td>
                                <button onclick="editAssignmentFromClass(${item.id})" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button onclick="deleteAssignmentFromClass(${item.id}, ${classId})" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="6" class="text-center text-muted">No assignments found for this class</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#classAssignmentsList').html(html);
            } else {
                $('#classAssignmentsList').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#classAssignmentsList').html('<div class="alert alert-danger">Failed to load assignments</div>');
        }
    });
}

function editAssignmentFromClass(assignmentId) {
    // Close current modal and open assignments page in new tab with filter
    $('#classAssignmentsModal').modal('hide');
    window.open('<?php echo APP_URL; ?>modules/academics/assignments.php', '_blank');
}

function deleteAssignmentFromClass(assignmentId, classId) {
    confirmAction('Are you sure you want to delete this assignment?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-assignment.php',
            type: 'POST',
            data: { id: assignmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadClassAssignments(classId);
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}
</script>

