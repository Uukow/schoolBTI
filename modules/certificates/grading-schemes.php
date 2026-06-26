<?php
/**
 * Grading Schemes Management
 * 
 * Configure grading scales and GPA calculation rules
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Grading Schemes';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];

// Get grading schemes
$sql = "SELECT gs.*, acs.session_name, b.branch_name,
        (SELECT COUNT(*) FROM grading_scale_items gsi WHERE gsi.grading_scheme_id = gs.id) as grade_count
        FROM grading_schemes gs
        LEFT JOIN academic_sessions acs ON gs.session_id = acs.id
        LEFT JOIN branches b ON gs.branch_id = b.id
        WHERE 1=1";

if ($branchId) {
    $sql .= " AND gs.branch_id = " . intval($branchId);
}
$sql .= " ORDER BY gs.is_default DESC, gs.created_at DESC";

$schemes = fetchAll(executeQuery($sql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get academic sessions
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSchemeModal">
                                <i class="ri-add-line"></i> Add Grading Scheme
                            </button>
                        </div>
                        <h4 class="page-title">Grading Schemes</h4>
                    </div>
                </div>
            </div>

            <?php if ($isSuperAdmin): ?>
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

            <!-- Info Card -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> 
                        <strong>About Grading Schemes:</strong> Define grading scales with letter grades, percentage ranges, and GPA points. 
                        You can create different schemes for different academic sessions or programs. One scheme can be set as default.
                    </div>
                </div>
            </div>

            <!-- Schemes List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Grading Schemes (<?php echo count($schemes); ?>)</h4>
                            
                            <?php if (empty($schemes)): ?>
                                <div class="alert alert-warning">
                                    <i class="ri-alert-line"></i> No grading schemes found. Create your first grading scheme to get started.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover datatable">
                                        <thead>
                                            <tr>
                                                <th>Scheme Name</th>
                                                <th>Branch</th>
                                                <th>Session</th>
                                                <th>Scale Type</th>
                                                <th>Max GPA</th>
                                                <th>Grade Levels</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schemes as $scheme): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($scheme['scheme_name']); ?></strong>
                                                    <?php if ($scheme['is_default']): ?>
                                                        <span class="badge bg-primary ms-1">Default</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($scheme['branch_name'] ?? 'All Branches'); ?></td>
                                                <td><?php echo htmlspecialchars($scheme['session_name'] ?? 'All Sessions'); ?></td>
                                                <td><span class="badge bg-info"><?php echo ucfirst($scheme['scale_type']); ?></span></td>
                                                <td><?php echo number_format($scheme['max_gpa'], 2); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo $scheme['grade_count']; ?> Grades</span></td>
                                                <td>
                                                    <?php if ($scheme['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button onclick="viewSchemeDetails(<?php echo $scheme['id']; ?>)" 
                                                            class="btn btn-sm btn-primary" title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button onclick="editScheme(<?php echo $scheme['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="toggleActiveStatus(<?php echo $scheme['id']; ?>, <?php echo $scheme['is_active'] ? 0 : 1; ?>)" 
                                                            class="btn btn-sm <?php echo $scheme['is_active'] ? 'btn-secondary' : 'btn-success'; ?>" 
                                                            title="<?php echo $scheme['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="ri-<?php echo $scheme['is_active'] ? 'pause-circle-line' : 'play-circle-line'; ?>"></i>
                                                    </button>
                                                    <?php if (!$scheme['is_default']): ?>
                                                    <button onclick="setAsDefault(<?php echo $scheme['id']; ?>)" 
                                                            class="btn btn-sm btn-info" title="Set as Default">
                                                        <i class="ri-star-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button onclick="deleteScheme(<?php echo $scheme['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
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

<!-- Add/Edit Scheme Modal -->
<div class="modal fade" id="addSchemeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="schemeModalTitle">Add Grading Scheme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSchemeForm">
                <input type="hidden" name="id" id="schemeId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Scheme Name</label>
                            <input type="text" class="form-control" name="scheme_name" required 
                                   placeholder="e.g., Standard 4.0 Scale">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Scale Type</label>
                            <select class="form-select" name="scale_type" required>
                                <option value="percentage">Percentage (0-100)</option>
                                <option value="gpa">GPA (0-4.0)</option>
                                <option value="letter">Letter Grade</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Max GPA</label>
                            <input type="number" step="0.01" class="form-control" name="max_gpa" value="4.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch</label>
                            <select class="form-select" name="branch_id">
                                <option value="">All Branches</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo $branch['id']; ?>">
                                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Academic Session</label>
                            <select class="form-select" name="session_id">
                                <option value="">All Sessions</option>
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?php echo $session['id']; ?>" <?php echo $session['is_active'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($session['session_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Percentage</label>
                            <input type="number" step="0.01" class="form-control" name="passing_percentage" value="50.00">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" 
                                      placeholder="Optional description of this grading scheme"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" id="isDefault" value="1">
                                <label class="form-check-label" for="isDefault">
                                    Set as Default Grading Scheme
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mb-3">Grade Scale Items</h5>
                    <p class="text-muted">Define the grade levels for this scheme. Add at least one grade.</p>
                    
                    <div id="gradeItemsContainer">
                        <div class="grade-item row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="grades[0][grade_letter]" placeholder="A+" required>
                                <small class="text-muted">Grade</small>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" class="form-control" name="grades[0][min_percentage]" placeholder="90" required>
                                <small class="text-muted">Min %</small>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" class="form-control" name="grades[0][max_percentage]" placeholder="100" required>
                                <small class="text-muted">Max %</small>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" class="form-control" name="grades[0][grade_point]" placeholder="4.00" required>
                                <small class="text-muted">GPA</small>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="grades[0][description]" placeholder="Outstanding">
                                <small class="text-muted">Description</small>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeGradeItem(this)">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-success" onclick="addGradeItem()">
                        <i class="ri-add-line"></i> Add Grade Level
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="schemeSubmitBtn">
                        <i class="ri-save-line"></i> Save Scheme
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Scheme Details Modal -->
<div class="modal fade" id="viewSchemeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSchemeModalTitle">Grading Scheme Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewSchemeContent">
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
let gradeItemIndex = 1;

// Add grade item
function addGradeItem() {
    const container = document.getElementById('gradeItemsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'grade-item row mb-2';
    newItem.innerHTML = `
        <div class="col-md-2">
            <input type="text" class="form-control" name="grades[${gradeItemIndex}][grade_letter]" placeholder="B+" required>
            <small class="text-muted">Grade</small>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" class="form-control" name="grades[${gradeItemIndex}][min_percentage]" placeholder="80" required>
            <small class="text-muted">Min %</small>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" class="form-control" name="grades[${gradeItemIndex}][max_percentage]" placeholder="89.99" required>
            <small class="text-muted">Max %</small>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" class="form-control" name="grades[${gradeItemIndex}][grade_point]" placeholder="3.50" required>
            <small class="text-muted">GPA</small>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="grades[${gradeItemIndex}][description]" placeholder="Excellent">
            <small class="text-muted">Description</small>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeGradeItem(this)">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `;
    container.appendChild(newItem);
    gradeItemIndex++;
}

function removeGradeItem(button) {
    const container = document.getElementById('gradeItemsContainer');
    if (container.children.length > 1) {
        button.closest('.grade-item').remove();
    } else {
        Swal.fire('Error', 'You must have at least one grade level', 'error');
    }
}

// Reset form when modal is closed
$('#addSchemeModal').on('hidden.bs.modal', function() {
    $('#addSchemeForm')[0].reset();
    $('#schemeId').val('');
    $('#schemeModalTitle').text('Add Grading Scheme');
    $('#schemeSubmitBtn').html('<i class="ri-save-line"></i> Save Scheme');
    $('#gradeItemsContainer').html(`
        <div class="grade-item row mb-2">
            <div class="col-md-2">
                <input type="text" class="form-control" name="grades[0][grade_letter]" placeholder="A+" required>
                <small class="text-muted">Grade</small>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control" name="grades[0][min_percentage]" placeholder="90" required>
                <small class="text-muted">Min %</small>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control" name="grades[0][max_percentage]" placeholder="100" required>
                <small class="text-muted">Max %</small>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control" name="grades[0][grade_point]" placeholder="4.00" required>
                <small class="text-muted">GPA</small>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="grades[0][description]" placeholder="Outstanding">
                <small class="text-muted">Description</small>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeGradeItem(this)">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>
    `);
    gradeItemIndex = 1;
});

// Edit scheme
function editScheme(schemeId) {
    $('#schemeModalTitle').text('Edit Grading Scheme');
    $('#schemeSubmitBtn').html('<i class="ri-save-line"></i> Update Scheme');
    $('#addSchemeModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/get-grading-scheme.php',
        type: 'GET',
        data: { id: schemeId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const scheme = response.data.scheme;
                const items = response.data.items;
                
                // Populate form fields
                $('#schemeId').val(scheme.id);
                $('input[name="scheme_name"]').val(scheme.scheme_name);
                $('select[name="scale_type"]').val(scheme.scale_type);
                $('input[name="max_gpa"]').val(scheme.max_gpa);
                $('select[name="branch_id"]').val(scheme.branch_id || '');
                $('select[name="session_id"]').val(scheme.session_id || '');
                $('input[name="passing_percentage"]').val(scheme.passing_percentage);
                $('textarea[name="description"]').val(scheme.description || '');
                $('input[name="is_default"]').prop('checked', scheme.is_default == 1);
                
                // Populate grade items
                let html = '';
                gradeItemIndex = 0;
                items.forEach(function(item, index) {
                    html += `
                        <div class="grade-item row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="grades[${index}][grade_letter]" value="${escapeHtml(item.grade_letter)}" required>
                                <small class="text-muted">Grade</small>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" class="form-control" name="grades[${index}][min_percentage]" value="${item.min_percentage}" required>
                                <small class="text-muted">Min %</small>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" class="form-control" name="grades[${index}][max_percentage]" value="${item.max_percentage}" required>
                                <small class="text-muted">Max %</small>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" class="form-control" name="grades[${index}][grade_point]" value="${item.grade_point}" required>
                                <small class="text-muted">GPA</small>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="grades[${index}][description]" value="${escapeHtml(item.description || '')}">
                                <small class="text-muted">Description</small>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeGradeItem(this)">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    gradeItemIndex = index + 1;
                });
                $('#gradeItemsContainer').html(html);
            } else {
                Swal.fire('Error', response.message, 'error');
                $('#addSchemeModal').modal('hide');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load grading scheme', 'error');
            $('#addSchemeModal').modal('hide');
        }
    });
}

// Add/Update scheme
$('#addSchemeForm').on('submit', function(e) {
    e.preventDefault();
    
    const schemeId = $('#schemeId').val();
    const url = schemeId 
        ? '<?php echo APP_URL; ?>ajax/certificates/update-grading-scheme.php'
        : '<?php echo APP_URL; ?>ajax/certificates/add-grading-scheme.php';
    
    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to save grading scheme', 'error');
        }
    });
});

// View scheme details
function viewSchemeDetails(schemeId) {
    $('#viewSchemeContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#viewSchemeModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/get-grading-scheme.php',
        type: 'GET',
        data: { id: schemeId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const scheme = response.data.scheme;
                const items = response.data.items;
                
                let html = '<div class="row mb-3">';
                html += '<div class="col-md-6">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="40%">Scheme Name</th><td><strong>' + escapeHtml(scheme.scheme_name) + '</strong></td></tr>';
                html += '<tr><th>Scale Type</th><td>' + escapeHtml(scheme.scale_type) + '</td></tr>';
                html += '<tr><th>Max GPA</th><td>' + scheme.max_gpa + '</td></tr>';
                html += '<tr><th>Passing %</th><td>' + scheme.passing_percentage + '%</td></tr>';
                html += '</table>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="40%">Branch</th><td>' + escapeHtml(scheme.branch_name || 'All Branches') + '</td></tr>';
                html += '<tr><th>Session</th><td>' + escapeHtml(scheme.session_name || 'All Sessions') + '</td></tr>';
                html += '<tr><th>Status</th><td>' + (scheme.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>') + '</td></tr>';
                html += '<tr><th>Default</th><td>' + (scheme.is_default == 1 ? '<span class="badge bg-primary">Yes</span>' : 'No') + '</td></tr>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                if (scheme.description) {
                    html += '<div class="alert alert-info">' + escapeHtml(scheme.description) + '</div>';
                }
                
                html += '<h5 class="mb-3">Grade Scale</h5>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-striped table-bordered">';
                html += '<thead><tr><th>Grade</th><th>Min %</th><th>Max %</th><th>GPA</th><th>Description</th></tr></thead>';
                html += '<tbody>';
                items.forEach(function(item) {
                    html += '<tr>';
                    html += '<td><strong>' + escapeHtml(item.grade_letter) + '</strong></td>';
                    html += '<td>' + item.min_percentage + '</td>';
                    html += '<td>' + item.max_percentage + '</td>';
                    html += '<td>' + item.grade_point + '</td>';
                    html += '<td>' + escapeHtml(item.description || '-') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                html += '</div>';
                
                $('#viewSchemeContent').html(html);
            } else {
                $('#viewSchemeContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#viewSchemeContent').html('<div class="alert alert-danger">Failed to load scheme details</div>');
        }
    });
}

// Toggle active status
function toggleActiveStatus(schemeId, newStatus) {
    const action = newStatus == 1 ? 'activate' : 'deactivate';
    const actionText = newStatus == 1 ? 'activate' : 'deactivate';
    
    Swal.fire({
        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Grading Scheme?`,
        text: `Are you sure you want to ${actionText} this grading scheme?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${actionText} it`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/certificates/toggle-scheme-status.php',
                type: 'POST',
                data: { 
                    id: schemeId,
                    is_active: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', `Failed to ${actionText} grading scheme`, 'error');
                }
            });
        }
    });
}

// Set as default
function setAsDefault(schemeId) {
    Swal.fire({
        title: 'Set as Default?',
        text: 'This will make this grading scheme the default for all certificates',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, set as default'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/certificates/set-default-scheme.php',
                type: 'POST',
                data: { id: schemeId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Delete scheme
function deleteScheme(schemeId) {
    Swal.fire({
        title: 'Delete Grading Scheme?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/certificates/delete-grading-scheme.php',
                type: 'POST',
                data: { id: schemeId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
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
</script>

