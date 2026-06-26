<?php
/**
 * LAB Management - Inventory Management
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Lab Inventory';
$currentUser = getCurrentUser();

$statusFilter   = $_GET['status']   ?? '';
$categoryFilter = $_GET['category'] ?? '';
$sectionFilter  = $_GET['section']  ?? '';
$searchQuery    = $_GET['search']   ?? '';

$sql = "SELECT i.*, c.category_name, s.section_name
        FROM lab_inventory_items i
        LEFT JOIN lab_inventory_categories c ON i.category_id = c.id
        LEFT JOIN lab_sections s ON i.section_id = s.id
        WHERE 1=1";
$params = []; $types = '';

$bf = labBranchWhere('i');
$sql .= $bf['sql'];
$params = array_merge($params, $bf['params']);
$types .= $bf['types'];
if (!empty($statusFilter))   { $sql .= " AND i.status = ?";       $params[] = $statusFilter;   $types .= 's'; }
if (!empty($categoryFilter)) { $sql .= " AND i.category_id = ?";  $params[] = $categoryFilter; $types .= 'i'; }
if (!empty($sectionFilter))  { $sql .= " AND i.section_id = ?";   $params[] = $sectionFilter;  $types .= 'i'; }
if (!empty($searchQuery)) {
    $sql .= " AND (i.item_title LIKE ? OR i.item_code LIKE ? OR i.barcode LIKE ? OR i.brand LIKE ?)";
    $s = "%$searchQuery%"; $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s; $types .= 'ssss';
}
$sql .= " ORDER BY i.item_title";

$items = fetchAll(executeQuery($sql, $types ?: null, $params ?: null));

// Stats
$statsBase = "SELECT COUNT(*) as cnt FROM lab_inventory_items WHERE 1=1" . labBranchWhere('', null, false);
$sTotal   = fetchOne(executeQuery($statsBase))['cnt'] ?? 0;
$sAvail   = fetchOne(executeQuery($statsBase . " AND status='available'"))['cnt'] ?? 0;
$sDamaged = fetchOne(executeQuery($statsBase . " AND status='damaged'"))['cnt'] ?? 0;
$sIssued  = fetchOne(executeQuery($statsBase . " AND status='issued'"))['cnt'] ?? 0;

// Dropdowns
$categories = fetchAll(executeQuery("SELECT id, category_name FROM lab_inventory_categories ORDER BY category_name"));
$sections   = fetchAll(executeQuery(
    "SELECT id, section_name FROM lab_sections WHERE 1=1" . labBranchWhere('', null, false) . " ORDER BY section_name"
));

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
                            <?php if (hasRole(['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Lab Technician', 'Procurement Officer'])): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                <i class="ri-add-line"></i> Add Item
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Lab Inventory Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Total Items','primary','ri-archive-line',$sTotal,''],
                    ['Available','success','ri-checkbox-circle-line',$sAvail,'?status=available'],
                    ['Damaged','danger','ri-alert-line',$sDamaged,'?status=damaged'],
                    ['Issued','warning','ri-arrow-right-circle-line',$sIssued,'?status=issued'],
                ] as [$label,$color,$icon,$val,$link]): ?>
                <div class="col-md-3">
                    <a href="<?php echo $link; ?>" class="text-decoration-none">
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

            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <?php foreach (['available','issued','damaged','repaired','lost','expired','under_maintenance'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="section" class="form-select">
                                <option value="">All Sections</option>
                                <?php foreach ($sections as $sec): ?>
                                <option value="<?php echo $sec['id']; ?>" <?php echo $sectionFilter == $sec['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec['section_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Title, code, barcode, brand">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Search</button>
                            <a href="inventory.php" class="btn btn-secondary"><i class="ri-refresh-line"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Inventory Items (<?php echo count($items); ?>)</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable-export">
                            <thead>
                                <tr>
                                    <th>Code</th><th>Item Title</th><th>Category</th><th>Section</th>
                                    <th>Brand</th><th>Qty</th><th>Available</th><th>Unit Cost</th>
                                    <th>Status</th><th>Condition</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['item_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['item_title']); ?>
                                        <?php if (!empty($item['barcode'])): ?>
                                        <br><small class="text-muted">BC: <?php echo htmlspecialchars($item['barcode']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['section_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>
                                        <?php $aqty = $item['available_qty']; $color = $aqty < 3 ? 'danger' : ($aqty < 6 ? 'warning' : 'success'); ?>
                                        <span class="badge bg-<?php echo $color; ?>"><?php echo $aqty; ?></span>
                                    </td>
                                    <td><?php echo formatCurrency($item['unit_cost']); ?></td>
                                    <td>
                                        <?php
                                        $sc=['available'=>'success','issued'=>'warning','damaged'=>'danger','repaired'=>'info','lost'=>'dark','expired'=>'secondary','under_maintenance'=>'warning'];
                                        ?>
                                        <span class="badge bg-<?php echo $sc[$item['status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$item['status'])); ?></span>
                                    </td>
                                    <td>
                                        <?php $cc=['new'=>'success','good'=>'info','fair'=>'warning','poor'=>'danger','condemned'=>'dark']; ?>
                                        <span class="badge bg-<?php echo $cc[$item['condition']] ?? 'secondary'; ?>"><?php echo ucfirst($item['condition']); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-info" title="View"><i class="ri-eye-line"></i></button>
                                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Lab Technician','Procurement Officer'])): ?>
                                            <button onclick="editItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-warning" title="Edit"><i class="ri-edit-line"></i></button>
                                            <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addItemForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Item Title</label>
                            <input type="text" class="form-control" name="item_title" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Item Code</label>
                            <input type="text" class="form-control" name="item_code" placeholder="Auto-generated if empty">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Barcode / QR</label>
                            <input type="text" class="form-control" name="barcode">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $sec): ?>
                                <option value="<?php echo $sec['id']; ?>"><?php echo htmlspecialchars($sec['section_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="available">Available</option>
                                <option value="under_maintenance">Under Maintenance</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Model Number</label>
                            <input type="text" class="form-control" name="model_number">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" class="form-control" name="supplier">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Condition</label>
                            <select class="form-select" name="condition">
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="poor">Poor</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label required">Quantity</label>
                            <input type="number" class="form-control" name="quantity" value="1" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" name="unit_cost" step="0.01" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" name="purchase_date">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Warranty Expiry</label>
                            <input type="date" class="form-control" name="warranty_expiry">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location (Shelf/Rack)</label>
                            <input type="text" class="form-control" name="location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Warranty Info</label>
                            <input type="text" class="form-control" name="warranty_info">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal (dynamically filled) -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm">
                <input type="hidden" name="id" id="editItemId">
                <div class="modal-body" id="editItemBody">
                    <p class="text-center text-muted">Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Item Modal -->
<div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewItemBody"><p class="text-center text-muted">Loading...</p></div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$('#addItemForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-item.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){ if(r.success){showToast(r.message,'success');$('#addItemModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error'); }
    });
});

function viewItem(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-item.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewItemBody').html(`
                    <table class="table table-bordered">
                        <tr><th>Item Code</th><td>${d.item_code||'N/A'}</td><th>Barcode</th><td>${d.barcode||'N/A'}</td></tr>
                        <tr><th>Title</th><td colspan="3">${d.item_title}</td></tr>
                        <tr><th>Category</th><td>${d.category_name||'N/A'}</td><th>Section</th><td>${d.section_name||'N/A'}</td></tr>
                        <tr><th>Brand</th><td>${d.brand||'N/A'}</td><th>Model</th><td>${d.model_number||'N/A'}</td></tr>
                        <tr><th>Supplier</th><td>${d.supplier||'N/A'}</td><th>Purchase Date</th><td>${d.purchase_date||'N/A'}</td></tr>
                        <tr><th>Quantity</th><td>${d.quantity}</td><th>Available</th><td>${d.available_qty}</td></tr>
                        <tr><th>Unit Cost</th><td>${d.unit_cost}</td><th>Total Cost</th><td>${d.total_cost}</td></tr>
                        <tr><th>Status</th><td>${d.status}</td><th>Condition</th><td>${d.condition}</td></tr>
                        <tr><th>Warranty Expiry</th><td>${d.warranty_expiry||'N/A'}</td><th>Location</th><td>${d.location||'N/A'}</td></tr>
                        <tr><th>Description</th><td colspan="3">${d.description||'N/A'}</td></tr>
                        <tr><th>Notes</th><td colspan="3">${d.notes||'N/A'}</td></tr>
                    </table>`);
                $('#viewItemModal').modal('show');
            }
        }
    });
}

function editItem(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-item.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                var cats=<?php echo json_encode($categories); ?>;
                var secs=<?php echo json_encode($sections); ?>;
                var catOpts=cats.map(c=>`<option value="${c.id}" ${c.id==d.category_id?'selected':''}>${c.category_name}</option>`).join('');
                var secOpts=secs.map(s=>`<option value="${s.id}" ${s.id==d.section_id?'selected':''}>${s.section_name}</option>`).join('');
                var statuses=['available','issued','damaged','repaired','lost','expired','under_maintenance'];
                var statOpts=statuses.map(s=>`<option value="${s}" ${s==d.status?'selected':''}>${s.replace(/_/g,' ')}</option>`).join('');
                var conds=['new','good','fair','poor','condemned'];
                var condOpts=conds.map(c=>`<option value="${c}" ${c==d.condition?'selected':''}>${c}</option>`).join('');
                $('#editItemId').val(d.id);
                $('#editItemBody').html(`
                <div class="row">
                  <div class="col-md-6 mb-3"><label class="form-label required">Item Title</label><input type="text" class="form-control" name="item_title" value="${d.item_title}" required></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Item Code</label><input type="text" class="form-control" name="item_code" value="${d.item_code||''}"></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Barcode</label><input type="text" class="form-control" name="barcode" value="${d.barcode||''}"></div>
                </div>
                <div class="row">
                  <div class="col-md-4 mb-3"><label class="form-label">Category</label><select class="form-select" name="category_id"><option value="">Select</option>${catOpts}</select></div>
                  <div class="col-md-4 mb-3"><label class="form-label">Section</label><select class="form-select" name="section_id"><option value="">Select</option>${secOpts}</select></div>
                  <div class="col-md-4 mb-3"><label class="form-label">Status</label><select class="form-select" name="status">${statOpts}</select></div>
                </div>
                <div class="row">
                  <div class="col-md-3 mb-3"><label class="form-label">Brand</label><input type="text" class="form-control" name="brand" value="${d.brand||''}"></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Model</label><input type="text" class="form-control" name="model_number" value="${d.model_number||''}"></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Condition</label><select class="form-select" name="condition">${condOpts}</select></div>
                  <div class="col-md-3 mb-3"><label class="form-label required">Quantity</label><input type="number" class="form-control" name="quantity" value="${d.quantity}" min="1" required></div>
                </div>
                <div class="row">
                  <div class="col-md-3 mb-3"><label class="form-label">Unit Cost</label><input type="number" class="form-control" name="unit_cost" step="0.01" value="${d.unit_cost}"></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Purchase Date</label><input type="date" class="form-control" name="purchase_date" value="${d.purchase_date||''}"></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Warranty Expiry</label><input type="date" class="form-control" name="warranty_expiry" value="${d.warranty_expiry||''}"></div>
                  <div class="col-md-3 mb-3"><label class="form-label">Location</label><input type="text" class="form-control" name="location" value="${d.location||''}"></div>
                </div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2">${d.description||''}</textarea></div>
                <div class="mb-3"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">${d.notes||''}</textarea></div>`);
                $('#editItemModal').modal('show');
            }
        }
    });
}

$('#editItemForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/edit-item.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#editItemModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function deleteItem(id) {
    confirmAction('Delete this inventory item?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/delete-item.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
