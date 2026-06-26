<?php
/**
 * LAB Management - Laboratory Booking & Scheduling
 */

require_once '../../config/config.php';
requireLabRoles(labParticipantRoles());

$pageTitle = 'Lab Bookings';
$currentUser = getCurrentUser();

$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date']   ?? '';

$branchCond = labBranchWhere('b', null, false);

$sql = "SELECT b.*, s.section_name, e.experiment_title, r.username as requester_user, a.username as approver_name
        FROM lab_bookings b
        LEFT JOIN lab_sections s ON b.section_id = s.id
        LEFT JOIN lab_experiments e ON b.experiment_id = e.id
        LEFT JOIN users r ON b.requester_id = r.id
        LEFT JOIN users a ON b.approved_by = a.id
        WHERE 1=1" . $branchCond;
$params = []; $types = '';

if (!hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])) {
    $sql .= " AND b.requester_id = ?"; $params[] = $currentUser['id']; $types .= 'i';
}
if (!empty($statusFilter)) { $sql .= " AND b.status = ?"; $params[] = $statusFilter; $types .= 's'; }
if (!empty($dateFilter))   { $sql .= " AND b.booking_date = ?"; $params[] = $dateFilter; $types .= 's'; }
$sql .= " ORDER BY b.booking_date DESC, b.start_time";

$bookings = fetchAll(executeQuery($sql, $types ?: null, $params ?: null));

$base = "SELECT COUNT(*) as c FROM lab_bookings WHERE 1=1" . labBranchWhere('', null, false);
$sPending   = fetchOne(executeQuery($base . " AND status='pending'"))['c'] ?? 0;
$sApproved  = fetchOne(executeQuery($base . " AND status='approved' AND booking_date >= CURDATE()"))['c'] ?? 0;
$sCompleted = fetchOne(executeQuery($base . " AND status='completed'"))['c'] ?? 0;
$sToday     = fetchOne(executeQuery($base . " AND booking_date = CURDATE()"))['c'] ?? 0;

$sections    = fetchAll(executeQuery("SELECT id, section_name, capacity FROM lab_sections WHERE status='active' ORDER BY section_name"));
$experiments = fetchAll(executeQuery("SELECT id, experiment_title FROM lab_experiments WHERE status='active' ORDER BY experiment_title"));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <a href="dashboard.php" class="btn btn-secondary me-1"><i class="ri-arrow-left-line"></i> Dashboard</a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookingModal">
                                <i class="ri-calendar-add-line"></i> Book Laboratory
                            </button>
                        </div>
                        <h4 class="page-title">Laboratory Booking &amp; Scheduling</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Pending','warning','ri-time-line',$sPending],
                    ['Today\'s Bookings','primary','ri-calendar-2-line',$sToday],
                    ['Upcoming Approved','success','ri-calendar-check-line',$sApproved],
                    ['Completed','secondary','ri-check-double-line',$sCompleted],
                ] as [$label,$color,$icon,$val]): ?>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-<?php echo $color; ?>-lighten text-<?php echo $color; ?>"><i class="<?php echo $icon; ?> font-24"></i></div>
                                <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted"><?php echo $label; ?></h5><h2 class="mb-0"><?php echo $val; ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <?php foreach (['pending','approved','rejected','completed','cancelled'] as $s): ?><option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($dateFilter); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="bookings.php" class="btn btn-secondary ms-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Bookings (<?php echo count($bookings); ?>)</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr><th>Booking #</th><th>Section</th><th>Requester</th><th>Date</th><th>Time</th><th>Purpose</th><th>Attendees</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $bk): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($bk['booking_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($bk['section_name']); ?></td>
                                    <td><?php echo htmlspecialchars($bk['requester_name'] ?? $bk['requester_user'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $d = strtotime($bk['booking_date']);
                                        $today = strtotime(date('Y-m-d'));
                                        $cls = $d === $today ? 'text-primary fw-bold' : ($d < $today ? 'text-muted' : '');
                                        ?>
                                        <span class="<?php echo $cls; ?>"><?php echo formatDate($bk['booking_date']); ?></span>
                                    </td>
                                    <td><?php echo substr($bk['start_time'],0,5); ?> - <?php echo substr($bk['end_time'],0,5); ?></td>
                                    <td><?php echo htmlspecialchars(substr($bk['purpose'] ?? '', 0, 40)); ?>...</td>
                                    <td><?php echo $bk['attendees_count']; ?></td>
                                    <td>
                                        <?php $sc=['pending'=>'warning','approved'=>'success','rejected'=>'danger','completed'=>'secondary','cancelled'=>'dark']; ?>
                                        <span class="badge bg-<?php echo $sc[$bk['status']] ?? 'secondary'; ?>"><?php echo ucfirst($bk['status']); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewBooking(<?php echo $bk['id']; ?>)" class="btn btn-sm btn-info"><i class="ri-eye-line"></i></button>
                                            <?php if ($bk['status'] === 'pending' && hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])): ?>
                                            <button onclick="updateBooking(<?php echo $bk['id']; ?>,'approved')" class="btn btn-sm btn-success" title="Approve"><i class="ri-check-line"></i></button>
                                            <button onclick="updateBooking(<?php echo $bk['id']; ?>,'rejected')" class="btn btn-sm btn-danger" title="Reject"><i class="ri-close-line"></i></button>
                                            <?php endif; ?>
                                            <?php if ($bk['status'] === 'approved' && hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])): ?>
                                            <button onclick="updateBooking(<?php echo $bk['id']; ?>,'completed')" class="btn btn-sm btn-secondary" title="Complete"><i class="ri-check-double-line"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($bookings)): ?><tr><td colspan="9" class="text-center text-muted">No bookings found</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Booking Modal -->
