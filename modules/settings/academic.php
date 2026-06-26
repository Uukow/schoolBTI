<?php
/**
 * Academic Settings
 * 
 * Configure academic sessions, terms, and academic settings
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Academic Settings';

// Get current session
$currentSession = getCurrentSession();

// Get all sessions
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

// Get current academic year settings
$academicSettingsSql = "SELECT * FROM system_settings LIMIT 1";
$academicSettings = fetchOne(executeQuery($academicSettingsSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                                <i class="ri-add-line"></i> Add Academic Session
                            </button>
                        </div>
                        <h4 class="page-title">Academic Settings</h4>
                    </div>
                </div>
            </div>

            <!-- Current Session Info -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Current Academic Session</h4>
                            <?php if ($currentSession): ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Session Name:</strong>
                                    <p class="mb-0"><?php echo htmlspecialchars($currentSession['session_name']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Start Date:</strong>
                                    <p class="mb-0"><?php echo formatDate($currentSession['start_date']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong>End Date:</strong>
                                    <p class="mb-0"><?php echo formatDate($currentSession['end_date']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-<?php echo $currentSession['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $currentSession['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="ri-alert-line"></i> No active academic session found. Please create and activate a session.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Sessions List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Academic Sessions</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Session Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                        <tr class="<?php echo $session['is_active'] ? 'table-success' : ''; ?>">
                                            <td><strong><?php echo htmlspecialchars($session['session_name']); ?></strong></td>
                                            <td><?php echo formatDate($session['start_date']); ?></td>
                                            <td><?php echo formatDate($session['end_date']); ?></td>
                                            <td>
                                                <?php if ($session['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$session['is_active']): ?>
                                                    <button onclick="activateSession(<?php echo $session['id']; ?>)" 
                                                            class="btn btn-sm btn-success" title="Activate">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="editSession(<?php echo $session['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <?php if (!$session['is_active']): ?>
                                                    <button onclick="deleteSession(<?php echo $session['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                <?php endif; ?>
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

            <!-- Academic Year Settings -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Academic Year Settings</h4>
                            
                            <form id="academicSettingsForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Academic Year Start Month</label>
                                        <select class="form-select" name="academic_year_start_month">
                                            <?php
                                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                                      'July', 'August', 'September', 'October', 'November', 'December'];
                                            $currentMonth = isset($academicSettings['academic_year_start_month']) ? (int)$academicSettings['academic_year_start_month'] : 1;
                                            for ($i = 1; $i <= 12; $i++):
                                            ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($currentMonth == $i) ? 'selected' : ''; ?>>
                                                    <?php echo $months[$i-1]; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Number of Terms per Year</label>
                                        <select class="form-select" name="terms_per_year">
                                            <?php
                                            $termsPerYear = isset($academicSettings['terms_per_year']) ? (int)$academicSettings['terms_per_year'] : 2;
                                            ?>
                                            <option value="2" <?php echo $termsPerYear == 2 ? 'selected' : ''; ?>>2 Terms</option>
                                            <option value="3" <?php echo $termsPerYear == 3 ? 'selected' : ''; ?>>3 Terms</option>
                                            <option value="4" <?php echo $termsPerYear == 4 ? 'selected' : ''; ?>>4 Terms</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line"></i> Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Academic Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSessionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Session Name</label>
                        <input type="text" class="form-control" name="session_name" required placeholder="e.g., 2024-2025">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive">
                            <label class="form-check-label" for="isActive">
                                Set as Active Session
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add session
$('#addSessionForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/settings/add-session.php',
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

// Activate session
function activateSession(id) {
    Swal.fire({
        title: 'Activate Session?',
        text: 'This will deactivate the current active session. Continue?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Activate!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/settings/activate-session.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Activated!',
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
        }
    });
}

// Delete session
function deleteSession(id) {
    Swal.fire({
        title: 'Delete Session?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/settings/delete-session.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
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
        }
    });
}

// Edit session (placeholder)
function editSession(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}

// Save academic settings
$('#academicSettingsForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/settings/save-academic-settings.php',
        type: 'POST',
        data: $(this).serialize(),
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

