<?php
/**
 * LAB Management - Guest/Visitor Management
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Lab Visitors';
$currentUser = getCurrentUser();

$branchCond = labBranchWhere('v', null, false);

$sql = "SELECT v.*, s.section_name, h.username as host_username, sec.username as security_name
        FROM lab_visitors v
        LEFT JOIN lab_sections s ON v.section_id = s.id
        LEFT JOIN users h ON v.host_id = h.id
        LEFT JOIN users sec ON v.security_approved_by = sec.id
        WHERE 1=1" . $branchCond . " ORDER BY v.created_at DESC";

$visitors = fetchAll(executeQuery($sql));

$base = "SELECT COUNT(*) as c FROM lab_visitors WHERE 1=1" . labBranchWhere('', null, false);
$sCheckedIn  = fetchOne(executeQuery($base . " AND status='checked_in'"))['c'] ?? 0;
$sCheckedOut = fetchOne(executeQuery($base . " AND status='checked_out'"))['c'] ?? 0;
$sToday      = fetchOne(executeQuery($base . " AND DATE(created_at) = CURDATE()"))['c'] ?? 0;

$sections  = fetchAll(executeQuery("SELECT id, section_name FROM lab_sections ORDER BY section_name"));
$staffList = fetchAll(executeQuery("SELECT id, username FROM users WHERE is_active=1 ORDER BY username"));

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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVisitorModal">
                                <i class="ri-user-add-line"></i> Register Visitor
                            </button>
                        </div>
                        <h4 class="page-title">Laboratory Visitor Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Today\'s Visitors','primary','ri-calendar-2-line',$sToday],
                    ['Currently Inside','success','ri-user-location-line',$sCheckedIn],
                    ['Checked Out','secondary','ri-logout-box-line',$sCheckedOut],
                    ['Total Records','info','ri-file-list-3-line',$sCheckedIn+$sCheckedOut],
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

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Visitor Log</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr><th>Name</th><th>ID #</th><th>Organization</th><th>Purpose</th><th>Section</th><th>Host</th><th>Entry</th><th>Exit</th><th>Pass</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visitors as $vis): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($vis['visitor_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($vis['visitor_id_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($vis['organization'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($vis['purpose'] ?? 'N/A', 0, 40)); ?>...</td>
                                    <td><?php echo htmlspecialchars($vis['section_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($vis['host_name'] ?? $vis['host_username'] ?? 'N/A'); ?></td>
                                    <td><?php echo $vis['entry_time'] ? date('d/m H:i', strtotime($vis['entry_time'])) : 'Pending'; ?></td>
                                    <td><?php echo $vis['exit_time'] ? date('d/m H:i', strtotime($vis['exit_time'])) : '-'; ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($vis['visitor_pass'] ?? 'N/A'); ?></span></td>
                                    <td>
                                        <?php $sc=['checked_in'=>'success','checked_out'=>'secondary','expected'=>'warning']; ?>
                                        <span class="badge bg-<?php echo $sc[$vis['status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$vis['status'])); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($vis['status'] === 'checked_in'): ?>
                                        <button onclick="checkOut(<?php echo $vis['id']; ?>)" class="btn btn-sm btn-warning" title="Check Out"><i class="ri-logout-box-line"></i></button>
                                        <?php endif; ?>
                                        <?php if ($vis['status'] === 'expected'): ?>
                                        <button onclick="checkIn(<?php echo $vis['id']; ?>)" class="btn btn-sm btn-success" title="Check In"><i class="ri-login-box-line"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($visitors)): ?><tr><td colspan="11" class="text-center text-muted">No visitors recorded</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Register Visitor Modal -->
<div class="modal fade" id="addVisitorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Register Laboratory Visitor</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addVisitorForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Visitor Name</label><input type="text" class="form-control" name="visitor_name" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">ID Number / Passport</label><input type="text" class="form-control" name="visitor_id_number"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Organization</label><input type="text" class="form-control" name="organization"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Contact Number</label><input type="text" class="form-control" name="contact_number"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Host / Contact Person</label>
                            <select class="form-select" name="host_id">
                                <option value="">Select Host</option>
                                <?php foreach ($staffList as $st): ?><option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Host Name (if not in system)</label><input type="text" class="form-control" name="host_name"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Lab Section to Visit</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Expected Entry Time</label><input type="datetime-local" class="form-control" name="entry_time" value="<?php echo date('Y-m-d\TH:i'); ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Purpose of Visit</label><textarea class="form-control" name="purpose" rows="3" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Additional Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="check_in_now" value="1" id="checkInNow">
                            <label class="form-check-label" for="checkInNow">Check in immediately (visitor is present now)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-user-add-line"></i> Register</button></div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addVisitorForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-visitor.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addVisitorModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function checkIn(id) {
    confirmAction('Check in this visitor?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/check-in-visitor.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}

function checkOut(id) {
    confirmAction('Check out this visitor?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/check-out-visitor.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