<div class="modal fade" id="addBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Book Laboratory</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addBookingForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Laboratory Section</label>
                            <select class="form-select" name="section_id" required>
                                <option value="">Select Lab</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?> (Cap: <?php echo $s['capacity']; ?>)</option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Related Experiment</label>
                            <select class="form-select" name="experiment_id">
                                <option value="">None</option>
                                <?php foreach ($experiments as $exp): ?><option value="<?php echo $exp['id']; ?>"><?php echo htmlspecialchars($exp['experiment_title']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label required">Date</label><input type="date" class="form-control" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label required">Start Time</label><input type="time" class="form-control" name="start_time" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label required">End Time</label><input type="time" class="form-control" name="end_time" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Number of Attendees</label><input type="number" class="form-control" name="attendees_count" min="1" value="1"></div>
                        <div class="col-md-8 mb-3"><label class="form-label">Requester Name (if not self)</label><input type="text" class="form-control" name="requester_name"></div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Purpose</label><textarea class="form-control" name="purpose" rows="3" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Equipment Needed</label><textarea class="form-control" name="equipment_needed" rows="2" placeholder="List any special equipment requirements"></textarea></div>
                    <div class="mb-3"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-calendar-check-line"></i> Submit Booking</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Booking Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewBookingBody"></div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addBookingForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-booking.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addBookingModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewBooking(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-booking.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewBookingBody').html(`
                    <table class="table table-bordered table-sm">
                        <tr><th>Booking #</th><td>${d.booking_number}</td><th>Status</th><td>${d.status}</td></tr>
                        <tr><th>Section</th><td>${d.section_name}</td><th>Date</th><td>${d.booking_date}</td></tr>
                        <tr><th>Time</th><td>${d.start_time} - ${d.end_time}</td><th>Attendees</th><td>${d.attendees_count}</td></tr>
                        <tr><th>Requester</th><td>${d.requester_name||d.requester_user||'N/A'}</td><th>Experiment</th><td>${d.experiment_title||'N/A'}</td></tr>
                        <tr><th colspan="4">Purpose</th></tr>
                        <tr><td colspan="4">${d.purpose||'N/A'}</td></tr>
                        <tr><th colspan="4">Equipment Needed</th></tr>
                        <tr><td colspan="4">${d.equipment_needed||'N/A'}</td></tr>
                        <tr><th colspan="4">Notes</th></tr>
                        <tr><td colspan="4">${d.notes||'N/A'}</td></tr>
                    </table>`);
                $('#viewBookingModal').modal('show');
            }
        }
    });
}

function updateBooking(id, status) {
    var msg = status==='approved' ? 'Approve this booking?' : (status==='rejected' ? 'Reject this booking?' : 'Mark as completed?');
    confirmAction(msg, function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/update-booking.php', type:'POST', data:{id:id, status:status}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
