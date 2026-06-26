<?php
/**
 * Hostels Management
 * 
 * Manage hostel buildings
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Hostels Management';

// Get current user
$currentUser = getCurrentUser();

// Get hostels
$sql = "SELECT h.*, b.branch_name,
        (SELECT COUNT(*) FROM hostel_rooms WHERE hostel_id = h.id) as total_rooms,
        (SELECT SUM(occupied) FROM hostel_rooms WHERE hostel_id = h.id) as total_occupied,
        (SELECT SUM(capacity) FROM hostel_rooms WHERE hostel_id = h.id) as total_capacity
        FROM hostels h
        LEFT JOIN branches b ON h.branch_id = b.id
        WHERE 1=1";

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND h.branch_id = {$currentUser['branch_id']}";
}

$sql .= " ORDER BY h.hostel_name";

$hostels = fetchAll(executeQuery($sql));

// Get branches
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHostelModal">
                                <i class="ri-add-line"></i> Add Hostel
                            </button>
                        </div>
                        <h4 class="page-title">Hostels Management</h4>
                    </div>
                </div>
            </div>

            <!-- Hostels List -->
            <div class="row">
                <?php foreach ($hostels as $hostel): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($hostel['hostel_name']); ?></h5>
                                    <p class="text-muted mb-0">
                                        <span class="badge bg-<?php echo $hostel['hostel_type'] == 'Boys' ? 'primary' : ($hostel['hostel_type'] == 'Girls' ? 'danger' : 'info'); ?>">
                                            <?php echo htmlspecialchars($hostel['hostel_type']); ?>
                                        </span>
                                        <span class="ms-2"><?php echo htmlspecialchars($hostel['branch_name']); ?></span>
                                    </p>
                                </div>
                                <div>
                                    <button onclick="editHostel(<?php echo $hostel['id']; ?>)" class="btn btn-sm btn-info" title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button onclick="deleteHostel(<?php echo $hostel['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Warden:</small>
                                    <p class="mb-0"><?php echo htmlspecialchars($hostel['warden_name'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Phone:</small>
                                    <p class="mb-0">
                                        <?php if ($hostel['warden_phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($hostel['warden_phone']); ?>">
                                                <?php echo htmlspecialchars($hostel['warden_phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($hostel['address']): ?>
                            <div class="mb-3">
                                <small class="text-muted">Address:</small>
                                <p class="mb-0"><?php echo htmlspecialchars($hostel['address']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-4">
                                    <div class="text-center">
                                        <h4 class="mb-0"><?php echo $hostel['total_rooms'] ?? 0; ?></h4>
                                        <small class="text-muted">Rooms</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <h4 class="mb-0"><?php echo $hostel['total_occupied'] ?? 0; ?></h4>
                                        <small class="text-muted">Occupied</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <h4 class="mb-0"><?php echo $hostel['total_capacity'] ?? 0; ?></h4>
                                        <small class="text-muted">Capacity</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="hostel-rooms.php?hostel_id=<?php echo $hostel['id']; ?>" class="btn btn-sm btn-primary w-100">
                                    <i class="ri-door-open-line"></i> Manage Rooms
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

<!-- Add Hostel Modal -->
<div class="modal fade" id="addHostelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Hostel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addHostelForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Hostel Name</label>
                        <input type="text" class="form-control" name="hostel_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Hostel Type</label>
                        <select class="form-select" name="hostel_type" required>
                            <option value="">Select Type</option>
                            <option value="Boys">Boys</option>
                            <option value="Girls">Girls</option>
                            <option value="Mixed">Mixed</option>
                        </select>
                    </div>
                    <?php if (hasRole(['Super Admin'])): ?>
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
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warden Name</label>
                        <input type="text" class="form-control" name="warden_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warden Phone</label>
                        <input type="text" class="form-control" name="warden_phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Hostel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add hostel
$('#addHostelForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/add-hostel.php',
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

// Delete hostel
function deleteHostel(id) {
    Swal.fire({
        title: 'Delete Hostel?',
        text: 'This will also delete all rooms and allocations. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-hostel.php',
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

// Edit hostel (placeholder)
function editHostel(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

