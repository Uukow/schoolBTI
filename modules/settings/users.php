<?php
/**
 * User Management
 * 
 * Manage system users and their roles
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'User Management';

// Get filters
$search       = trim($_GET['search'] ?? '');
$roleFilter   = $_GET['role_id'] ?? '';
$branchFilter = $_GET['branch_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';   // 1 = Active, 0 = Inactive
$verifiedFilter = $_GET['verified'] ?? ''; // 1 = Verified, 0 = Not Verified

// Build users query with filters
$sql = "SELECT u.*, r.role_name, b.branch_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN branches b ON u.branch_id = b.id
        WHERE 1=1";

$params = [];
$types  = '';

if (!empty($roleFilter)) {
    $sql      .= " AND u.role_id = ?";
    $params[] = (int) $roleFilter;
    $types    .= 'i';
}

if (!empty($branchFilter)) {
    $sql      .= " AND u.branch_id = ?";
    $params[] = (int) $branchFilter;
    $types    .= 'i';
}

if ($statusFilter !== '' && in_array($statusFilter, ['0', '1'], true)) {
    $sql      .= " AND u.is_active = ?";
    $params[] = (int) $statusFilter;
    $types    .= 'i';
}

if ($verifiedFilter !== '' && in_array($verifiedFilter, ['0', '1'], true)) {
    $sql      .= " AND u.is_verified = ?";
    $params[] = (int) $verifiedFilter;
    $types    .= 'i';
}

if (!empty($search)) {
    $sql      .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $searchLike = '%' . $search . '%';
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types    .= 'ss';
}

$sql .= " ORDER BY u.created_at DESC";

$stmt  = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$users = fetchAll($stmt);

// Get roles
$rolesSql = "SELECT * FROM roles ORDER BY role_name";
$roles = fetchAll(executeQuery($rolesSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get staff records for linking
$staffSql = "SELECT s.id, s.staff_id, s.first_name, s.last_name, s.email, s.user_id, s.designation
             FROM staff s
             WHERE s.status = 'Active'
             ORDER BY s.first_name, s.last_name";
$staffList = fetchAll(executeQuery($staffSql));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified
    FROM users";
$statsResult = executeQuery($statsSql);
$stats = fetchOne($statsResult);

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="ri-user-add-line"></i> Add User
                            </button>
                        </div>
                        <h4 class="page-title">User Management</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-user-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Users</h5>
                                    <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Active</h5>
                                    <h2 class="mb-0"><?php echo $stats['active']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-shield-check-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Verified</h5>
                                    <h2 class="mb-0"><?php echo $stats['verified']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           placeholder="Username or Email"
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Role</label>
                                    <select name="role_id" class="form-select">
                                        <option value="">All Roles</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['id']; ?>"
                                                <?php echo ($roleFilter == $role['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role['role_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Branch</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>"
                                                <?php echo ($branchFilter == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All</option>
                                        <option value="1" <?php echo ($statusFilter === '1') ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo ($statusFilter === '0') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Verified</label>
                                    <select name="verified" class="form-select">
                                        <option value="">All</option>
                                        <option value="1" <?php echo ($verifiedFilter === '1') ? 'selected' : ''; ?>>Verified</option>
                                        <option value="0" <?php echo ($verifiedFilter === '0') ? 'selected' : ''; ?>>Not Verified</option>
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-filter-line"></i>
                                        </button>
                                        <a href="users.php" class="btn btn-secondary" title="Reset filters">
                                            <i class="ri-refresh-line"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Users (<?php echo count($users); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Branch</th>
                                            <th>Last Login</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['branch_name'] ?? 'All Branches'); ?></td>
                                            <td><?php echo $user['last_login'] ? formatDate($user['last_login'], 'd M Y H:i') : 'Never'; ?></td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                                <?php if ($user['is_verified']): ?>
                                                    <i class="ri-verified-badge-line text-success" title="Verified"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 align-items-center">
                                                    <?php if ($user['id'] != getCurrentUser()['id']): ?>
                                                    <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active']; ?>)" 
                                                            class="btn btn-sm btn-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>" 
                                                            title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="ri-toggle-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button onclick="resetPassword(<?php echo $user['id']; ?>)" 
                                                            class="btn btn-sm btn-info" 
                                                            title="Reset Password">
                                                        <i class="ri-lock-unlock-line"></i>
                                                        <span class="d-none d-md-inline ms-1">Reset</span>
                                                    </button>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ri-more-2-line"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="editUser(<?php echo $user['id']; ?>)">
                                                                    <i class="ri-edit-line me-2"></i>
                                                                    Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                                                    <i class="ri-eye-line me-2"></i>
                                                                    View Details
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if ($user['id'] != getCurrentUser()['id']): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active']; ?>)">
                                                                    <i class="ri-toggle-line me-2"></i>
                                                                    <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="resetPassword(<?php echo $user['id']; ?>)">
                                                                    <i class="ri-lock-unlock-line me-2"></i>
                                                                    Reset Password
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="verifyUser(<?php echo $user['id']; ?>, <?php echo $user['is_verified'] ? 0 : 1; ?>)">
                                                                    <i class="ri-verified-badge-line me-2"></i>
                                                                    <?php echo $user['is_verified'] ? 'Unverify' : 'Verify'; ?>
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                                    <i class="ri-delete-bin-line me-2"></i>
                                                                    Delete
                                                                </a>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Password</label>
                        <input type="password" class="form-control" name="password" minlength="8" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Role</label>
                        <select class="form-select" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="edit_password" minlength="8">
                        <small class="text-muted">Leave blank to keep current password. Minimum 8 characters if changing.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Role</label>
                        <select class="form-select" name="role_id" id="edit_role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="branch_id" id="edit_branch_id">
                            <option value="">All Branches</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['id']; ?>">
                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link to Staff</label>
                        <select class="form-select" name="staff_id" id="edit_staff_id">
                            <option value="">No Staff Link</option>
                            <?php foreach ($staffList as $staff): ?>
                                <option value="<?php echo $staff['id']; ?>" data-user-id="<?php echo $staff['user_id'] ?? ''; ?>">
                                    <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name'] . ' (' . $staff['staff_id'] . ')'); ?>
                                    <?php if ($staff['user_id']): ?>
                                        (Already linked to User ID: <?php echo $staff['user_id']; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Select a staff record to link this user account to. This is required for teachers to see their lesson plans.</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Details Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="ri-user-line me-2"></i> User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn" style="display: none;">
                    <i class="ri-edit-line"></i> Edit User
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add user
$('#addUserForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/users/add-user.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addUserModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Toggle user status
function toggleUserStatus(userId, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    confirmAction(`Are you sure you want to ${action} this user?`, function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/users/toggle-status.php',
            type: 'POST',
            data: { user_id: userId },
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

// Reset password
function resetPassword(userId) {
    confirmAction('Reset password for this user? A new password will be generated and emailed to them.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/users/reset-password.php',
            type: 'POST',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}

// View user details
function viewUserDetails(userId) {
    $('#viewUserModal').modal('show');
    $('#userDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/users/get-user.php',
        type: 'POST',
        data: { user_id: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const user = response.data;
                let html = `
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="text-center">
                                <div class="avatar-lg mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: bold;">
                                    ${user.username.charAt(0).toUpperCase()}
                                </div>
                                <h4 class="mb-1">${escapeHtml(user.username)}</h4>
                                <p class="text-muted mb-0">${escapeHtml(user.email)}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-user-line me-2"></i> Username</h6>
                                    <p class="mb-0"><strong>${escapeHtml(user.username)}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-mail-line me-2"></i> Email</h6>
                                    <p class="mb-0"><strong>${escapeHtml(user.email)}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-shield-user-line me-2"></i> Role</h6>
                                    <p class="mb-0">
                                        <span class="badge bg-primary">${escapeHtml(user.role_name || 'N/A')}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-building-line me-2"></i> Branch</h6>
                                    <p class="mb-0"><strong>${escapeHtml(user.branch_name || 'All Branches')}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-checkbox-circle-line me-2"></i> Status</h6>
                                    <p class="mb-0">
                                        ${user.is_active == 1 ? 
                                            '<span class="badge bg-success">Active</span>' : 
                                            '<span class="badge bg-danger">Inactive</span>'}
                                        ${user.is_verified == 1 ? 
                                            '<i class="ri-verified-badge-line text-success ms-2" title="Verified"></i>' : 
                                            '<i class="ri-close-circle-line text-muted ms-2" title="Not Verified"></i>'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-time-line me-2"></i> Last Login</h6>
                                    <p class="mb-0">
                                        <strong>${user.last_login ? formatDateTime(user.last_login) : 'Never'}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-calendar-line me-2"></i> Created At</h6>
                                    <p class="mb-0"><strong>${user.created_at ? formatDateTime(user.created_at) : 'N/A'}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="ri-edit-line me-2"></i> Updated At</h6>
                                    <p class="mb-0"><strong>${user.updated_at ? formatDateTime(user.updated_at) : 'N/A'}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#userDetailsContent').html(html);
                $('#editFromViewBtn').show().off('click').on('click', function() {
                    $('#viewUserModal').modal('hide');
                    setTimeout(function() {
                        editUser(userId);
                    }, 300);
                });
            } else {
                $('#userDetailsContent').html('<div class="alert alert-danger">' + escapeHtml(response.message) + '</div>');
            }
        },
        error: function() {
            $('#userDetailsContent').html('<div class="alert alert-danger">Failed to load user details</div>');
        }
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
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Helper function to format datetime
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Edit user
function editUser(userId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/users/get-user.php',
        type: 'POST',
        data: { user_id: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const user = response.data;
                $('#edit_user_id').val(user.id);
                $('#edit_username').val(user.username);
                $('#edit_email').val(user.email);
                $('#edit_password').val('');
                $('#edit_role_id').val(user.role_id);
                $('#edit_branch_id').val(user.branch_id || '');
                $('#edit_staff_id').val(user.staff_id || '');
                $('#edit_is_active').prop('checked', user.is_active == 1);
                $('#editUserModal').modal('show');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to load user data', 'error');
        }
    });
}

// Update user
$('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/users/update-user.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#editUserModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete user
function deleteUser(userId) {
    confirmAction('Are you sure you want to delete this user? This action cannot be undone.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/users/delete-user.php',
            type: 'POST',
            data: { user_id: userId },
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
                showToast('Failed to delete user', 'error');
            }
        });
    });
}

// Verify/Unverify user
function verifyUser(userId, action) {
    const actionText = action ? 'verify' : 'unverify';
    confirmAction(`Are you sure you want to ${actionText} this user?`, function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/users/toggle-verify.php',
            type: 'POST',
            data: { user_id: userId, is_verified: action },
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
</script>

