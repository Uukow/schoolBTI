<?php
/**
 * Hostel Allocations
 * 
 * Allocate students to hostel rooms
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Hostel Allocations';

// Get filters
$hostelId = $_GET['hostel_id'] ?? '';
$roomId = $_GET['room_id'] ?? '';
$statusFilter = $_GET['status'] ?? 'Active';

// Get hostels
$hostelsSql = "SELECT * FROM hostels ORDER BY hostel_name";
$hostels = fetchAll(executeQuery($hostelsSql));

// Get allocations
$sql = "SELECT ha.*, h.hostel_name, hr.room_number, hr.room_type,
        s.student_id, s.first_name, s.last_name, c.class_name
        FROM hostel_allocations ha
        INNER JOIN hostels h ON ha.hostel_id = h.id
        INNER JOIN hostel_rooms hr ON ha.room_id = hr.id
        INNER JOIN students s ON ha.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($hostelId)) {
    $sql .= " AND ha.hostel_id = ?";
    $params[] = $hostelId;
    $types .= 'i';
}

if (!empty($roomId)) {
    $sql .= " AND ha.room_id = ?";
    $params[] = $roomId;
    $types .= 'i';
}

if (!empty($statusFilter)) {
    $sql .= " AND ha.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

$sql .= " ORDER BY ha.allocation_date DESC";

$allocations = fetchAll(executeQuery($sql, $types, $params));

// Get available rooms for allocation
$roomsSql = "SELECT hr.*, h.hostel_name 
             FROM hostel_rooms hr
             INNER JOIN hostels h ON hr.hostel_id = h.id
             WHERE hr.is_active = 1
             AND hr.capacity > (SELECT COUNT(*) FROM hostel_allocations WHERE room_id = hr.id AND status = 'Active')
             ORDER BY h.hostel_name, hr.room_number";
$availableRooms = fetchAll(executeQuery($roomsSql));

// Get active students
$studentsSql = "SELECT s.*, c.class_name FROM students s 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                WHERE s.status = 'Active' 
                AND s.id NOT IN (SELECT student_id FROM hostel_allocations WHERE status = 'Active')
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal">
                                <i class="ri-user-add-line"></i> Allocate Student
                            </button>
                        </div>
                        <h4 class="page-title">Hostel Allocations</h4>
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
                                    <label class="form-label">Hostel</label>
                                    <select class="form-select" name="hostel_id">
                                        <option value="">All Hostels</option>
                                        <?php foreach ($hostels as $hostel): ?>
                                            <option value="<?php echo $hostel['id']; ?>" <?php echo ($hostelId == $hostel['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($hostel['hostel_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Active" <?php echo ($statusFilter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Vacated" <?php echo ($statusFilter == 'Vacated') ? 'selected' : ''; ?>>Vacated</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Allocations List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Allocations (<?php echo count($allocations); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Hostel</th>
                                            <th>Room</th>
                                            <th>Bed Number</th>
                                            <th>Allocation Date</th>
                                            <th>Vacation Date</th>
                                            <th>Fee Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allocations as $allocation): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($allocation['first_name'] . ' ' . $allocation['last_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($allocation['student_id']); ?> | <?php echo htmlspecialchars($allocation['class_name']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($allocation['hostel_name']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($allocation['room_number']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($allocation['room_type'] ?? 'Standard'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($allocation['bed_number'] ?? '-'); ?></td>
                                            <td><?php echo formatDate($allocation['allocation_date']); ?></td>
                                            <td><?php echo $allocation['vacation_date'] ? formatDate($allocation['vacation_date']) : '-'; ?></td>
                                            <td><?php echo $allocation['fee_amount'] ? formatCurrency($allocation['fee_amount']) : '-'; ?></td>
                                            <td>
                                                <?php if ($allocation['status'] == 'Active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Vacated</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($allocation['status'] == 'Active'): ?>
                                                    <button onclick="vacateRoom(<?php echo $allocation['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Vacate">
                                                        <i class="ri-logout-box-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="deleteAllocation(<?php echo $allocation['id']; ?>)" 
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

<!-- Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Allocate Student to Hostel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="allocateForm">
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
                        <label class="form-label required">Select Room</label>
                        <select class="form-select" name="room_id" id="roomSelect" required>
                            <option value="">Choose Room</option>
                            <?php foreach ($availableRooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>" 
                                        data-hostel="<?php echo $room['hostel_id']; ?>"
                                        data-available="<?php echo $room['capacity'] - ($room['occupied'] ?? 0); ?>">
                                    <?php echo htmlspecialchars($room['hostel_name'] . ' - Room ' . $room['room_number'] . ' (Available: ' . ($room['capacity'] - ($room['occupied'] ?? 0)) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bed Number</label>
                        <input type="text" class="form-control" name="bed_number" placeholder="e.g., A1, B2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Allocation Date</label>
                        <input type="date" class="form-control" name="allocation_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Fee</label>
                        <input type="number" class="form-control" name="fee_amount" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Allocate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Allocate student
$('#allocateForm').on('submit', function(e) {
    e.preventDefault();
    
    const roomSelect = $('#roomSelect');
    const available = roomSelect.find(':selected').data('available');
    
    if (available <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Room Full',
            text: 'This room is already full.'
        });
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/facilities/allocate-hostel.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Allocated!',
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

// Vacate room
function vacateRoom(id) {
    Swal.fire({
        title: 'Vacate Room?',
        text: 'Mark this allocation as vacated?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Vacate!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/vacate-hostel.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Vacated!',
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

// Delete allocation
function deleteAllocation(id) {
    Swal.fire({
        title: 'Delete Allocation?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/facilities/delete-allocation.php',
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

