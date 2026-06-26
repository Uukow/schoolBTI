<?php
/**
 * Transport Assignments
 * 
 * Assign students to transport routes and vehicles
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Transport Assignments';

// Get filters
$routeFilter = $_GET['route_id'] ?? '';
$vehicleFilter = $_GET['vehicle_id'] ?? '';
$statusFilter = $_GET['status'] ?? 'Active';

// Get routes
$routesSql = "SELECT * FROM transport_routes ORDER BY route_name";
$routes = fetchAll(executeQuery($routesSql));

// Get vehicles
$vehiclesSql = "SELECT * FROM transport_vehicles WHERE is_active = 1 ORDER BY vehicle_number";
$vehicles = fetchAll(executeQuery($vehiclesSql));

// Get assignments
$sql = "SELECT ta.*, r.route_name, r.route_code, v.vehicle_number, v.vehicle_model,
        s.student_id, s.first_name, s.last_name, c.class_name
        FROM transport_assignments ta
        INNER JOIN transport_routes r ON ta.route_id = r.id
        INNER JOIN transport_vehicles v ON ta.vehicle_id = v.id
        INNER JOIN students s ON ta.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($routeFilter)) {
    $sql .= " AND ta.route_id = ?";
    $params[] = $routeFilter;
    $types .= 'i';
}

if (!empty($vehicleFilter)) {
    $sql .= " AND ta.vehicle_id = ?";
    $params[] = $vehicleFilter;
    $types .= 'i';
}

if (!empty($statusFilter)) {
    $sql .= " AND ta.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

$sql .= " ORDER BY ta.assignment_date DESC";

$assignments = fetchAll(executeQuery($sql, $types, $params));

// Get available students (not assigned to transport)
$studentsSql = "SELECT s.*, c.class_name FROM students s 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                WHERE s.status = 'Active' 
                AND s.id NOT IN (SELECT student_id FROM transport_assignments WHERE status = 'Active')
                ORDER BY s.first_name";
$availableStudents = fetchAll(executeQuery($studentsSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                                <i class="ri-user-add-line"></i> Assign Student
                            </button>
                        </div>
                        <h4 class="page-title">Transport Assignments</h4>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Route</label>
                                    <select class="form-select" name="route_id">
                                        <option value="">All Routes</option>
                                        <?php foreach ($routes as $route): ?>
                                            <option value="<?php echo $route['id']; ?>" <?php echo ($routeFilter == $route['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($route['route_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Active" <?php echo ($statusFilter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($statusFilter == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Assignments (<?php echo count($assignments); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Route</th>
                                            <th>Vehicle</th>
                                            <th>Pickup Point</th>
                                            <th>Drop Point</th>
                                            <th>Assignment Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($assignment['student_id']); ?> | <?php echo htmlspecialchars($assignment['class_name']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($assignment['route_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($assignment['route_code']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($assignment['vehicle_number']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($assignment['vehicle_model'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($assignment['pickup_point'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['drop_point'] ?? '-'); ?></td>
                                            <td><?php echo formatDate($assignment['assignment_date']); ?></td>
                                            <td>
                                                <?php if ($assignment['status'] == 'Active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button onclick="toggleStatus(<?php echo $assignment['id']; ?>, '<?php echo $assignment['status']; ?>')" 
                                                        class="btn btn-sm btn-<?php echo $assignment['status'] == 'Active' ? 'warning' : 'success'; ?>" 
                                                        title="<?php echo $assignment['status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="ri-<?php echo $assignment['status'] == 'Active' ? 'pause' : 'play'; ?>-line"></i>
                                                </button>
                                                <button onclick="deleteAssignment(<?php echo $assignment['id']; ?>)" 
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

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Student to Transport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Select Student</label>
                        <select class="form-select" name="student_id" required>
                            <option value="">Choose Student</option>
                            <?php foreach ($availableStudents as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['class_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Select Route</label>
                        <select class="form-select" name="route_id" id="routeSelect" required>
                            <option value="">Choose Route</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['id']; ?>">
                                    <?php echo htmlspecialchars($route['route_name'] . ' (' . $route['route_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Select Vehicle</label>
                        <select class="form-select" name="vehicle_id" required>
                            <option value="">Choose Vehicle</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo htmlspecialchars($vehicle['vehicle_number'] . ' - ' . ($vehicle['vehicle_model'] ?? 'N/A')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pickup Point</label>
                            <input type="text" class="form-control" name="pickup_point" placeholder="Pickup location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Drop Point</label>
                            <input type="text" class="form-control" name="drop_point" placeholder="Drop location">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Assignment Date</label>
                        <input type="date" class="form-control" name="assignment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Assign student
$('#assignForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/assign-transport.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Assigned!',
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

// Toggle status
function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus == 'Active' ? 'Inactive' : 'Active';
    
    Swal.fire({
        title: `${newStatus == 'Active' ? 'Activate' : 'Deactivate'} Assignment?`,
        text: `Change assignment status to ${newStatus}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus == 'Active' ? '#28a745' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${newStatus == 'Active' ? 'Activate' : 'Deactivate'}!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/toggle-transport-status.php',
                type: 'POST',
                data: { id: id, status: newStatus },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
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

// Delete assignment
function deleteAssignment(id) {
    Swal.fire({
        title: 'Delete Assignment?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-transport-assignment.php',
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

