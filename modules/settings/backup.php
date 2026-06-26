<?php
/**
 * Backup & Restore
 * 
 * Database backup and restore functionality
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin']);

$pageTitle = 'Backup & Restore';

// Get backup history
$backupsSql = "SELECT bh.*, u.username as created_by_name
               FROM backup_history bh
               LEFT JOIN users u ON bh.created_by = u.id
               ORDER BY bh.created_at DESC
               LIMIT 50";
$backups = fetchAll(executeQuery($backupsSql));

// Calculate total backup size
$totalSizeSql = "SELECT SUM(backup_size) as total_size FROM backup_history";
$totalSize = fetchOne(executeQuery($totalSizeSql))['total_size'] ?? 0;

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
                            <button type="button" class="btn btn-primary" onclick="createBackup()">
                                <i class="ri-download-cloud-line"></i> Create Backup
                            </button>
                        </div>
                        <h4 class="page-title">Backup & Restore</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-database-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Backups</h5>
                                    <h2 class="mb-0"><?php echo count($backups); ?></h2>
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
                                        <i class="ri-file-storage-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Size</h5>
                                    <h2 class="mb-0"><?php echo formatFileSize($totalSize); ?></h2>
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
                                        <i class="ri-check-double-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Successful</h5>
                                    <h2 class="mb-0">
                                        <?php 
                                        $successCount = count(array_filter($backups, function($b) {
                                            return $b['status'] == 'Success';
                                        }));
                                        echo $successCount;
                                        ?>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup History -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Backup History</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Backup Name</th>
                                            <th>Type</th>
                                            <th>Size</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($backup['backup_name']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $backup['backup_type'] == 'Full' ? 'primary' : 'info'; ?>">
                                                    <?php echo htmlspecialchars($backup['backup_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatFileSize($backup['backup_size'] ?? 0); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($backup['status']) {
                                                    case 'Success': $statusClass = 'success'; break;
                                                    case 'Failed': $statusClass = 'danger'; break;
                                                    case 'In Progress': $statusClass = 'warning'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($backup['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($backup['created_by_name'] ?? 'System'); ?></td>
                                            <td><?php echo formatDateTime($backup['created_at']); ?></td>
                                            <td>
                                                <?php if ($backup['status'] == 'Success' && file_exists(ABSPATH . 'backups/' . $backup['backup_path'])): ?>
                                                    <a href="<?php echo APP_URL; ?>ajax/settings/download-backup.php?id=<?php echo $backup['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Download">
                                                        <i class="ri-download-line"></i>
                                                    </a>
                                                    <button onclick="restoreBackup(<?php echo $backup['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Restore">
                                                        <i class="ri-refresh-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="deleteBackup(<?php echo $backup['id']; ?>)" 
                                                        class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
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

            <!-- Restore Backup -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Restore from File</h4>
                            
                            <form id="restoreForm" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label required">Select Backup File</label>
                                        <input type="file" class="form-control" name="backup_file" accept=".sql,.gz" required>
                                        <small class="text-muted">Supported formats: .sql, .sql.gz</small>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end mb-3">
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="ri-upload-cloud-line"></i> Restore Backup
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Create backup
function createBackup() {
    Swal.fire({
        title: 'Create Backup?',
        text: 'This will create a full database backup. This may take a few moments.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Create Backup!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Creating Backup...',
                text: 'Please wait while the backup is being created.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/settings/create-backup.php',
                type: 'POST',
                dataType: 'json',
                timeout: 300000, // 5 minutes timeout for large databases
                success: function(response) {
                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Backup Created!',
                            text: response.message || 'Backup created successfully!',
                            timer: 3000,
                            showConfirmButton: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: (response && response.message) ? response.message : 'Failed to create backup. Please try again.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Failed to create backup. ';
                    
                    if (status === 'timeout') {
                        errorMessage += 'The request timed out. The database might be too large.';
                    } else if (status === 'parsererror') {
                        errorMessage += 'Invalid response from server. Please check the server logs.';
                        // Try to parse the response anyway
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            errorMessage += ' Response: ' + xhr.responseText.substring(0, 100);
                        }
                    } else if (xhr.status === 0) {
                        errorMessage += 'No response from server. Please check your connection.';
                    } else if (xhr.status === 500) {
                        errorMessage += 'Server error. Please check the server logs.';
                    } else {
                        errorMessage += 'Error: ' + (error || status);
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Restore backup
function restoreBackup(id) {
    Swal.fire({
        title: 'Restore Backup?',
        text: 'This will restore the database from this backup. All current data will be replaced. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Restore!',
        input: 'text',
        inputLabel: 'Type "RESTORE" to confirm',
        inputPlaceholder: 'RESTORE',
        inputValidator: (value) => {
            if (value !== 'RESTORE') {
                return 'You must type RESTORE to confirm';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Restoring Backup...',
                text: 'Please wait while the backup is being restored.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/settings/restore-backup.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restored!',
                            text: response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'Reload Page'
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

// Restore from file
$('#restoreForm').on('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Restore from File?',
        text: 'This will restore the database from the uploaded file. All current data will be replaced. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Restore!',
        input: 'text',
        inputLabel: 'Type "RESTORE" to confirm',
        inputPlaceholder: 'RESTORE',
        inputValidator: (value) => {
            if (value !== 'RESTORE') {
                return 'You must type RESTORE to confirm';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Restoring Backup...',
                text: 'Please wait while the backup is being restored.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/settings/restore-from-file.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restored!',
                            text: response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'Reload Page'
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
});

// Delete backup
function deleteBackup(id) {
    Swal.fire({
        title: 'Delete Backup?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/settings/delete-backup.php',
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
</script>

