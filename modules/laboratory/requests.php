<?php
/**
 * LAB Management - Material Request Management
 */

require_once '../../config/config.php';
requireLabRoles(labParticipantRoles());

$pageTitle = 'Material Requests';
$currentUser = getCurrentUser();

$statusFilter = $_GET['status'] ?? '';

$requesters = getLabRequesterOptions($currentUser);
$defaultRequesterKey = getLabRequesterDefaultKey($currentUser);
if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Lab Technician'])) {
    $requesters = array_values(array_filter(
        $requesters,
        fn($r) => $r['value'] === $defaultRequesterKey
    ));
}

$sql = "SELECT r.*, COALESCE(r.requester_name, u.username) as requester_name, s.section_name,
               a.username as approver_name, iss.username as issuer_name
        FROM lab_material_requests r
        LEFT JOIN users u ON r.requester_id = u.id
        LEFT JOIN lab_sections s ON r.section_id = s.id
        LEFT JOIN users a ON r.approved_by = a.id
        LEFT JOIN users iss ON r.issued_by = iss.id
        WHERE 1=1";
$params = []; $types = '';

if (!hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Lab Technician'])) {
    $sql .= " AND r.requester_id = ?"; $params[] = $currentUser['id']; $types .= 'i';
} elseif (!hasRole(['Super Admin'])) {
    $bf = labBranchWhere('r');
    $sql .= $bf['sql'];
    $params = array_merge($params, $bf['params']);
    $types .= $bf['types'];
}

if (!empty($statusFilter)) { $sql .= " AND r.status = ?"; $params[] = $statusFilter; $types .= 's'; }
$sql .= " ORDER BY r.created_at DESC";

$requests = fetchAll(executeQuery($sql, $types ?: null, $params ?: null));

// Stats
$branchCond = labBranchWhere('', null, false);
$statsPending  = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_material_requests WHERE status='pending'" . $branchCond))['c'] ?? 0;
$statsApproved = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_material_requests WHERE status='approved'" . $branchCond))['c'] ?? 0;
$statsIssued   = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_material_requests WHERE status='issued'" . $branchCond))['c'] ?? 0;
$statsReturned = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_material_requests WHERE status='returned'" . $branchCond))['c'] ?? 0;

