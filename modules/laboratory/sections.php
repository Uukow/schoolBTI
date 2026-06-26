<?php
/**
 * LAB Management - Laboratory Sections
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Laboratory Sections';
$currentUser = getCurrentUser();

$bf = labBranchWhere('s');
$sql = "SELECT s.*, u.username as supervisor_name,
               (SELECT COUNT(*) FROM lab_inventory_items i WHERE i.section_id = s.id) as item_count
        FROM lab_sections s
        LEFT JOIN users u ON s.supervisor_id = u.id
        WHERE 1=1" . $bf['sql'] . " ORDER BY s.section_name";

$sections = fetchAll(executeQuery($sql, $bf['types'] ?: null, $bf['params'] ?: null));

// Stats
$totalActive   = count(array_filter($sections, fn($s) => $s['status'] === 'active'));
$totalInactive = count(array_filter($sections, fn($s) => $s['status'] === 'inactive'));
$totalMaint    = count(array_filter($sections, fn($s) => $s['status'] === 'under_maintenance'));

// Staff list for supervisor dropdown
$staffSql = "SELECT id, username, email FROM users WHERE is_active = 1 ORDER BY username";
$staffList = fetchAll(executeQuery($staffSql));

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
                            <?php if (hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager'])): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                                <i class="ri-add-line"></i> Add Section
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Laboratory Sections</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary-lighten text-primary"><i class="ri-building-2-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Sections</h5>
                                    <h2 class="mb-0"><?php echo count($sections); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success-lighten text-success"><i class="ri-checkbox-circle-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Active</h5>
                                    <h2 class="mb-0"><?php echo $totalActive; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning-lighten text-warning"><i class="ri-tools-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Under Maintenance</h5>
                                    <h2 class="mb-0"><?php echo $totalMaint; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-secondary-lighten text-secondary"><i class="ri-close-circle-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Inactive</h5>
                                    <h2 class="mb-0"><?php echo $totalInactive; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Laboratory Sections</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Section Name</th>
                                            <th>Code</th>
                                            <th>Supervisor</th>
                                            <th>Capacity</th>
                                            <th>Items</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sections as $i => $sec): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><strong><?php echo htmlspecialchars($sec['section_name']); ?></strong>
                                                <?php if (!empty($sec['description'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($sec['description'], 0, 60)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($sec['section_code']); ?></span></td>
                                            <td><?php echo htmlspecialchars($sec['supervisor_name'] ?? 'Not Assigned'); ?></td>
                                            <td><?php echo $sec['capacity']; ?></td>
                                            <td><span class="badge bg-info"><?php echo $sec['item_count']; ?></span></td>
                                            <td><?php echo htmlspecialchars($sec['location'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $sc = ['active'=>'success','inactive'=>'secondary','under_maintenance'=>'warning'];
                                                $sl = ['active'=>'Active','inactive'=>'Inactive','under_maintenance'=>'Maintenance'];
                                                $s = $sec['status'];
                                                ?>
                                                <span class="badge bg-<?php echo $sc[$s] ?? 'secondary'; ?>"><?php echo $sl[$s] ?? ucfirst($s); ?></span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if (hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager'])): ?>
                                                    <button onclick="editSection(<?php echo $sec['id']; ?>)" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteSection(<?php echo $sec['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
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
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Laboratory Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSectionForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required">Section Name</label>
                            <input type="text" class="form-control" name="section_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Section Code</label>
                            <input type="text" class="form-control" name="section_code" required placeholder="e.g. LAB-ELEC">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supervisor</label>
                            <select class="form-select" name="supervisor_id">
                                <option value="">Select Supervisor</option>
                                <?php foreach ($staffList as $st): ?>
                                <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="30" min="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="under_maintenance">Under Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" placeholder="Building / Floor / Room">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Laboratory Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSectionForm">
                <input type="hidden" name="id" id="editSectionId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required">Section Name</label>
                            <input type="text" class="form-control" name="section_name" id="editSectionName" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Section Code</label>
                            <input type="text" class="form-control" name="section_code" id="editSectionCode" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supervisor</label>
                            <select class="form-select" name="supervisor_id" id="editSupervisorId">
                                <option value="">Select Supervisor</option>
                                <?php foreach ($staffList as $st): ?>
                                <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" id="editCapacity" min="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="under_maintenance">Under Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" id="editLocation">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addSectionForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/laboratory/add-section.php',
        type: 'POST', data: $(this).serialize(), dataType: 'json',
        success: function(r) {
            if (r.success) { showToast(r.message, 'success'); $('#addSectionModal').modal('hide'); setTimeout(() => location.reload(), 1200); }
            else showToast(r.message, 'error');
        }
    });
});

function editSection(id) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/laboratory/get-section.php',
        type: 'GET', data: { id: id }, dataType: 'json',
        success: function(r) {
            if (r.success) {
                var d = r.data;
                $('#editSectionId').val(d.id);
                $('#editSectionName').val(d.section_name);
                $('#editSectionCode').val(d.section_code);
                $('#editSupervisorId').val(d.supervisor_id || '');
                $('#editCapacity').val(d.capacity);
                $('#editStatus').val(d.status);
                $('#editLocation').val(d.location || '');
                $('#editDescription').val(d.description || '');
                $('#editSectionModal').modal('show');
            }
        }
    });
}

$('#editSectionForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/laboratory/edit-section.php',
        type: 'POST', data: $(this).serialize(), dataType: 'json',
        success: function(r) {
            if (r.success) { showToast(r.message, 'success'); $('#editSectionModal').modal('hide'); setTimeout(() => location.reload(), 1200); }
            else showToast(r.message, 'error');
        }
    });
});

function deleteSection(id) {
    confirmAction('Delete this laboratory section? Items assigned to it will be unlinked.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/laboratory/delete-section.php',
            type: 'POST', data: { id: id }, dataType: 'json',
            success: function(r) {
                if (r.success) { showToast(r.message, 'success'); setTimeout(() => location.reload(), 1200); }
                else showToast(r.message, 'error');
            }
        });
    });
}
</script>
