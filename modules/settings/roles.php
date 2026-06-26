<?php
/**
 * Roles & Permissions Management
 * 
 * Manage user roles and their permissions
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin']);

$pageTitle = 'Roles & Permissions';

// Get roles
$rolesSql = "SELECT r.*, 
            (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
            FROM roles r
            ORDER BY r.role_name";
$roles = fetchAll(executeQuery($rolesSql));

// Get all permissions
$permissionsSql = "SELECT * FROM permissions ORDER BY module, permission_name";
$permissions = fetchAll(executeQuery($permissionsSql));

// Group permissions by module
$permissionsByModule = [];
foreach ($permissions as $permission) {
    $module = $permission['module'];
    if (!isset($permissionsByModule[$module])) {
        $permissionsByModule[$module] = [];
    }
    $permissionsByModule[$module][] = $permission;
}

// Get role permissions for selected role
$selectedRoleId = $_GET['role_id'] ?? $roles[0]['id'] ?? 0;
$rolePermissions = [];
if ($selectedRoleId) {
    $rpSql = "SELECT permission_id FROM role_permissions WHERE role_id = ?";
    $rpStmt = executeQuery($rpSql, 'i', [$selectedRoleId]);
    $rpData = fetchAll($rpStmt);
    foreach ($rpData as $rp) {
        $rolePermissions[] = $rp['permission_id'];
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="ri-add-line"></i> Add Role
                            </button>
                        </div>
                        <h4 class="page-title">Roles & Permissions</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Roles List -->
                <div class="col-md-4">
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
                                    <p class="mb-1"><?php echo htmlspecialchars($role['role_description'] ?? 'No description'); ?></p>
                                    <?php if ($role['is_system_role']): ?>
                                        <small class="text-muted">System Role</small>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Permissions -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                Permissions for: 
                                <?php 
                                $selectedRole = array_filter($roles, function($r) use ($selectedRoleId) {
                                    return $r['id'] == $selectedRoleId;
                                });
                                $selectedRole = reset($selectedRole);
                                echo htmlspecialchars($selectedRole['role_name'] ?? 'Select Role');
                                ?>
                            </h4>
                            
                            <?php if ($selectedRoleId): ?>
                            <form id="permissionsForm">
                                <input type="hidden" name="role_id" value="<?php echo $selectedRoleId; ?>">
                                
                                <?php foreach ($permissionsByModule as $module => $modulePermissions): ?>
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><?php echo htmlspecialchars(ucfirst($module)); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($modulePermissions as $permission): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="<?php echo $permission['id']; ?>"
                                                   id="perm_<?php echo $permission['id']; ?>"
                                                   <?php echo in_array($permission['id'], $rolePermissions) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="perm_<?php echo $permission['id']; ?>">
                                                <strong><?php echo htmlspecialchars($permission['permission_name']); ?></strong>
                                                <?php if ($permission['description']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($permission['description']); ?></small>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-save-line"></i> Save Permissions
                                    </button>
                                </div>
                            </form>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line font-24"></i>
                                <h5 class="mt-2">Select a Role</h5>
                                <p class="mb-0">Please select a role from the list to manage its permissions.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRoleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Role Name</label>
                        <input type="text" class="form-control" name="role_name" required placeholder="e.g., Manager">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="role_description" rows="3" placeholder="Role description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add role
$('#addRoleForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/settings/add-role.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        }
    });
});

// Save permissions
$('#permissionsForm').on('submit', function(e) {
    e.preventDefault();
    
    const permissions = $('.permission-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/settings/save-permissions.php',
        type: 'POST',
        data: {
            role_id: $('input[name="role_id"]').val(),
            permissions: JSON.stringify(permissions)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
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
        }
    });
});
</script>

