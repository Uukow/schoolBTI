<?php
require_once '../../config/config.php';
hrRequireAccess('hr_payroll', 'view', ['Accountant', 'Teacher', 'Staff']);
$pageTitle = 'Advance Salary';
$isAdmin = hasRole(['Super Admin', 'Admin', 'Accountant']);
$staff = $isAdmin ? fetchAll(executeQuery("SELECT id, staff_id, first_name, last_name FROM staff WHERE status='Active'")) : [];
include '../../includes/header.php'; include '../../includes/sidebar.php';
?>
<div class="content-page"><div class="content"><div class="container-fluid">
<div class="page-title-box"><div class="page-title-right"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal"><i class="ri-add-line"></i> Request Advance</button></div><h4 class="page-title">Advance Salary</h4></div>
<div class="card"><div class="card-body"><table class="table table-hover" id="dataTable"><thead><tr><th>No</th><th>Staff</th><th>Requested</th><th>Approved</th><th>Recovery/mo</th><th>Recovered</th><th>Status</th><?php if($isAdmin):?><th>Action</th><?php endif;?></tr></thead><tbody></tbody></table></div></div>
</div></div></div>
<div class="modal fade" id="modal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Advance Request</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<?php if($isAdmin):?><select id="staffId" class="form-select mb-3"><option value="">Staff</option><?php foreach($staff as $s):?><option value="<?php echo $s['id'];?>"><?php echo htmlspecialchars($s['staff_id']);?></option><?php endforeach;?></select><?php endif;?>
<input type="number" id="amount" class="form-control mb-3" placeholder="Amount"><input type="number" id="months" class="form-control mb-3" value="3" placeholder="Recovery months">
<textarea id="reason" class="form-control" rows="3" placeholder="Reason"></textarea></div>
<div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="saveBtn">Submit</button></div>
</div></div></div>
<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function(){var H=HrModule,isAdmin=<?php echo $isAdmin?'true':'false';?>;
function load(){H.get('ajax/hr/get-advances.php').then(function(r){var t=document.querySelector('#dataTable tbody');
if(!r.success||!r.data.length){t.innerHTML='<tr><td colspan="8" class="text-muted">No records</td></tr>';return;}
t.innerHTML=r.data.map(function(a){var act=isAdmin&&a.status==='Pending'?'<button class="btn btn-sm btn-success approve" data-id="'+a.id+'" data-amt="'+a.requested_amount+'">Approve</button>':'';
return '<tr><td>'+a.advance_no+'</td><td>'+a.first_name+' '+a.last_name+'</td><td>'+a.requested_amount+'</td><td>'+(a.approved_amount||'—')+'</td><td>'+a.monthly_recovery+'</td><td>'+a.total_recovered+'</td><td>'+H.badge(a.status)+'</td>'+(isAdmin?'<td>'+act+'</td>':'')+'</tr>';}).join('');});}
document.getElementById('saveBtn').onclick=function(){var p={requested_amount:amount.value,recovery_months:months.value,reason:reason.value};if(isAdmin)p.staff_id=staffId.value;
H.post('ajax/hr/save-advance.php',p).then(function(r){r.success?(bootstrap.Modal.getInstance(modal).hide(),H.success(r.message,load)):H.error(r.message);});};
document.addEventListener('click',function(e){if(e.target.classList.contains('approve')){H.post('ajax/hr/save-advance.php',{id:e.target.dataset.id,status:'Approved',approved_amount:e.target.dataset.amt,recovery_months:3}).then(function(r){r.success?H.success(r.message,load):H.error(r.message);});}});
load();})();
</script>
