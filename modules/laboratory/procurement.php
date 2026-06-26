<?php
/**
 * LAB Management - Procurement & Recently Purchased Items
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Lab Procurement';
$currentUser = getCurrentUser();

$statusFilter = $_GET['status'] ?? '';
$branchCond   = labBranchWhere('p', null, false);

$sql = "SELECT p.*, c.category_name, s.section_name, a.username as approver_name, u.username as creator_name
        FROM lab_procurement p
        LEFT JOIN lab_inventory_categories c ON p.category_id = c.id
        LEFT JOIN lab_sections s ON p.section_id = s.id
        LEFT JOIN users a ON p.approved_by = a.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE 1=1" . $branchCond;
$params = []; $types = '';

if (!empty($statusFilter)) { $sql .= " AND p.status = ?"; $params[] = $statusFilter; $types .= 's'; }
$sql .= " ORDER BY p.purchase_date DESC, p.created_at DESC";

$procurements = fetchAll(executeQuery($sql, $types ?: null, $params ?: null));

$base = "SELECT COUNT(*) as c, COALESCE(SUM(total_price),0) as total FROM lab_procurement WHERE 1=1" . labBranchWhere('', null, false);
$sAll      = fetchOne(executeQuery($base));
$sReceived = fetchOne(executeQuery($base . " AND status='received'"));
$sPending  = fetchOne(executeQuery($base . " AND status='pending'"))['c'] ?? 0;
$sMonth    = fetchOne(executeQuery($base . " AND status='received' AND purchase_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"))['c'] ?? 0;

$categories = fetchAll(executeQuery("SELECT id, category_name FROM lab_inventory_categories ORDER BY category_name"));
$sections   = fetchAll(executeQuery("SELECT id, section_name FROM lab_sections ORDER BY section_name"));

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
                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Procurement Officer'])): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProcurementModal">
                                <i class="ri-shopping-cart-line"></i> New Purchase Record
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Procurement &amp; Purchase Records</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary-lighten text-primary"><i class="ri-file-list-3-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted">Total Records</h5><h2 class="mb-0"><?php echo $sAll['c'] ?? 0; ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success-lighten text-success"><i class="ri-money-dollar-circle-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted">Total Spend</h5><h2 class="mb-0 fs-5"><?php echo formatCurrency($sReceived['total'] ?? 0); ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning-lighten text-warning"><i class="ri-time-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted">Pending Approval</h5><h2 class="mb-0"><?php echo $sPending; ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info-lighten text-info"><i class="ri-calendar-line font-24"></i></div>
                                <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted">This Month</h5><h2 class="mb-0"><?php echo $sMonth; ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <?php foreach (['pending','approved','ordered','received','rejected','cancelled'] as $s): ?><option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary">Filter</button><a href="procurement.php" class="btn btn-secondary ms-1">Reset</a></div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Purchase Records (<?php echo count($procurements); ?>)</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr><th>PO #</th><th>Supplier</th><th>Items</th><th>Category</th><th>Qty</th><th>Total Price</th><th>Date</th><th>Warranty</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($procurements as $p): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($p['purchase_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($p['item_description'], 0, 50)); ?>...</td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo $p['quantity']; ?></td>
                                    <td><strong><?php echo formatCurrency($p['total_price']); ?></strong></td>
                                    <td><?php echo formatDate($p['purchase_date']); ?></td>
                                    <td>
                                        <?php if ($p['warranty_expiry']): ?>
                                        <?php $wexp = strtotime($p['warranty_expiry']); $wcolor = $wexp < time() ? 'danger' : ($wexp < strtotime('+30 days') ? 'warning' : 'success'); ?>
                                        <span class="badge bg-<?php echo $wcolor; ?>"><?php echo formatDate($p['warranty_expiry']); ?></span>
                                        <?php else: echo 'N/A'; endif; ?>
                                    </td>
                                    <td>
                                        <?php $sc=['pending'=>'warning','approved'=>'info','ordered'=>'primary','received'=>'success','rejected'=>'danger','cancelled'=>'secondary']; ?>
                                        <span class="badge bg-<?php echo $sc[$p['status']] ?? 'secondary'; ?>"><?php echo ucfirst($p['status']); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewProcurement(<?php echo $p['id']; ?>)" class="btn btn-sm btn-info"><i class="ri-eye-line"></i></button>
                                            <?php if ($p['status'] === 'pending' && hasRole(['Super Admin','Admin','Lab Director'])): ?>
                                            <button onclick="approveProcurement(<?php echo $p['id']; ?>)" class="btn btn-sm btn-success" title="Approve"><i class="ri-check-line"></i></button>
                                            <?php endif; ?>
                                            <?php if ($p['status'] === 'approved' && hasRole(['Super Admin','Admin','Lab Director','Procurement Officer'])): ?>
                                            <button onclick="receiveProcurement(<?php echo $p['id']; ?>)" class="btn btn-sm btn-primary" title="Mark Received"><i class="ri-inbox-archive-line"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($procurements)): ?><tr><td colspan="10" class="text-center text-muted">No purchase records</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Procurement Modal -->
<div class="modal fade" id="addProcurementModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Purchase Record</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addProcurementForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Supplier Name</label><input type="text" class="form-control" name="supplier_name" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Supplier Contact</label><input type="text" class="form-control" name="supplier_contact"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Supplier Email</label><input type="email" class="form-control" name="supplier_email"></div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Item Description</label><textarea class="form-control" name="item_description" rows="2" required></textarea></div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select</option>
                                <?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3"><label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3"><label class="form-label">Invoice Number</label><input type="text" class="form-control" name="invoice_number"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label required">Quantity</label><input type="number" class="form-control" name="quantity" id="qty" value="1" min="1" required oninput="calcTotal()"></div>
                        <div class="col-md-3 mb-3"><label class="form-label required">Unit Price</label><input type="number" class="form-control" name="unit_price" id="unitPrice" step="0.01" value="0" required oninput="calcTotal()"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Total Price</label><input type="number" class="form-control" name="total_price" id="totalPrice" step="0.01" value="0" readonly></div>
                        <div class="col-md-3 mb-3"><label class="form-label required">Purchase Date</label><input type="date" class="form-control" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Expected Delivery</label><input type="date" class="form-control" name="expected_delivery"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Warranty (months)</label><input type="number" class="form-control" name="warranty_period" min="0" value="0"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Warranty Expiry</label><input type="date" class="form-control" name="warranty_expiry"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="pending">Pending Approval</option>
                                <option value="received">Received (already)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Procurement Modal -->
<div class="modal fade" id="viewProcurementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Purchase Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewProcBody"></div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function calcTotal() {
    var qty = parseFloat($('#qty').val()) || 0;
    var price = parseFloat($('#unitPrice').val()) || 0;
    $('#totalPrice').val((qty * price).toFixed(2));
}

$('#addProcurementForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-procurement.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addProcurementModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewProcurement(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-procurement.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewProcBody').html(`
                    <table class="table table-bordered table-sm">
                        <tr><th>PO #</th><td>${d.purchase_number}</td><th>Status</th><td>${d.status}</td></tr>
                        <tr><th>Supplier</th><td>${d.supplier_name}</td><th>Contact</th><td>${d.supplier_contact||'N/A'}</td></tr>
                        <tr><th>Category</th><td>${d.category_name||'N/A'}</td><th>Section</th><td>${d.section_name||'N/A'}</td></tr>
                        <tr><th>Quantity</th><td>${d.quantity}</td><th>Unit Price</th><td>${d.unit_price}</td></tr>
                        <tr><th>Total Price</th><td colspan="3"><strong>${d.total_price}</strong></td></tr>
                        <tr><th>Purchase Date</th><td>${d.purchase_date}</td><th>Expected Delivery</th><td>${d.expected_delivery||'N/A'}</td></tr>
                        <tr><th>Warranty Period</th><td>${d.warranty_period||'N/A'} months</td><th>Warranty Expiry</th><td>${d.warranty_expiry||'N/A'}</td></tr>
                        <tr><th>Invoice #</th><td colspan="3">${d.invoice_number||'N/A'}</td></tr>
                        <tr><th colspan="4">Item Description</th></tr>
                        <tr><td colspan="4">${d.item_description||'N/A'}</td></tr>
                        <tr><th colspan="4">Notes</th></tr>
                        <tr><td colspan="4">${d.notes||'N/A'}</td></tr>
                    </table>`);
                $('#viewProcurementModal').modal('show');
            }
        }
    });
}

function approveProcurement(id) {
    confirmAction('Approve this purchase?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/approve-procurement.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}

function receiveProcurement(id) {
    confirmAction('Mark this purchase as received?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/receive-procurement.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
