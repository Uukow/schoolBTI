<?php
/**
 * Transport Vehicles Management
 * 
 * Manage transport vehicles
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Transport Vehicles';

// Get current user
$currentUser = getCurrentUser();

// Get vehicles
$sql = "SELECT v.*, b.branch_name,
        (SELECT COUNT(*) FROM transport_assignments WHERE vehicle_id = v.id AND status = 'Active') as assigned_students
        FROM transport_vehicles v
        LEFT JOIN branches b ON v.branch_id = b.id
        WHERE 1=1";

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND v.branch_id = {$currentUser['branch_id']}";
}

$sql .= " ORDER BY v.vehicle_number";

$vehicles = fetchAll(executeQuery($sql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                                <i class="ri-add-line"></i> Add Vehicle
                            </button>
                        </div>
                        <h4 class="page-title">Transport Vehicles</h4>
                    </div>
                </div>
            </div>

            <!-- Vehicles List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Vehicles</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vehicle Number</th>
                                            <th>Model</th>
                                            <th>Type</th>
                                            <th>Capacity</th>
                                            <th>Driver</th>
                                            <th>Driver Phone</th>
                                            <th>Assigned Students</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($vehicle['vehicle_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($vehicle['vehicle_model'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($vehicle['vehicle_type'] ?? 'N/A'); ?></td>
                                            <td><?php echo $vehicle['capacity'] ?? 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($vehicle['driver_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($vehicle['driver_phone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($vehicle['driver_phone']); ?>">
                                                        <?php echo htmlspecialchars($vehicle['driver_phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $vehicle['assigned_students'] ?? 0; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($vehicle['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="transport-maintenance.php?vehicle_id=<?php echo $vehicle['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Maintenance">
                                                    <i class="ri-tools-line"></i>
                                                </a>
                                                <button onclick="editVehicle(<?php echo $vehicle['id']; ?>)" 
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" 
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

        </div>
    </div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addVehicleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Vehicle Number</label>
                        <input type="text" class="form-control" name="vehicle_number" required placeholder="e.g., ABC-123">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehicle Model</label>
                            <input type="text" class="form-control" name="vehicle_model" placeholder="e.g., Toyota Hiace">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehicle Type</label>
                            <input type="text" class="form-control" name="vehicle_type" placeholder="e.g., Bus, Van">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" min="1" placeholder="Number of seats">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Driver Name</label>
                        <input type="text" class="form-control" name="driver_name">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Driver Phone</label>
                            <input type="text" class="form-control" name="driver_phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Driver License</label>
                            <input type="text" class="form-control" name="driver_license">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Registration Date</label>
                        <input type="date" class="form-control" name="registration_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Insurance Details</label>
                        <textarea class="form-control" name="insurance_details" rows="2"></textarea>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add vehicle
$('#addVehicleForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/add-vehicle.php',
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

// Delete vehicle
function deleteVehicle(id) {
    Swal.fire({
        title: 'Delete Vehicle?',
        text: 'This will also delete all assignments and maintenance records. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-vehicle.php',
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

// Edit vehicle (placeholder)
function editVehicle(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

