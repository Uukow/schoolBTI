<?php
/**
 * LAB Management - Issue Management
 */

require_once '../../config/config.php';
requireLabRoles(labTeacherRoles());

$pageTitle = 'Lab Issues';
$currentUser = getCurrentUser();

$statusFilter = $_GET['status'] ?? '';

$branchCond = labBranchWhere('i', null, false);

$sql = "SELECT i.*, t.type_name, s.section_name, r.username as reporter_name, a.username as assignee_name
        FROM lab_issues i
        LEFT JOIN lab_issue_types t ON i.issue_type_id = t.id
        LEFT JOIN lab_sections s ON i.section_id = s.id
        LEFT JOIN users r ON i.reported_by = r.id
        LEFT JOIN users a ON i.assigned_to = a.id
        WHERE 1=1" . $branchCond;
$params = []; $types = '';

if (!empty($statusFilter)) { $sql .= " AND i.status = ?"; $params[] = $statusFilter; $types .= 's'; }
$sql .= " ORDER BY i.created_at DESC";

$issues = fetchAll(executeQuery($sql, $types ?: null, $params ?: null));

$base = "SELECT COUNT(*) as c FROM lab_issues WHERE 1=1" . labBranchWhere('', null, false);
$sOpen   = fetchOne(executeQuery($base . " AND status='open'"))['c'] ?? 0;
$sInProg = fetchOne(executeQuery($base . " AND status='in_progress'"))['c'] ?? 0;
$sEsc    = fetchOne(executeQuery($base . " AND status='escalated'"))['c'] ?? 0;
$sResolved = fetchOne(executeQuery($base . " AND status='resolved'"))['c'] ?? 0;