$sections = fetchAll(executeQuery(
    "SELECT id, section_name FROM lab_sections WHERE 1=1" . labBranchWhere('', null, false) . " ORDER BY section_name"
));
$items    = fetchAll(executeQuery("SELECT id, item_title, available_qty FROM lab_inventory_items WHERE status='available' ORDER BY item_title"));

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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                                <i class="ri-add-line"></i> New Request
                            </button>
                        </div>
                        <h4 class="page-title">Material Request Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Pending','warning','ri-time-line',$statsPending,'pending'],
                    ['Approved','success','ri-checkbox-circle-line',$statsApproved,'approved'],
                    ['Issued','info','ri-arrow-right-circle-line',$statsIssued,'issued'],
                    ['Returned','secondary','ri-arrow-left-circle-line',$statsReturned,'returned'],
                ] as [$label,$color,$icon,$val,$status]): ?>
                <div class="col-md-3">
                    <a href="?status=<?php echo $status; ?>" class="text-decoration-none">
                        <div class="card widget-stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-<?php echo $color; ?>-lighten text-<?php echo $color; ?>"><i class="<?php echo $icon; ?> font-24"></i></div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mt-0 mb-1 text-muted"><?php echo $label; ?></h5>
                                        <h2 class="mb-0"><?php echo $val; ?></h2>
                                    </div>
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
                                <option value="">All Statuses</option>
                                <?php foreach (['pending','approved','rejected','issued','returned','closed'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="requests.php" class="btn btn-secondary ms-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Material Requests</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr>
                                    <th>Request #</th><th>Requester</th><th>Type</th><th>Section</th>
                                    <th>Date</th><th>Status</th><th>Approved By</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($req['request_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($req['requester_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo ucfirst($req['requester_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars($req['section_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatDate($req['request_date']); ?></td>
                                    <td>
                                        <?php $sc=['pending'=>'warning','approved'=>'success','rejected'=>'danger','issued'=>'info','returned'=>'secondary','closed'=>'dark']; ?>
                                        <span class="badge bg-<?php echo $sc[$req['status']] ?? 'secondary'; ?>"><?php echo ucfirst($req['status']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($req['approver_name'] ?? '-'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewRequest(<?php echo $req['id']; ?>)" class="btn btn-sm btn-info" title="View"><i class="ri-eye-line"></i></button>
                                            <?php if ($req['status'] === 'pending' && hasRole(['Super Admin','Admin','Lab Director','Lab Manager'])): ?>
                                            <button onclick="approveRequest(<?php echo $req['id']; ?>)" class="btn btn-sm btn-success" title="Approve"><i class="ri-check-line"></i></button>
                                            <button onclick="rejectRequest(<?php echo $req['id']; ?>)" class="btn btn-sm btn-danger" title="Reject"><i class="ri-close-line"></i></button>
                                            <?php endif; ?>
                                            <?php if ($req['status'] === 'approved' && hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician'])): ?>
                                            <button onclick="issueRequest(<?php echo $req['id']; ?>)" class="btn btn-sm btn-primary" title="Issue Materials"><i class="ri-arrow-right-circle-line"></i></button>
                                            <?php endif; ?>
                                            <?php if ($req['status'] === 'issued'): ?>
                                            <button onclick="returnRequest(<?php echo $req['id']; ?>)" class="btn btn-sm btn-secondary" title="Mark Returned"><i class="ri-arrow-left-circle-line"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($requests)): ?>
                                <tr><td colspan="8" class="text-center text-muted">No requests found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Request Modal -->
<div class="modal fade" id="addRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Material Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRequestForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Requester Name</label>
                            <select class="form-select select2-requester" name="requester_key" id="requesterSelect" required>
                                <option value="">Search and select requester...</option>
                                <?php foreach ($requesters as $req): ?>
                                <option value="<?php echo htmlspecialchars($req['value']); ?>" <?php echo $req['value'] === $defaultRequesterKey ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($req['label']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Requester Type</label>
                            <select class="form-select" name="requester_type">
                                <option value="student">Student</option>
                                <option value="instructor">Instructor</option>
                                <option value="technician">Technician</option>
                                <option value="department">Department</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Request Date</label>
                            <input type="date" class="form-control" name="request_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Required By Date</label>
                            <input type="date" class="form-control" name="required_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose / Description</label>
                        <textarea class="form-control" name="purpose" rows="3" placeholder="Describe purpose of the material request"></textarea>
                    </div>
                    <hr>
                    <h6>Requested Items</h6>
                    <div id="requestItemsContainer">
                        <div class="row request-item mb-2">
                            <div class="col-md-7">
                                <select class="form-select" name="item_ids[]">
                                    <option value="">Select Item</option>
                                    <?php foreach ($items as $it): ?>
                                    <option value="<?php echo $it['id']; ?>"><?php echo htmlspecialchars($it['item_title']); ?> (Avail: <?php echo $it['available_qty']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="quantities[]" value="1" min="1" placeholder="Qty">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm remove-item" disabled><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addMoreItems">
                        <i class="ri-add-line"></i> Add More Items
                    </button>
                    <div class="mt-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewRequestBody"><p class="text-center">Loading...</p></div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Reject Request</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="rejectForm">
                <input type="hidden" name="id" id="rejectId">
                <div class="modal-body">
                    <label class="form-label">Rejection Reason</label>
                    <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function initRequesterSelect() {
    if (!$('#requesterSelect').length || !$.fn.select2) return;
    if ($('#requesterSelect').hasClass('select2-hidden-accessible')) {
        $('#requesterSelect').select2('destroy');
    }
    $('#requesterSelect').select2({
        dropdownParent: $('#addRequestModal'),
        placeholder: 'Search requester by name...',
        allowClear: true,
        width: '100%'
    });
}

$('#addRequestModal').on('shown.bs.modal', function() {
    initRequesterSelect();
});

// Add more items
$('#addMoreItems').on('click', function() {
    var template = `<div class="row request-item mb-2">
        <div class="col-md-7"><select class="form-select" name="item_ids[]"><option value="">Select Item</option><?php foreach ($items as $it): ?><option value="<?php echo $it['id']; ?>"><?php echo addslashes(htmlspecialchars($it['item_title'])); ?> (Avail: <?php echo $it['available_qty']; ?>)</option><?php endforeach; ?></select></div>
        <div class="col-md-3"><input type="number" class="form-control" name="quantities[]" value="1" min="1" placeholder="Qty"></div>
        <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-item"><i class="ri-delete-bin-line"></i></button></div>
    </div>`;
    $('#requestItemsContainer').append(template);
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('.request-item').remove();
});

$('#addRequestForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-request.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addRequestModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewRequest(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-request.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                var items=r.items||[];
                var itemRows=items.map(i=>`<tr><td>${i.item_name}</td><td>${i.quantity_requested}</td><td>${i.quantity_issued||0}</td><td>${i.quantity_returned||0}</td></tr>`).join('');
                $('#viewRequestBody').html(`
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Request #:</strong> ${d.request_number}</div>
                        <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-info">${d.status}</span></div>
                        <div class="col-md-6 mt-2"><strong>Requester:</strong> ${d.requester_name||'N/A'}</div>
                        <div class="col-md-6 mt-2"><strong>Type:</strong> ${d.requester_type}</div>
                        <div class="col-md-6 mt-2"><strong>Section:</strong> ${d.section_name||'N/A'}</div>
                        <div class="col-md-6 mt-2"><strong>Request Date:</strong> ${d.request_date}</div>
                        <div class="col-12 mt-2"><strong>Purpose:</strong> ${d.purpose||'N/A'}</div>
                        <div class="col-12 mt-2"><strong>Notes:</strong> ${d.notes||'N/A'}</div>
                    </div>
                    <h6>Requested Items</h6>
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Item</th><th>Requested</th><th>Issued</th><th>Returned</th></tr></thead>
                        <tbody>${itemRows}</tbody>
                    </table>`);
                $('#viewRequestModal').modal('show');
            }
        }
    });
}

function approveRequest(id) {
    confirmAction('Approve this material request?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/approve-request.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}

function rejectRequest(id) {
    $('#rejectId').val(id);
    $('#rejectModal').modal('show');
}

$('#rejectForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/reject-request.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#rejectModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function issueRequest(id) {
    confirmAction('Issue materials for this request?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/issue-materials.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}

function returnRequest(id) {
    confirmAction('Mark all materials as returned?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/return-materials.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
