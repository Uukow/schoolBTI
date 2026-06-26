<?php
/**
 * Hostel Rooms Management
 * 
 * Manage rooms in a hostel
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Hostel Rooms';

// Get hostel ID
$hostelId = (int)($_GET['hostel_id'] ?? 0);

if (empty($hostelId)) {
    $_SESSION['error'] = 'Invalid hostel ID';
    redirect(APP_URL . 'modules/facilities/hostels.php');
}

// Get hostel info
$sql = "SELECT h.*, b.branch_name FROM hostels h LEFT JOIN branches b ON h.branch_id = b.id WHERE h.id = ?";
$stmt = executeQuery($sql, 'i', [$hostelId]);
$hostel = fetchOne($stmt);

if (!$hostel) {
    $_SESSION['error'] = 'Hostel not found';
    redirect(APP_URL . 'modules/facilities/hostels.php');
}

// Get rooms
$sql = "SELECT r.*, 
        (SELECT COUNT(*) FROM hostel_allocations WHERE room_id = r.id AND status = 'Active') as current_occupants
        FROM hostel_rooms r
        WHERE r.hostel_id = ?
        ORDER BY r.room_number";
$rooms = fetchAll(executeQuery($sql, 'i', [$hostelId]));

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
                            <a href="<?php echo APP_URL; ?>modules/facilities/hostels.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Hostels
                            </a>
                            <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="ri-add-line"></i> Add Room
                            </button>
                        </div>
                        <h4 class="page-title">
                            Rooms - <?php echo htmlspecialchars($hostel['hostel_name']); ?>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Hostel Info -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Hostel Type:</strong> 
                                    <span class="badge bg-<?php echo $hostel['hostel_type'] == 'Boys' ? 'primary' : ($hostel['hostel_type'] == 'Girls' ? 'danger' : 'info'); ?>">
                                        <?php echo htmlspecialchars($hostel['hostel_type']); ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Branch:</strong> <?php echo htmlspecialchars($hostel['branch_name']); ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Warden:</strong> <?php echo htmlspecialchars($hostel['warden_name'] ?? 'N/A'); ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Rooms:</strong> <?php echo count($rooms); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rooms List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Rooms List</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Room Number</th>
                                            <th>Room Type</th>
                                            <th>Capacity</th>
                                            <th>Occupied</th>
                                            <th>Available</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rooms as $room): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($room['room_type'] ?? 'Standard'); ?></td>
                                            <td><?php echo $room['capacity']; ?></td>
                                            <td><?php echo $room['current_occupants'] ?? 0; ?></td>
                                            <td><?php echo $room['capacity'] - ($room['current_occupants'] ?? 0); ?></td>
                                            <td>
                                                <?php if ($room['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="hostel-allocations.php?room_id=<?php echo $room['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View Allocations">
                                                    <i class="ri-user-line"></i>
                                                </a>
                                                <button onclick="editRoom(<?php echo $room['id']; ?>)" 
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button onclick="deleteRoom(<?php echo $room['id']; ?>)" 
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

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRoomForm">
                <input type="hidden" name="hostel_id" value="<?php echo $hostelId; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Room Number</label>
                        <input type="text" class="form-control" name="room_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Type</label>
                        <input type="text" class="form-control" name="room_type" placeholder="e.g., Standard, Deluxe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Capacity</label>
                        <input type="number" class="form-control" name="capacity" value="2" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add room
$('#addRoomForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/add-room.php',
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

// Delete room
function deleteRoom(id) {
    Swal.fire({
        title: 'Delete Room?',
        text: 'This will also delete all allocations. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-room.php',
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

// Edit room (placeholder)
function editRoom(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