$issueTypes = fetchAll(executeQuery("SELECT id, type_name FROM lab_issue_types WHERE is_active=1 ORDER BY type_name"));
$sections   = fetchAll(executeQuery("SELECT id, section_name FROM lab_sections ORDER BY section_name"));
$items      = fetchAll(executeQuery("SELECT id, item_title, item_code FROM lab_inventory_items ORDER BY item_title"));
$staffList  = fetchAll(executeQuery("SELECT id, username FROM users WHERE is_active=1 ORDER BY username"));

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
                            <a href="issue-types.php" class="btn btn-outline-secondary me-1"><i class="ri-list-settings-line"></i> Manage Issue Types</a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIssueModal">
                                <i class="ri-add-line"></i> Report Issue
                            </button>
                        </div>
                        <h4 class="page-title">Laboratory Issue Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Open','danger','ri-error-warning-line',$sOpen,'open'],
                    ['In Progress','warning','ri-loader-line',$sInProg,'in_progress'],
                    ['Escalated','dark','ri-arrow-up-circle-line',$sEsc,'escalated'],
                    ['Resolved','success','ri-checkbox-circle-line',$sResolved,'resolved'],
                ] as [$label,$color,$icon,$val,$st]): ?>
                <div class="col-md-3">
                    <a href="?status=<?php echo $st; ?>" class="text-decoration-none">
                        <div class="card widget-stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-<?php echo $color; ?>-lighten text-<?php echo $color; ?>"><i class="<?php echo $icon; ?> font-24"></i></div>
                                    <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted"><?php echo $label; ?></h5><h2 class="mb-0"><?php echo $val; ?></h2></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <?php foreach (['open','in_progress','escalated','resolved','closed'] as $s): ?><option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary">Filter</button><a href="issues.php" class="btn btn-secondary ms-1">Reset</a></div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Issues (<?php echo count($issues); ?>)</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr><th>Issue #</th><th>Title</th><th>Type</th><th>Priority</th><th>Section</th><th>Reporter</th><th>Assigned To</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issues as $issue): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($issue['issue_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($issue['title']); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($issue['type_name'] ?? 'N/A'); ?></span></td>
                                    <td>
                                        <?php $pc=['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'dark']; ?>
                                        <span class="badge bg-<?php echo $pc[$issue['priority']] ?? 'secondary'; ?>"><?php echo ucfirst($issue['priority']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($issue['section_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($issue['reporter_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($issue['assignee_name'] ?? 'Unassigned'); ?></td>
                                    <td>
                                        <?php $sc=['open'=>'danger','in_progress'=>'warning','escalated'=>'dark','resolved'=>'success','closed'=>'secondary']; ?>
                                        <span class="badge bg-<?php echo $sc[$issue['status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$issue['status'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewIssue(<?php echo $issue['id']; ?>)" class="btn btn-sm btn-info"><i class="ri-eye-line"></i></button>
                                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician'])): ?>
                                            <button onclick="updateIssue(<?php echo $issue['id']; ?>)" class="btn btn-sm btn-warning"><i class="ri-edit-line"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($issues)): ?><tr><td colspan="9" class="text-center text-muted">No issues found</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Issue Modal -->
<div class="modal fade" id="addIssueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Report Lab Issue</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addIssueForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3"><label class="form-label required">Issue Title</label><input type="text" class="form-control" name="title" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <?php foreach (['low','medium','high','critical'] as $p): ?><option value="<?php echo $p; ?>"><?php echo ucfirst($p); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Issue Type</label>
                            <select class="form-select" name="issue_type_id">
                                <option value="">Select Type</option>
                                <?php foreach ($issueTypes as $it): ?><option value="<?php echo $it['id']; ?>"><?php echo htmlspecialchars($it['type_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Related Item</label>
                            <select class="form-select" name="item_id">
                                <option value="">Select Item (optional)</option>
                                <?php foreach ($items as $it): ?><option value="<?php echo $it['id']; ?>"><?php echo htmlspecialchars($it['item_code'] . ' - ' . $it['item_title']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Assign To</label>
                            <select class="form-select" name="assigned_to">
                                <option value="">Select (optional)</option>
                                <?php foreach ($staffList as $st): ?><option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Description</label><textarea class="form-control" name="description" rows="4" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Submit Issue</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Update Issue Modal -->
<div class="modal fade" id="updateIssueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Update Issue</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="updateIssueForm">
                <input type="hidden" name="id" id="updateIssueId">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select class="form-select" name="status" id="updateIssueStatus">
                            <?php foreach (['open','in_progress','escalated','resolved','closed'] as $s): ?><option value="<?php echo $s; ?>"><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Assign To</label>
                        <select class="form-select" name="assigned_to" id="updateIssueAssigned">
                            <option value="">Unassigned</option>
                            <?php foreach ($staffList as $st): ?><option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Resolution Notes</label><textarea class="form-control" name="resolution_notes" id="updateIssueNotes" rows="4"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Issue Modal -->
<div class="modal fade" id="viewIssueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Issue Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewIssueBody"></div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addIssueForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-issue.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addIssueModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewIssue(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-issue.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewIssueBody').html(`
                    <table class="table table-bordered table-sm">
                        <tr><th>Issue #</th><td>${d.issue_number}</td><th>Status</th><td>${d.status}</td></tr>
                        <tr><th>Title</th><td colspan="3">${d.title}</td></tr>
                        <tr><th>Type</th><td>${d.type_name||'N/A'}</td><th>Priority</th><td>${d.priority}</td></tr>
                        <tr><th>Section</th><td>${d.section_name||'N/A'}</td><th>Reporter</th><td>${d.reporter_name||'N/A'}</td></tr>
                        <tr><th>Assigned To</th><td>${d.assignee_name||'Unassigned'}</td><th>Reported</th><td>${d.created_at}</td></tr>
                        <tr><th colspan="4">Description</th></tr>
                        <tr><td colspan="4">${d.description||'N/A'}</td></tr>
                        <tr><th colspan="4">Resolution Notes</th></tr>
                        <tr><td colspan="4">${d.resolution_notes||'N/A'}</td></tr>
                    </table>`);
                $('#viewIssueModal').modal('show');
            }
        }
    });
}

function updateIssue(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-issue.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#updateIssueId').val(d.id);
                $('#updateIssueStatus').val(d.status);
                $('#updateIssueAssigned').val(d.assigned_to||'');
                $('#updateIssueNotes').val(d.resolution_notes||'');
                $('#updateIssueModal').modal('show');
            }
        }
    });
}

$('#updateIssueForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/update-issue.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#updateIssueModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});
</script>
