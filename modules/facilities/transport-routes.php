<?php
/**
 * Transport Routes Management
 * 
 * Manage transport routes
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Transport Routes';

// Get current user
$currentUser = getCurrentUser();

// Get routes
$sql = "SELECT r.*, b.branch_name,
        (SELECT COUNT(*) FROM transport_assignments WHERE route_id = r.id AND status = 'Active') as assigned_students
        FROM transport_routes r
        LEFT JOIN branches b ON r.branch_id = b.id
        WHERE 1=1";

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND r.branch_id = {$currentUser['branch_id']}";
}

$sql .= " ORDER BY r.route_name";

$routes = fetchAll(executeQuery($sql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                                <i class="ri-add-line"></i> Add Route
                            </button>
                        </div>
                        <h4 class="page-title">Transport Routes</h4>
                    </div>
                </div>
            </div>

            <!-- Routes List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Routes</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Route Code</th>
                                            <th>Route Name</th>
                                            <th>Start Point</th>
                                            <th>End Point</th>
                                            <th>Distance</th>
                                            <th>Fare</th>
                                            <th>Assigned Students</th>
                                            <th>Branch</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($routes as $route): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($route['route_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($route['route_name']); ?></td>
                                            <td><?php echo htmlspecialchars($route['start_point']); ?></td>
                                            <td><?php echo htmlspecialchars($route['end_point']); ?></td>
                                            <td><?php echo $route['distance'] ? number_format($route['distance'], 2) . ' km' : 'N/A'; ?></td>
                                            <td><strong><?php echo $route['fare'] ? formatCurrency($route['fare']) : 'N/A'; ?></strong></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $route['assigned_students'] ?? 0; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($route['branch_name']); ?></td>
                                            <td>
                                                <button onclick="viewStops(<?php echo $route['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="View Stops">
                                                    <i class="ri-map-pin-line"></i>
                                                </button>
                                                <button onclick="editRoute(<?php echo $route['id']; ?>)" 
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button onclick="deleteRoute(<?php echo $route['id']; ?>)" 
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

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Transport Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRouteForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Route Code</label>
                        <input type="text" class="form-control" name="route_code" required placeholder="e.g., RT001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Route Name</label>
                        <input type="text" class="form-control" name="route_name" required placeholder="e.g., Downtown Route">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Point</label>
                            <input type="text" class="form-control" name="start_point" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Point</label>
                            <input type="text" class="form-control" name="end_point" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stops (comma-separated)</label>
                        <textarea class="form-control" name="stops" rows="3" placeholder="Stop 1, Stop 2, Stop 3..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" class="form-control" name="distance" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fare</label>
                            <input type="number" class="form-control" name="fare" step="0.01" min="0">
                        </div>
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
                        <i class="ri-save-line"></i> Add Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add route
$('#addRouteForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/add-route.php',
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

// View stops
function viewStops(id) {
    Swal.fire({
        icon: 'info',
        title: 'Route Stops',
        text: 'Stop details will be displayed here.'
    });
}

// Delete route
function deleteRoute(id) {
    Swal.fire({
        title: 'Delete Route?',
        text: 'This will also delete all assignments. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-route.php',
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

// Edit route (placeholder)
function editRoute(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

