<?php
require_once '../../config/config.php';
hrRequireAccess('hr_items', 'view');
$pageTitle = 'Item Requests';
$isAdmin = hasRole(['Super Admin', 'Admin']);
$inventory = fetchAll(executeQuery("SELECT id, item_name, item_code, quantity FROM inventory_items ORDER BY item_name"));
include '../../includes/header.php'; include '../../includes/sidebar.php';
?>
<div class="content-page"><div class="content"><div class="container-fluid">
<div class="page-title-box"><div class="page-title-right"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal"><i class="ri-add-line"></i> New Request</button></div><h4 class="page-title">Item Requests</h4></div>
<div class="card"><div class="card-body"><table class="table table-hover" id="dataTable"><thead><tr><th>Request No</th><th>Staff</th><th>Purpose</th><th>Priority</th><th>Status</th><?php if($isAdmin):?><th>Actions</th><?php endif;?></tr></thead><tbody></tbody></table></div></div>
</div></div></div>
<div class="modal fade" id="modal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Item Request</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<textarea id="purpose" class="form-control mb-3" placeholder="Purpose"></textarea>
<select id="inventoryItem" class="form-select mb-3"><option value="">Inventory item (optional)</option><?php foreach($inventory as $i):?><option value="<?php echo $i['id'];?>"><?php echo htmlspecialchars($i['item_name'].' (stock: '.$i['quantity'].')');?></option><?php endforeach;?></select>
<input type="text" id="itemDesc" class="form-control mb-3" placeholder="Item description"><input type="number" id="qty" class="form-control mb-3" value="1" min="1">
<select id="priority" class="form-select"><option>Normal</option><option>Urgent</option></select>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="saveBtn">Submit</button></div>
</div></div></div>
<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function(){var H=HrModule,isAdmin=<?php echo $isAdmin?'true':'false';?>;
function load(){H.get('ajax/hr/get-item-requests.php').then(function(r){var t=document.querySelector('#dataTable tbody');
if(!r.success||!r.data.length){t.innerHTML='<tr><td colspan="6" class="text-muted">No requests</td></tr>';return;}
t.innerHTML=r.data.map(function(x){var act=isAdmin?'<button class="btn btn-sm btn-success approve" data-id="'+x.id+'">Approve</button> <button class="btn btn-sm btn-primary fulfill" data-id="'+x.id+'">Fulfill</button>':'';
return '<tr><td>'+x.request_no+'</td><td>'+x.first_name+' '+x.last_name+'</td><td>'+H.escapeHtml(x.purpose)+'</td><td>'+x.priority+'</td><td>'+H.badge(x.status)+'</td>'+(isAdmin?'<td>'+act+'</td>':'')+'</tr>';});});}
document.getElementById('saveBtn').onclick=function(){H.post('ajax/hr/save-item-request.php',{purpose:purpose.value,inventory_item_id:inventoryItem.value,item_description:itemDesc.value,quantity_requested:qty.value,priority:priority.value}).then(function(r){r.success?(bootstrap.Modal.getInstance(modal).hide(),H.success(r.message,load)):H.error(r.message);});};
document.addEventListener('click',function(e){if(e.target.classList.contains('approve'))H.post('ajax/hr/save-item-request.php',{id:e.target.dataset.id,action:'approve'}).then(function(r){r.success?H.success(r.message,load):H.error(r.message);});
if(e.target.classList.contains('fulfill'))H.post('ajax/hr/save-item-request.php',{id:e.target.dataset.id,action:'fulfill'}).then(function(r){r.success?H.success(r.message,load):H.error(r.message);});});
load();})();
</script>
