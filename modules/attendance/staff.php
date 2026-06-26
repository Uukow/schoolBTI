<?php
/**
 * Staff Attendance Management
 * 
 * Mark and manage staff attendance
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Staff Attendance';

// Get selected date
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get all active staff
$sql = "SELECT s.*, b.branch_name,
        (SELECT status FROM staff_attendance WHERE staff_id = s.id AND attendance_date = ? LIMIT 1) as attendance_status,
        (SELECT check_in FROM staff_attendance WHERE staff_id = s.id AND attendance_date = ? LIMIT 1) as check_in_time,
        (SELECT check_out FROM staff_attendance WHERE staff_id = s.id AND attendance_date = ? LIMIT 1) as check_out_time
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE s.status = 'Active'
        ORDER BY s.first_name, s.last_name";

$stmt = executeQuery($sql, 'sss', [$selectedDate, $selectedDate, $selectedDate]);
$staff = fetchAll($stmt);

// Get statistics
$statsSql = "SELECT 
    COUNT(DISTINCT staff_id) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
    SUM(CASE WHEN status = 'Leave' THEN 1 ELSE 0 END) as on_leave
    FROM staff_attendance 
    WHERE attendance_date = ?";

$statsStmt = executeQuery($statsSql, 's', [$selectedDate]);
$stats = fetchOne($statsStmt);

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
                            <button type="button" class="btn btn-success" onclick="markAllPresent()">
                                <i class="ri-checkbox-multiple-line"></i> Mark All Present
                            </button>
                        </div>
                        <h4 class="page-title">Staff Attendance</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-user-settings-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Staff</h5>
                                    <h2 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Present</h5>
                                    <h2 class="mb-0"><?php echo $stats['present'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-close-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Absent</h5>
                                    <h2 class="mb-0"><?php echo $stats['absent'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-shield-check-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">On Leave</h5>
                                    <h2 class="mb-0"><?php echo $stats['on_leave'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Selection -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Attendance Date</label>
                                    <input type="date" class="form-control" name="date" value="<?php echo $selectedDate; ?>" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Load Attendance
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                Mark Attendance - <?php echo formatDate($selectedDate); ?>
                                <span class="badge bg-info ms-2"><?php echo count($staff); ?> Staff Members</span>
                            </h4>
                            
                            <form id="staffAttendanceForm">
                                <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="12%">Staff ID</th>
                                                <th width="20%">Name</th>
                                                <th width="15%">Designation</th>
                                                <th width="10%">Check In</th>
                                                <th width="10%">Check Out</th>
                                                <th width="20%">Status</th>
                                                <th width="8%">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($staff as $index => $member): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($member['staff_id']); ?></strong>
                                                    <input type="hidden" name="staff[<?php echo $member['id']; ?>][id]" value="<?php echo $member['id']; ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo htmlspecialchars($member['designation']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" 
                                                           name="staff[<?php echo $member['id']; ?>][check_in]"
                                                           value="<?php echo $member['check_in_time'] ?? ''; ?>">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" 
                                                           name="staff[<?php echo $member['id']; ?>][check_out]"
                                                           value="<?php echo $member['check_out_time'] ?? ''; ?>">
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="staff[<?php echo $member['id']; ?>][status]" required>
                                                        <option value="Present" <?php echo ($member['attendance_status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                                        <option value="Absent" <?php echo ($member['attendance_status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                                        <option value="Late" <?php echo ($member['attendance_status'] == 'Late') ? 'selected' : ''; ?>>Late</option>
                                                        <option value="Half Day" <?php echo ($member['attendance_status'] == 'Half Day') ? 'selected' : ''; ?>>Half Day</option>
                                                        <option value="Leave" <?php echo ($member['attendance_status'] == 'Leave') ? 'selected' : ''; ?>>Leave</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="staff[<?php echo $member['id']; ?>][remarks]" 
                                                           placeholder="Note">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-save-line"></i> Save Attendance
                                    </button>
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
// Mark all present
function markAllPresent() {
    $('select[name*="[status]"]').val('Present');
    const now = new Date();
    const time = now.toTimeString().slice(0, 5);
    $('input[name*="[check_in]"]').val(time);
    showToast('All staff marked as present with current time', 'success');
}

// Save attendance
$('#staffAttendanceForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/attendance/save-staff-attendance.php',
        type: 'POST',
        data: $(this).serialize(),
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
</script>

