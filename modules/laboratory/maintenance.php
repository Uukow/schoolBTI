<?php
/**
 * LAB Management - Equipment Maintenance Management
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Equipment Maintenance';
$currentUser = getCurrentUser();

$typeFilter   = $_GET['type']   ?? '';
$statusFilter = $_GET['status'] ?? '';

$branchCond = labBranchWhere('m', null, false);

$sql = "SELECT m.*, i.item_title, i.item_code, s.section_name, t.username as technician_name, u.username as reporter_name
        FROM lab_maintenance_records m
        LEFT JOIN lab_inventory_items i ON m.item_id = i.id
        LEFT JOIN lab_sections s ON m.section_id = s.id
        LEFT JOIN users t ON m.assigned_technician = t.id
        LEFT JOIN users u ON m.reported_by = u.id
        WHERE 1=1" . $branchCond;
$params = []; $types = '';

if (!empty($typeFilter))   { $sql .= " AND m.maintenance_type = ?"; $params[] = $typeFilter;   $types .= 's'; }
if (!empty($statusFilter)) { $sql .= " AND m.status = ?";           $params[] = $statusFilter; $types .= 's'; }
$sql .= " ORDER BY m.created_at DESC";

$records = fetchAll(executeQuery($sql, $types ?: null, $params ?: null));

// Stats
$base = "SELECT COUNT(*) as c FROM lab_maintenance_records WHERE 1=1" . labBranchWhere('', null, false);
$sTotal  = fetchOne(executeQuery($base))['c'] ?? 0;
$sOpen   = fetchOne(executeQuery($base . " AND status IN('reported','assigned','in_progress')"))['c'] ?? 0;
$sDone   = fetchOne(executeQuery($base . " AND status='completed'"))['c'] ?? 0;
$sPrev   = fetchOne(executeQuery($base . " AND maintenance_type='preventive'"))['c'] ?? 0;

$items     = fetchAll(executeQuery("SELECT id, item_title, item_code FROM lab_inventory_items ORDER BY item_title"));
$sections  = fetchAll(executeQuery("SELECT id, section_name FROM lab_sections ORDER BY section_name"));
$techStaff = fetchAll(executeQuery("SELECT id, username FROM users WHERE is_active=1 ORDER BY username"));

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
                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician','Maintenance Officer'])): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                                <i class="ri-add-line"></i> Log Maintenance
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Equipment Maintenance Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Total Records','primary','ri-file-list-line',$sTotal],
                    ['Open / In Progress','warning','ri-tools-line',$sOpen],
                    ['Completed','success','ri-check-double-line',$sDone],
                    ['Preventive','info','ri-calendar-check-line',$sPrev],
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
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <?php foreach (['repair','preventive','inspection','calibration','replacement'] as $t): ?>
                                <option value="<?php echo $t; ?>" <?php echo $typeFilter === $t ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <?php foreach (['reported','assigned','in_progress','completed','closed','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="maintenance.php" class="btn btn-secondary ms-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Maintenance Records (<?php echo count($records); ?>)</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr><th>Ref #</th><th>Item</th><th>Type</th><th>Severity</th><th>Technician</th><th>Scheduled</th><th>Cost</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $rec): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($rec['maintenance_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($rec['item_title'] ?? 'General'); ?>
                                        <?php if (!empty($rec['section_name'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($rec['section_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark"><?php echo ucfirst($rec['maintenance_type']); ?></span></td>
                                    <td>
                                        <?php $sev=['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'dark']; ?>
                                        <span class="badge bg-<?php echo $sev[$rec['severity']] ?? 'secondary'; ?>"><?php echo ucfirst($rec['severity']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($rec['technician_name'] ?? 'Unassigned'); ?></td>
                                    <td><?php echo $rec['scheduled_date'] ? formatDate($rec['scheduled_date']) : 'N/A'; ?></td>
                                    <td><?php echo formatCurrency($rec['cost']); ?></td>
                                    <td>
                                        <?php $st=['reported'=>'warning','assigned'=>'info','in_progress'=>'primary','completed'=>'success','closed'=>'secondary','cancelled'=>'danger']; ?>
                                        <span class="badge bg-<?php echo $st[$rec['status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$rec['status'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewMaintenance(<?php echo $rec['id']; ?>)" class="btn btn-sm btn-info"><i class="ri-eye-line"></i></button>
                                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician','Maintenance Officer'])): ?>
                                            <button onclick="updateMaintenance(<?php echo $rec['id']; ?>)" class="btn btn-sm btn-warning"><i class="ri-edit-line"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($records)): ?><tr><td colspan="9" class="text-center text-muted">No records found</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Log Maintenance Record</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addMaintenanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Equipment Item</label>
                            <select class="form-select" name="item_id">
                                <option value="">Select Item (optional)</option>
                                <?php foreach ($items as $it): ?><option value="<?php echo $it['id']; ?>"><?php echo htmlspecialchars($it['item_code'] . ' - ' . $it['item_title']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Maintenance Type</label>
                            <select class="form-select" name="maintenance_type" required>
                                <?php foreach (['repair','preventive','inspection','calibration','replacement'] as $t): ?><option value="<?php echo $t; ?>"><?php echo ucfirst($t); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Severity</label>
                            <select class="form-select" name="severity">
                                <?php foreach (['low','medium','high','critical'] as $sv): ?><option value="<?php echo $sv; ?>"><?php echo ucfirst($sv); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Damage Category</label>
                            <input type="text" class="form-control" name="damage_category" placeholder="e.g. Electrical, Mechanical">
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Description</label><textarea class="form-control" name="description" rows="3" required></textarea></div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assign Technician</label>
                            <select class="form-select" name="assigned_technician">
                                <option value="">Select</option>
                                <?php foreach ($techStaff as $t): ?><option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['username']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3"><label class="form-label">Service Provider</label><input type="text" class="form-control" name="service_provider"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Estimated Cost</label><input type="number" class="form-control" name="cost" step="0.01" value="0"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Scheduled Date</label><input type="date" class="form-control" name="scheduled_date"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Responsible Person</label><input type="text" class="form-control" name="responsible_user"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach (['reported','assigned','in_progress'] as $s): ?><option value="<?php echo $s; ?>"><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Investigation Notes</label><textarea class="form-control" name="investigation_notes" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Update Maintenance Record</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="updateMaintenanceForm">
                <input type="hidden" name="id" id="updateMainId">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select class="form-select" name="status" id="updateMainStatus">
                            <?php foreach (['reported','assigned','in_progress','completed','closed','cancelled'] as $s): ?><option value="<?php echo $s; ?>"><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Actual Cost</label><input type="number" class="form-control" name="cost" id="updateMainCost" step="0.01"></div>
                    <div class="mb-3"><label class="form-label">Completed Date</label><input type="date" class="form-control" name="completed_date" id="updateMainDate"></div>
                    <div class="mb-3"><label class="form-label">Resolution Notes</label><textarea class="form-control" name="resolution_notes" id="updateMainNotes" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Maintenance Modal -->
<div class="modal fade" id="viewMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Maintenance Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewMainBody"></div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addMaintenanceForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-maintenance.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addMaintenanceModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function updateMaintenance(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-maintenance.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#updateMainId').val(d.id);
                $('#updateMainStatus').val(d.status);
                $('#updateMainCost').val(d.cost||0);
                $('#updateMainDate').val(d.completed_date||'');
                $('#updateMainNotes').val(d.resolution_notes||'');
                $('#updateMaintenanceModal').modal('show');
            }
        }
    });
}

$('#updateMaintenanceForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/update-maintenance.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#updateMaintenanceModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewMaintenance(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-maintenance.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewMainBody').html(`
                    <table class="table table-bordered table-sm">
                        <tr><th>Ref #</th><td>${d.maintenance_number}</td><th>Type</th><td>${d.maintenance_type}</td></tr>
                        <tr><th>Item</th><td>${d.item_title||'General'}</td><th>Section</th><td>${d.section_name||'N/A'}</td></tr>
                        <tr><th>Severity</th><td>${d.severity}</td><th>Status</th><td>${d.status}</td></tr>
                        <tr><th>Technician</th><td>${d.technician_name||'Unassigned'}</td><th>Service Provider</th><td>${d.service_provider||'N/A'}</td></tr>
                        <tr><th>Cost</th><td>${d.cost}</td><th>Scheduled</th><td>${d.scheduled_date||'N/A'}</td></tr>
                        <tr><th>Completed</th><td>${d.completed_date||'N/A'}</td><th>Responsible</th><td>${d.responsible_user||'N/A'}</td></tr>
                        <tr><th colspan="4">Description</th></tr>
                        <tr><td colspan="4">${d.description||'N/A'}</td></tr>
                        <tr><th colspan="4">Resolution Notes</th></tr>
                        <tr><td colspan="4">${d.resolution_notes||'N/A'}</td></tr>
                    </table>`);
                $('#viewMaintenanceModal').modal('show');
            }
        }
    });
}
</script>
