<?php
require_once '../../config/config.php';
hrRequirePage('hr_payroll', 'view', ['Accountant']);
$pageTitle = 'Payroll Runs';
$branches = fetchAll(executeQuery("SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name"));
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<div class="content-page"><div class="content"><div class="container-fluid">
<div class="row"><div class="col-12"><div class="page-title-box">
<div class="page-title-right"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#runModal"><i class="ri-add-line"></i> New Payroll Run</button></div>
<h4 class="page-title">Payroll Runs & Approval</h4></div></div></div>
<div class="row"><div class="col-12"><div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-hover" id="dataTable"><thead><tr>
<th>Run No</th><th>Month</th><th>Branch</th><th>Staff</th><th>Total</th><th>Status</th><th>Actions</th>
</tr></thead><tbody></tbody></table></div>
</div></div></div></div>
</div></div></div>

<div class="modal fade" id="runModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Create Payroll Run</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Payment Month</label><input type="month" class="form-control" id="paymentMonth" value="<?php echo date('Y-m'); ?>"></div>
<?php if (hasRole(['Super Admin'])): ?>
<div class="mb-3"><label class="form-label">Branch (optional)</label>
<select class="form-select" id="branchId"><option value="">All Branches</option>
<?php foreach ($branches as $b): ?><option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option><?php endforeach; ?>
</select></div>
<?php endif; ?>
<div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" id="remarks" rows="2"></textarea></div>
</div>
<div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="createRunBtn">Create Run</button></div>
</div></div></div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    function load() {
        var tb = document.querySelector('#dataTable tbody');
        tb.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';
        H.get('ajax/hr/get-payroll-runs.php').then(function (res) {
            if (!res.success || !res.data.length) { tb.innerHTML = '<tr><td colspan="7" class="text-muted">No payroll runs yet</td></tr>'; return; }
            tb.innerHTML = res.data.map(function (r) {
                var actions = '';
                if (r.status === 'Draft') actions += '<button class="btn btn-sm btn-success me-1 approve-run" data-id="'+r.id+'">Approve</button>';
                if (r.status === 'Approved') actions += '<a class="btn btn-sm btn-outline-primary" href="'+H.apiUrl()+'ajax/hr/bank-export.php?run_id='+r.id+'">Bank CSV</a>';
                return '<tr><td>'+H.escapeHtml(r.run_no)+'</td><td>'+H.formatDate(r.payment_month)+'</td><td>'+H.escapeHtml(r.branch_name||'All')+'</td><td>'+r.total_staff+'</td><td><?php echo CURRENCY_SYMBOL; ?>'+parseFloat(r.total_amount).toLocaleString()+'</td><td>'+H.badge(r.status,'info')+'</td><td>'+actions+'</td></tr>';
            }).join('');
        }).catch(function (e) { H.error(e.message); });
    }
    document.getElementById('createRunBtn').addEventListener('click', function () {
        H.post('ajax/hr/create-payroll-run.php', {
            payment_month: document.getElementById('paymentMonth').value,
            branch_id: document.getElementById('branchId') ? document.getElementById('branchId').value : '',
            remarks: document.getElementById('remarks').value
        }).then(function (res) {
            if (res.success) { bootstrap.Modal.getInstance(document.getElementById('runModal')).hide(); H.success(res.message, load); }
            else H.error(res.message);
        });
    });
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('approve-run')) {
            H.post('ajax/hr/approve-payroll-run.php', { run_id: e.target.dataset.id, action: 'approve' })
                .then(function (res) { res.success ? H.success(res.message, load) : H.error(res.message); });
        }
    });
    load();
})();
</script>
