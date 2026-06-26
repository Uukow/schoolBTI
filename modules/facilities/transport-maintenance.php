<?php
/**
 * Vehicle Maintenance
 * 
 * Manage vehicle maintenance records
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Vehicle Maintenance';

// Get filters
$vehicleFilter = $_GET['vehicle_id'] ?? '';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Get vehicles
$vehiclesSql = "SELECT * FROM transport_vehicles WHERE is_active = 1 ORDER BY vehicle_number";
$vehicles = fetchAll(executeQuery($vehiclesSql));

// Get maintenance records
$sql = "SELECT m.*, v.vehicle_number, v.vehicle_model,
        u.username as recorded_by_name
        FROM vehicle_maintenance m
        INNER JOIN transport_vehicles v ON m.vehicle_id = v.id
        LEFT JOIN users u ON m.recorded_by = u.id
        WHERE m.maintenance_date BETWEEN ? AND ?";

$params = [$startDate, $endDate];
$types = 'ss';

if (!empty($vehicleFilter)) {
    $sql .= " AND m.vehicle_id = ?";
    $params[] = $vehicleFilter;
    $types .= 'i';
}

$sql .= " ORDER BY m.maintenance_date DESC";

$maintenance = fetchAll(executeQuery($sql, $types, $params));

// Calculate statistics
$statsSql = "SELECT 
    COUNT(*) as total_records,
    SUM(cost) as total_cost
    FROM vehicle_maintenance
    WHERE maintenance_date BETWEEN ? AND ?";

$statsParams = [$startDate, $endDate];
$statsTypes = 'ss';

if (!empty($vehicleFilter)) {
    $statsSql .= " AND vehicle_id = ?";
    $statsParams[] = $vehicleFilter;
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                                <i class="ri-add-line"></i> Add Maintenance
                            </button>
                        </div>
                        <h4 class="page-title">Vehicle Maintenance</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-file-list-3-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Records</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_records'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Cost</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_cost'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Vehicle</label>
                                    <select class="form-select" name="vehicle_id">
                                        <option value="">All Vehicles</option>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>" <?php echo ($vehicleFilter == $vehicle['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($vehicle['vehicle_number']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Maintenance Records</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Maintenance Type</th>
                                            <th>Date</th>
                                            <th>Cost</th>
                                            <th>Performed By</th>
                                            <th>Next Maintenance</th>
                                            <th>Description</th>
                                            <th>Recorded By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenance as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($record['vehicle_number']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($record['vehicle_model'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['maintenance_type']); ?></td>
                                            <td><?php echo formatDate($record['maintenance_date']); ?></td>
                                            <td><strong><?php echo formatCurrency($record['cost'] ?? 0); ?></strong></td>
                                            <td><?php echo htmlspecialchars($record['performed_by'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($record['next_maintenance_date']): ?>
                                                    <?php 
                                                    $nextDate = new DateTime($record['next_maintenance_date']);
                                                    $today = new DateTime();
                                                    $daysLeft = $today->diff($nextDate)->days;
                                                    $badgeClass = $daysLeft <= 7 ? 'danger' : ($daysLeft <= 30 ? 'warning' : 'success');
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>">
                                                        <?php echo formatDate($record['next_maintenance_date']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['description'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($record['recorded_by_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <button onclick="editMaintenance(<?php echo $record['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button onclick="deleteMaintenance(<?php echo $record['id']; ?>)" 
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

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Maintenance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMaintenanceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Vehicle</label>
                        <select class="form-select" name="vehicle_id" required>
                            <option value="">Choose Vehicle</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo htmlspecialchars($vehicle['vehicle_number'] . ' - ' . ($vehicle['vehicle_model'] ?? 'N/A')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Maintenance Type</label>
                        <input type="text" class="form-control" name="maintenance_type" required placeholder="e.g., Oil Change, Tire Replacement">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Maintenance Date</label>
                        <input type="date" class="form-control" name="maintenance_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cost</label>
                        <input type="number" class="form-control" name="cost" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Performed By</label>
                        <input type="text" class="form-control" name="performed_by" placeholder="Mechanic/Service center name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Maintenance Date</label>
                        <input type="date" class="form-control" name="next_maintenance_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Maintenance details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add maintenance
$('#addMaintenanceForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/add-maintenance.php',
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

// Delete maintenance
function deleteMaintenance(id) {
    Swal.fire({
        title: 'Delete Maintenance Record?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-maintenance.php',
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

// Edit maintenance (placeholder)
function editMaintenance(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

