<?php
/**
 * Branches Management
 * 
 * Manage school branches/campuses
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Branches Management';

// Get all branches with statistics
$sql = "SELECT b.*, 
        (SELECT COUNT(*) FROM students s WHERE s.branch_id = b.id AND s.status = 'Active') as student_count,
        (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id AND st.status = 'Active') as staff_count,
        (SELECT COUNT(*) FROM classes c WHERE c.branch_id = b.id AND c.is_active = 1) as class_count
        FROM branches b 
        ORDER BY b.branch_name";

$branches = fetchAll(executeQuery($sql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                                <i class="ri-building-line"></i> Add Branch
                            </button>
                        </div>
                        <h4 class="page-title">Branches Management</h4>
                    </div>
                </div>
            </div>

            <!-- Branches Grid -->
            <div class="row">
                <?php foreach ($branches as $branch): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-lg">
                                        <div class="avatar-title bg-primary-lighten text-primary rounded">
                                            <i class="ri-building-4-line font-32"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1"><?php echo htmlspecialchars($branch['branch_name']); ?></h4>
                                    <p class="text-muted mb-0">
                                        <small>Code: <?php echo htmlspecialchars($branch['branch_code']); ?></small>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($branch['address']): ?>
                            <p class="mb-2">
                                <i class="ri-map-pin-line me-2"></i>
                                <?php echo htmlspecialchars($branch['address']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($branch['phone']): ?>
                            <p class="mb-2">
                                <i class="ri-phone-line me-2"></i>
                                <?php echo htmlspecialchars($branch['phone']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($branch['email']): ?>
                            <p class="mb-3">
                                <i class="ri-mail-line me-2"></i>
                                <?php echo htmlspecialchars($branch['email']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-primary mb-0"><?php echo $branch['student_count']; ?></h4>
                                    <small class="text-muted">Students</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success mb-0"><?php echo $branch['staff_count']; ?></h4>
                                    <small class="text-muted">Staff</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info mb-0"><?php echo $branch['class_count']; ?></h4>
                                    <small class="text-muted">Classes</small>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <?php if ($branch['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                                
                                <?php if ($branch['established_date']): ?>
                                    <small class="text-muted ms-2">
                                        Est. <?php echo date('Y', strtotime($branch['established_date'])); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (hasRole(['Super Admin'])): ?>
                            <div class="mt-3 d-flex justify-content-end gap-2">
                                <button onclick="viewBranchDetails(<?php echo $branch['id']; ?>)" 
                                        class="btn btn-sm btn-info" title="View Details">
                                    <i class="ri-eye-line"></i> View
                                </button>
                                <button onclick="toggleBranchStatus(<?php echo $branch['id']; ?>, <?php echo $branch['is_active'] ? 1 : 0; ?>)" 
                                        class="btn btn-sm btn-<?php echo $branch['is_active'] ? 'success' : 'danger'; ?>" 
                                        title="<?php echo $branch['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="ri-toggle-<?php echo $branch['is_active'] ? 'fill' : 'line'; ?>"></i>
                                </button>
                                <button onclick="editBranch(<?php echo $branch['id']; ?>)" 
                                        class="btn btn-sm btn-warning" title="Edit">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                <button onclick="deleteBranch(<?php echo $branch['id']; ?>)" 
                                        class="btn btn-sm btn-danger" title="Delete">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBranchForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Branch Name</label>
                        <input type="text" class="form-control" name="branch_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Branch Code</label>
                        <input type="text" class="form-control" name="branch_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Established Date</label>
                        <input type="date" class="form-control" name="established_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Branch Details Modal -->
<div class="modal fade" id="viewBranchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Branch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBranchContent">
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

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBranchForm">
                <input type="hidden" name="id" id="editBranchId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Branch Name</label>
                        <input type="text" class="form-control" name="branch_name" id="editBranchName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Branch Code</label>
                        <input type="text" class="form-control" name="branch_code" id="editBranchCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="editAddress" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="editPhone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Established Date</label>
                        <input type="date" class="form-control" name="established_date" id="editEstablishedDate">
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
                        <i class="ri-save-line"></i> Update Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add branch
$('#addBranchForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/branches/add-branch.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addBranchModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Edit branch - Load branch data
function editBranch(branchId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/branches/get-branch.php',
        type: 'GET',
        data: { id: branchId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const branchData = response.data;
                $('#editBranchId').val(branchData.id);
                $('#editBranchName').val(branchData.branch_name);
                $('#editBranchCode').val(branchData.branch_code);
                $('#editAddress').val(branchData.address || '');
                $('#editPhone').val(branchData.phone || '');
                $('#editEmail').val(branchData.email || '');
                $('#editEstablishedDate').val(branchData.established_date || '');
                $('#editIsActive').prop('checked', branchData.is_active == 1);
                $('#editBranchModal').modal('show');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to load branch details', 'error');
        }
    });
}

// Update branch
$('#editBranchForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/branches/edit-branch.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#editBranchModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete branch
function deleteBranch(branchId) {
    confirmAction('Are you sure you want to delete this branch? This action cannot be undone. Make sure there are no students, staff, or classes associated with this branch.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/branches/delete-branch.php',
            type: 'POST',
            data: { id: branchId },
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
            },
            error: function() {
                showToast('Failed to delete branch', 'error');
            }
        });
    });
}

// View branch details
function viewBranchDetails(branchId) {
    $('#viewBranchContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#viewBranchModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/branches/get-branch.php',
        type: 'GET',
        data: { id: branchId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const branch = response.data;
                
                // Get statistics
                $.ajax({
                    url: '<?php echo APP_URL; ?>ajax/branches/get-branch-stats.php',
                    type: 'GET',
                    data: { id: branchId },
                    dataType: 'json',
                    success: function(statsResponse) {
                        const stats = statsResponse.success ? statsResponse.data : { students: 0, staff: 0, classes: 0 };
                        displayBranchDetails(branch, stats);
                    },
                    error: function() {
                        displayBranchDetails(branch, { students: 0, staff: 0, classes: 0 });
                    }
                });
            } else {
                $('#viewBranchContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#viewBranchContent').html('<div class="alert alert-danger">Failed to load branch details</div>');
        }
    });
}

function displayBranchDetails(branch, stats) {
    let html = '<div class="row">';
    
    // Branch Information
    html += '<div class="col-md-12 mb-4">';
    html += '<h5 class="mb-3"><i class="ri-information-line"></i> Branch Information</h5>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-bordered">';
    html += '<tr><th width="30%">Branch Name</th><td><strong>' + escapeHtml(branch.branch_name) + '</strong></td></tr>';
    html += '<tr><th>Branch Code</th><td>' + escapeHtml(branch.branch_code) + '</td></tr>';
    html += '<tr><th>Status</th><td>' + (branch.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>') + '</td></tr>';
    if (branch.address) {
        html += '<tr><th>Address</th><td>' + escapeHtml(branch.address) + '</td></tr>';
    }
    if (branch.phone) {
        html += '<tr><th>Phone</th><td>' + escapeHtml(branch.phone) + '</td></tr>';
    }
    if (branch.email) {
        html += '<tr><th>Email</th><td>' + escapeHtml(branch.email) + '</td></tr>';
    }
    if (branch.established_date) {
        html += '<tr><th>Established Date</th><td>' + escapeHtml(branch.established_date) + '</td></tr>';
    }
    html += '</table>';
    html += '</div>';
    html += '</div>';
    
    // Statistics
    html += '<div class="col-md-12 mb-4">';
    html += '<h5 class="mb-3"><i class="ri-bar-chart-line"></i> Statistics</h5>';
    html += '<div class="row text-center">';
    html += '<div class="col-md-4">';
    html += '<div class="card border-primary">';
    html += '<div class="card-body">';
    html += '<h3 class="text-primary mb-0">' + stats.students + '</h3>';
    html += '<small class="text-muted">Students</small>';
    html += '</div></div></div>';
    html += '<div class="col-md-4">';
    html += '<div class="card border-success">';
    html += '<div class="card-body">';
    html += '<h3 class="text-success mb-0">' + stats.staff + '</h3>';
    html += '<small class="text-muted">Staff</small>';
    html += '</div></div></div>';
    html += '<div class="col-md-4">';
    html += '<div class="card border-info">';
    html += '<div class="card-body">';
    html += '<h3 class="text-info mb-0">' + stats.classes + '</h3>';
    html += '<small class="text-muted">Classes</small>';
    html += '</div></div></div>';
    html += '</div>';
    html += '</div>';
    
    html += '</div>';
    
    $('#viewBranchContent').html(html);
}

// Toggle branch status
function toggleBranchStatus(branchId, currentStatus) {
    const action = currentStatus == 1 ? 'deactivate' : 'activate';
    const actionText = currentStatus == 1 ? 'Deactivate' : 'Activate';
    
    confirmAction(`Are you sure you want to ${action} this branch?`, function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/branches/toggle-status.php',
            type: 'POST',
            data: { id: branchId },
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
            },
            error: function() {
                showToast('Failed to update branch status', 'error');
            }
        });
    }, {
        confirmText: `Yes, ${actionText}!`
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}
</script>

