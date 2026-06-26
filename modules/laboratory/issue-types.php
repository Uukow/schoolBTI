<?php
/**
 * LAB Management - Issue Types Configuration
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager'])) {
    header('Location: ' . APP_URL . 'modules/laboratory/issues.php'); exit;
}

$pageTitle = 'Lab Issue Types';
$currentUser = getCurrentUser();

$issueTypes = fetchAll(executeQuery("SELECT * FROM lab_issue_types ORDER BY type_name"));

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
                            <a href="issues.php" class="btn btn-secondary me-1"><i class="ri-arrow-left-line"></i> Back to Issues</a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIssueTypeModal">
                                <i class="ri-add-line"></i> Add Issue Type
                            </button>
                        </div>
                        <h4 class="page-title">Issue Types Configuration</h4>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Configured Issue Types</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead><tr><th>Type Name</th><th>Code</th><th>Priority</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($issueTypes as $it): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($it['type_name']); ?></strong></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($it['type_code']); ?></span></td>
                                    <td>
                                        <?php $pc=['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'dark']; ?>
                                        <span class="badge bg-<?php echo $pc[$it['priority_level']] ?? 'secondary'; ?>"><?php echo ucfirst($it['priority_level']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($it['description'] ?? '', 0, 60)); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $it['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $it['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                    </td>
                                    <td>
                                        <button onclick="toggleIssueType(<?php echo $it['id']; ?>, <?php echo $it['is_active'] ? 0 : 1; ?>)" class="btn btn-sm btn-<?php echo $it['is_active'] ? 'warning' : 'success'; ?>" title="<?php echo $it['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="ri-<?php echo $it['is_active'] ? 'eye-off' : 'eye'; ?>-line"></i>
                                        </button>
                                        <button onclick="deleteIssueType(<?php echo $it['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
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

<!-- Add Issue Type Modal -->
<div class="modal fade" id="addIssueTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Issue Type</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addIssueTypeForm">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label required">Type Name</label><input type="text" class="form-control" name="type_name" required></div>
                    <div class="mb-3"><label class="form-label">Type Code</label><input type="text" class="form-control" name="type_code" placeholder="Auto-generated if empty"></div>
                    <div class="mb-3"><label class="form-label">Priority Level</label>
                        <select class="form-select" name="priority_level">
                            <?php foreach (['low','medium','high','critical'] as $p): ?><option value="<?php echo $p; ?>"><?php echo ucfirst($p); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addIssueTypeForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-issue-type.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addIssueTypeModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function toggleIssueType(id, state) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/toggle-issue-type.php', type:'POST', data:{id:id, is_active:state}, dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
}

function deleteIssueType(id) {
    confirmAction('Delete this issue type?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/delete-issue-type.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
