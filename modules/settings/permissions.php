<?php
/**
 * Granular Permissions Management
 * 
 * Advanced role-by-action permission management system
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin']);

$pageTitle = 'Granular Permissions Management';

// Get roles
$rolesSql = "SELECT r.*, 
            (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
            FROM roles r
            ORDER BY r.role_name";
$roles = fetchAll(executeQuery($rolesSql));

// Get selected role
$selectedRoleId = $_GET['role_id'] ?? ($roles[0]['id'] ?? 0);

// Get all modules with actions
$modules = PermissionManager::getAllModulesWithActions();

// Get current role permissions
$rolePermissions = [];
if ($selectedRoleId) {
    $rolePermissions = PermissionManager::getRolePermissions($selectedRoleId);
}

// Get selected role info
$selectedRole = null;
if ($selectedRoleId) {
    foreach ($roles as $role) {
        if ($role['id'] == $selectedRoleId) {
            $selectedRole = $role;
            break;
        }
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
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/settings/roles.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Roles
                            </a>
                            <button type="button" class="btn btn-info" onclick="showAuditLog()">
                                <i class="ri-file-list-3-line"></i> View Audit Log
                            </button>
                        </div>
                        <h4 class="page-title">Granular Permissions Management</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Roles List -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Roles</h4>
                            
                            <div class="list-group">
                                <?php foreach ($roles as $role): ?>
                                <a href="?role_id=<?php echo $role['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo ($selectedRoleId == $role['id']) ? 'active' : ''; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($role['role_name']); ?></h5>
                                        <span class="badge bg-primary"><?php echo $role['user_count']; ?> users</span>
                                    </div>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($role['role_description'] ?? 'No description'); ?></p>
                                    <?php if ($role['is_system_role']): ?>
                                        <small class="text-muted"><i class="ri-shield-star-line"></i> System Role</small>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Permissions Matrix -->
                <div class="col-md-9">
                    <?php if ($selectedRoleId && $selectedRole): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">
                                    Permissions for: <strong><?php echo htmlspecialchars($selectedRole['role_name']); ?></strong>
                                </h4>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                        <i class="ri-checkbox-multiple-line"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                        <i class="ri-checkbox-blank-line"></i> Deselect All
                                    </button>
                                </div>
                            </div>
                            
                            <form id="permissionsForm">
                                <input type="hidden" name="role_id" value="<?php echo $selectedRoleId; ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 200px;">Module</th>
                                                <th>Create</th>
                                                <th>View</th>
                                                <th>Update</th>
                                                <th>Delete</th>
                                                <th>Approve</th>
                                                <th>Reject</th>
                                                <th>Export</th>
                                                <th>Print</th>
                                                <th>Import</th>
                                                <th>Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($modules as $module): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                                    <?php if ($module['module_description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($module['module_description']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <?php 
                                                // Define action order for display
                                                $actionOrder = ['create', 'view', 'update', 'delete', 'approve', 'reject', 'export', 'print', 'import', 'manage'];
                                                
                                                foreach ($actionOrder as $actionKey): 
                                                    // Find action in module
                                                    $action = null;
                                                    foreach ($module['actions'] as $act) {
                                                        if ($act['action_key'] === $actionKey) {
                                                            $action = $act;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    if ($action):
                                                        $checkboxId = "perm_{$module['module_key']}_{$actionKey}";
                                                        $isChecked = isset($rolePermissions[$module['module_key']][$actionKey]) && 
                                                                     $rolePermissions[$module['module_key']][$actionKey];
                                                ?>
                                                <td class="text-center">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               name="permissions[<?php echo $module['module_key']; ?>][<?php echo $actionKey; ?>]"
                                                               value="1"
                                                               id="<?php echo $checkboxId; ?>"
                                                               data-module="<?php echo $module['module_key']; ?>"
                                                               data-action="<?php echo $actionKey; ?>"
                                                               <?php echo $isChecked ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="<?php echo $checkboxId; ?>" title="<?php echo htmlspecialchars($action['action_description'] ?? ''); ?>">
                                                        </label>
                                                    </div>
                                                </td>
                                                <?php else: ?>
                                                <td class="text-center text-muted">-</td>
                                                <?php endif; endforeach; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                        <i class="ri-refresh-line"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-save-line"></i> Save Permissions
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line font-24"></i>
                                <h5 class="mt-2">Select a Role</h5>
                                <p class="mb-0">Please select a role from the list to manage its permissions.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Audit Log Modal -->
<div class="modal fade" id="auditLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Permission Audit Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="auditLogTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Changed By</th>
                                <th>Target</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Change Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
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
// Save permissions
$('#permissionsForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {};
    const roleId = $('input[name="role_id"]').val();
    
    // Collect all checked permissions
    $('.permission-checkbox:checked').each(function() {
        const module = $(this).data('module');
        const action = $(this).data('action');
        
        if (!formData[module]) {
            formData[module] = {};
        }
        formData[module][action] = true;
    });
    
    // Show loading
    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while permissions are being saved',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/settings/save-role-permissions.php',
        type: 'POST',
        data: {
            role_id: roleId,
            permissions: JSON.stringify(formData)
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while saving permissions.'
            });
        }
    });
});

// Select all checkboxes
function selectAll() {
    $('.permission-checkbox').prop('checked', true);
}

// Deselect all checkboxes
function deselectAll() {
    $('.permission-checkbox').prop('checked', false);
}

// Reset form to original state
function resetForm() {
    location.reload();
}

// Show audit log
function showAuditLog() {
    $('#auditLogModal').modal('show');
    
    // Load audit log data
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/settings/get-permission-audit-log.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const tbody = $('#auditLogTable tbody');
                tbody.empty();
                
                if (response.data.length === 0) {
                    tbody.append('<tr><td colspan="7" class="text-center">No audit log entries found.</td></tr>');
                } else {
                    response.data.forEach(function(entry) {
                        const row = `
                            <tr>
                                <td>${entry.created_at}</td>
                                <td>${entry.username || 'System'}</td>
                                <td><span class="badge bg-${entry.target_type === 'role' ? 'primary' : 'info'}">${entry.target_type}</span> #${entry.target_id}</td>
                                <td>${entry.module_name || '-'}</td>
                                <td>${entry.action_name || '-'}</td>
                                <td><span class="badge bg-${getChangeTypeColor(entry.change_type)}">${entry.change_type}</span></td>
                                <td>${entry.description || '-'}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
            }
        }
    });
}

function getChangeTypeColor(type) {
    const colors = {
        'grant': 'success',
        'revoke': 'danger',
        'override_grant': 'info',
        'override_revoke': 'warning',
        'override_remove': 'secondary'
    };
    return colors[type] || 'secondary';
}
</script>

