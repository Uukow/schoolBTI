<?php
require_once '../../config/config.php';
hrRequirePage('hr_payroll', 'view');

$pageTitle = 'Employee Contracts';
$staff = fetchAll(executeQuery(
    "SELECT id, staff_id, first_name, last_name, designation FROM staff WHERE status IN ('Active','Inactive') ORDER BY first_name"
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
<button type="button" class="btn btn-primary" id="btnNewContract">
<i class="ri-file-add-line"></i> New Contract
</button>
</div>
<h4 class="page-title">Employee Contracts</h4>
</div>
</div>
</div>

<!-- KPI Cards -->
<div class="row" id="contractStats">
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-3"><i class="ri-file-list-3-line font-24"></i></div>
<div><p class="text-muted mb-1">Total Contracts</p><h3 class="mb-0" id="statTotal">—</h3></div>
</div></div></div>
</div>
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-3"><i class="ri-checkbox-circle-line font-24"></i></div>
<div><p class="text-muted mb-1">Active</p><h3 class="mb-0" id="statActive">—</h3></div>
</div></div></div>
</div>
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-3"><i class="ri-alarm-warning-line font-24"></i></div>
<div><p class="text-muted mb-1">Expiring (30 days)</p><h3 class="mb-0" id="statExpiring">—</h3></div>
</div></div></div>
</div>
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-danger-lighten text-danger rounded p-2 me-3"><i class="ri-close-circle-line font-24"></i></div>
<div><p class="text-muted mb-1">Past End Date</p><h3 class="mb-0" id="statExpired">—</h3></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-3">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Contract no, staff name…">
</div>
<div class="col-md-2">
<label class="form-label small">Staff</label>
<select id="filterStaff" class="form-select">
<option value="">All Staff</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['staff_id'] . ' - ' . $s['first_name'] . ' ' . $s['last_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select">
<option value="">All</option>
<option value="Active">Active</option>
<option value="Draft">Draft</option>
<option value="Expired">Expired</option>
<option value="Terminated">Terminated</option>
<option value="Renewed">Renewed</option>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Type</label>
<select id="filterType" class="form-select">
<option value="">All Types</option>
<option value="Permanent">Permanent</option>
<option value="Fixed_Term">Fixed Term</option>
<option value="Probation">Probation</option>
<option value="Consultancy">Consultancy</option>
</select>
</div>
<div class="col-md-3">
<button type="button" class="btn btn-primary me-1" id="btnFilter"><i class="ri-filter-line"></i> Apply</button>
<button type="button" class="btn btn-light" id="btnReset"><i class="ri-refresh-line"></i> Reset</button>
</div>
</div>
</div></div>

<!-- Table -->
<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="contractsTable">
<thead class="table-light">
<tr>
<th>Contract No</th>
<th>Employee</th>
<th>Type</th>
<th>Period</th>
<th>Salary</th>
<th>Status</th>
<th>File</th>
<th style="min-width:160px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="8" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>

</div>
</div>
</div>

<!-- Create / Edit Contract Modal -->
<div class="modal fade" id="contractModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="contractModalTitle"><i class="ri-file-text-line"></i> New Contract</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="contractForm" enctype="multipart/form-data">
<div class="modal-body">
<input type="hidden" name="id" id="contractId" value="">

<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Employee <span class="text-danger">*</span></label>
<select name="staff_id" id="contractStaffId" class="form-select" required>
<option value="">Select employee</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>" data-designation="<?php echo htmlspecialchars($s['designation'] ?? ''); ?>">
<?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Contract Type <span class="text-danger">*</span></label>
<select name="contract_type" id="contractType" class="form-select" required>
<option value="Permanent">Permanent</option>
<option value="Fixed_Term">Fixed Term</option>
<option value="Probation">Probation</option>
<option value="Consultancy">Consultancy</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Start Date <span class="text-danger">*</span></label>
<input type="date" name="start_date" id="contractStart" class="form-control" required>
</div>
<div class="col-md-4">
<label class="form-label">End Date <span class="text-muted small" id="endDateHint">(optional for permanent)</span></label>
<input type="date" name="end_date" id="contractEnd" class="form-control">
</div>
<div class="col-md-4">
<label class="form-label">Monthly Salary (<?php echo CURRENCY_SYMBOL; ?>)</label>
<input type="number" step="0.01" min="0" name="salary_amount" id="contractSalary" class="form-control" placeholder="0.00">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select name="status" id="contractStatus" class="form-select">
<option value="Draft">Draft</option>
<option value="Active" selected>Active</option>
</select>
</div>
<div class="col-md-8">
<label class="form-label">Contract Document</label>
<input type="file" name="contract_file" id="contractFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
<small class="text-muted">PDF or image of signed contract (optional)</small>
<div id="existingFileHint" class="small text-info mt-1 d-none"></div>
</div>
<div class="col-12">
<label class="form-label">Notes</label>
<textarea name="notes" id="contractNotes" class="form-control" rows="2" placeholder="Terms, clauses, or internal remarks"></textarea>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Contract</button>
</div>
</form>
</div>
</div>
</div>

<!-- View Contract Modal -->
<div class="modal fade" id="viewContractModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="ri-eye-line"></i> Contract Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" id="viewContractBody"></div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>

<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Renew Contract</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" id="renewContractId">
<p class="text-muted" id="renewContractLabel"></p>
<div class="mb-3"><label class="form-label">New Start Date</label><input type="date" id="renewStart" class="form-control"></div>
<div class="mb-3"><label class="form-label">New End Date</label><input type="date" id="renewEnd" class="form-control"></div>
<div class="mb-3"><label class="form-label">Salary (<?php echo CURRENCY_SYMBOL; ?>)</label><input type="number" step="0.01" id="renewSalary" class="form-control"></div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-success" id="confirmRenewBtn">Confirm Renewal</button>
</div>
</div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var contractsCache = [];
    var contractModal = null;
    var renewModal = null;
    var viewModal = null;

    function statusBadge(c) {
        var cls = 'secondary';
        if (c.status === 'Active') cls = 'success';
        else if (c.status === 'Draft') cls = 'info';
        else if (c.status === 'Terminated' || c.status === 'Expired') cls = 'danger';
        else if (c.status === 'Renewed') cls = 'warning';
        var extra = '';
        if (c.status === 'Active' && c.end_date && c.days_to_expiry !== null) {
            var d = parseInt(c.days_to_expiry, 10);
            if (d < 0) extra = ' <span class="badge bg-danger">Overdue</span>';
            else if (d <= 30) extra = ' <span class="badge bg-warning text-dark">' + d + 'd left</span>';
        }
        return H.badge(c.status, cls) + extra;
    }

    function formatMoney(n) {
        if (n === null || n === undefined || n === '') return '—';
        return '<?php echo CURRENCY_SYMBOL; ?>' + parseFloat(n).toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    function buildQuery() {
        var q = [];
        var s = document.getElementById('filterSearch').value.trim();
        var staff = document.getElementById('filterStaff').value;
        var status = document.getElementById('filterStatus').value;
        var type = document.getElementById('filterType').value;
        if (s) q.push('q=' + encodeURIComponent(s));
        if (staff) q.push('staff_id=' + staff);
        if (status) q.push('status=' + encodeURIComponent(status));
        if (type) q.push('contract_type=' + encodeURIComponent(type));
        return q.length ? '?' + q.join('&') : '';
    }

    function load() {
        var tb = document.querySelector('#contractsTable tbody');
        tb.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-contracts.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data;
            contractsCache = payload.contracts || [];
            var stats = payload.stats || {};
            document.getElementById('statTotal').textContent = stats.total || 0;
            document.getElementById('statActive').textContent = stats.active || 0;
            document.getElementById('statExpiring').textContent = stats.expiring_soon || 0;
            document.getElementById('statExpired').textContent = stats.expired || 0;

            if (!contractsCache.length) {
                tb.innerHTML = '<tr><td colspan="8" class="text-muted text-center py-4">No contracts found</td></tr>';
                return;
            }
            tb.innerHTML = contractsCache.map(function (c) {
                var period = H.formatDate(c.start_date) + ' → ' + (c.end_date ? H.formatDate(c.end_date) : 'Open-ended');
                var file = c.file_path
                    ? '<a href="' + H.apiUrl() + c.file_path + '" target="_blank" class="btn btn-sm btn-link p-0">View</a>'
                    : '<span class="text-muted">—</span>';
                var actions =
                    '<div class="btn-group btn-group-sm">' +
                    '<button type="button" class="btn btn-outline-info btn-view" data-id="' + c.id + '" title="View"><i class="ri-eye-line"></i></button>' +
                    '<button type="button" class="btn btn-outline-primary btn-edit" data-id="' + c.id + '" title="Edit"><i class="ri-edit-line"></i></button>';
                if (c.status === 'Active' || c.status === 'Draft') {
                    actions += '<button type="button" class="btn btn-outline-success btn-renew" data-id="' + c.id + '" title="Renew"><i class="ri-refresh-line"></i></button>';
                    actions += '<button type="button" class="btn btn-outline-danger btn-terminate" data-id="' + c.id + '" title="Terminate"><i class="ri-close-line"></i></button>';
                }
                actions += '</div>';
                return '<tr><td><strong>' + H.escapeHtml(c.contract_no) + '</strong></td>' +
                    '<td><div>' + H.escapeHtml(c.first_name + ' ' + c.last_name) + '</div><small class="text-muted">' + H.escapeHtml(c.staff_code) + '</small></td>' +
                    '<td>' + H.escapeHtml(String(c.contract_type).replace(/_/g, ' ')) + '</td>' +
                    '<td>' + period + '</td>' +
                    '<td>' + formatMoney(c.salary_amount) + '</td>' +
                    '<td>' + statusBadge(c) + '</td>' +
                    '<td>' + file + '</td>' +
                    '<td>' + actions + '</td></tr>';
            }).join('');
        }).catch(function (e) { H.error(e.message || 'Failed to load'); });
    }

    function openContractForm(data) {
        document.getElementById('contractForm').reset();
        document.getElementById('contractId').value = data ? data.id : '';
        document.getElementById('contractModalTitle').innerHTML = data
            ? '<i class="ri-edit-line"></i> Edit Contract'
            : '<i class="ri-file-add-line"></i> New Contract';
        document.getElementById('existingFileHint').classList.add('d-none');
        if (data) {
            document.getElementById('contractStaffId').value = data.staff_id;
            document.getElementById('contractType').value = data.contract_type;
            document.getElementById('contractStart').value = (data.start_date || '').substring(0, 10);
            document.getElementById('contractEnd').value = data.end_date ? data.end_date.substring(0, 10) : '';
            document.getElementById('contractSalary').value = data.salary_amount || '';
            document.getElementById('contractStatus').value = data.status || 'Active';
            document.getElementById('contractNotes').value = data.notes || '';
            if (data.file_path) {
                var hint = document.getElementById('existingFileHint');
                hint.innerHTML = 'Current file: <a href="' + H.apiUrl() + data.file_path + '" target="_blank">View attached contract</a>';
                hint.classList.remove('d-none');
            }
        } else {
            document.getElementById('contractStart').value = new Date().toISOString().slice(0, 10);
            document.getElementById('contractStatus').value = 'Active';
        }
        toggleEndDateRequired();
        if (!contractModal) contractModal = new bootstrap.Modal(document.getElementById('contractModal'));
        contractModal.show();
    }

    function toggleEndDateRequired() {
        var type = document.getElementById('contractType').value;
        var end = document.getElementById('contractEnd');
        var hint = document.getElementById('endDateHint');
        if (type === 'Permanent') {
            end.removeAttribute('required');
            hint.textContent = '(optional for permanent)';
        } else {
            end.setAttribute('required', 'required');
            hint.textContent = '(required)';
        }
    }

    function viewContract(id) {
        var c = contractsCache.find(function (x) { return String(x.id) === String(id); });
        if (!c) return;
        var html = '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Contract No</strong><br>' + H.escapeHtml(c.contract_no) + '</div>' +
            '<div class="col-md-6"><strong>Status</strong><br>' + statusBadge(c) + '</div>' +
            '<div class="col-md-6"><strong>Employee</strong><br>' + H.escapeHtml(c.first_name + ' ' + c.last_name) + ' (' + H.escapeHtml(c.staff_code) + ')</div>' +
            '<div class="col-md-6"><strong>Designation</strong><br>' + H.escapeHtml(c.designation || '—') + '</div>' +
            '<div class="col-md-4"><strong>Type</strong><br>' + H.escapeHtml(String(c.contract_type).replace(/_/g, ' ')) + '</div>' +
            '<div class="col-md-4"><strong>Start</strong><br>' + H.formatDate(c.start_date) + '</div>' +
            '<div class="col-md-4"><strong>End</strong><br>' + (c.end_date ? H.formatDate(c.end_date) : 'Open-ended') + '</div>' +
            '<div class="col-md-6"><strong>Salary</strong><br>' + formatMoney(c.salary_amount) + '</div>' +
            '<div class="col-md-6"><strong>Document</strong><br>' + (c.file_path ? '<a href="' + H.apiUrl() + c.file_path + '" target="_blank">Download contract file</a>' : '—') + '</div>' +
            '<div class="col-12"><strong>Notes</strong><br>' + H.escapeHtml(c.notes || '—') + '</div>' +
            '</div>';
        document.getElementById('viewContractBody').innerHTML = html;
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewContractModal'));
        viewModal.show();
    }

    document.getElementById('btnNewContract').addEventListener('click', function () { openContractForm(null); });
    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('btnReset').addEventListener('click', function () {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterStaff').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterType').value = '';
        load();
    });
    document.getElementById('contractType').addEventListener('change', toggleEndDateRequired);

    document.getElementById('contractForm').addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(H.apiUrl() + 'ajax/hr/save-contract.php', { method: 'POST', body: new FormData(this) })
            .then(H.parseJson)
            .then(function (res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('contractModal')).hide();
                    H.success(res.message, load);
                } else H.error(res.message);
            })
            .catch(function () { H.error('Failed to save contract'); });
    });

    document.getElementById('confirmRenewBtn').addEventListener('click', function () {
        H.post('ajax/hr/update-contract-status.php', {
            contract_id: document.getElementById('renewContractId').value,
            action: 'renew',
            start_date: document.getElementById('renewStart').value,
            end_date: document.getElementById('renewEnd').value,
            salary_amount: document.getElementById('renewSalary').value
        }).then(function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('renewModal')).hide();
                H.success(res.message + (res.data && res.data.contract_no ? ' (' + res.data.contract_no + ')' : ''), load);
            } else H.error(res.message);
        });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        if (btn.classList.contains('btn-view')) viewContract(btn.dataset.id);
        if (btn.classList.contains('btn-edit')) {
            var c = contractsCache.find(function (x) { return String(x.id) === btn.dataset.id; });
            if (c) openContractForm(c);
        }
        if (btn.classList.contains('btn-renew')) {
            var cr = contractsCache.find(function (x) { return String(x.id) === btn.dataset.id; });
            if (!cr) return;
            document.getElementById('renewContractId').value = cr.id;
            document.getElementById('renewContractLabel').textContent = cr.contract_no + ' — ' + cr.first_name + ' ' + cr.last_name;
            document.getElementById('renewStart').value = new Date().toISOString().slice(0, 10);
            document.getElementById('renewEnd').value = cr.end_date ? cr.end_date.substring(0, 10) : '';
            document.getElementById('renewSalary').value = cr.salary_amount || '';
            if (!renewModal) renewModal = new bootstrap.Modal(document.getElementById('renewModal'));
            renewModal.show();
        }
        if (btn.classList.contains('btn-terminate')) {
            var tid = btn.dataset.id;
            Swal.fire({
                title: 'Terminate contract?',
                text: 'This will mark the contract as terminated.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Terminate'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                H.post('ajax/hr/update-contract-status.php', { contract_id: tid, action: 'terminate' })
                    .then(function (res) { res.success ? H.success(res.message, load) : H.error(res.message); });
            });
        }
    });

    document.getElementById('filterSearch').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); load(); }
    });

    load();
})();
</script>
