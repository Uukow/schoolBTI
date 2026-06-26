<?php
/**
 * Leave Management — applications, balances, multi-level approval
 */
require_once '../../config/config.php';
hrRequireAccess('hr_leave', 'view');

$pageTitle = 'Leave Management';
$currentUser = getCurrentUser();
$isStaffRole = hasRole(['Teacher', 'Staff']);
$canApprove = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_leave', 'approve'));
$canApplyForOthers = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_leave', 'create'));

$staffRow = fetchOne(executeQuery("SELECT id, staff_id, first_name, last_name FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
$myStaffId = (int)($staffRow['id'] ?? 0);

$staff = fetchAll(executeQuery(
    "SELECT id, staff_id, first_name, last_name, department FROM staff WHERE status = 'Active' ORDER BY first_name"
));
$leaveTypes = fetchAll(executeQuery("SELECT id, leave_name, leave_code, days_allowed FROM leave_types ORDER BY leave_name"));
$departments = fetchAll(executeQuery(
    "SELECT DISTINCT department FROM staff WHERE department IS NOT NULL AND department != '' ORDER BY department"
));
$year = (int)date('Y');

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
<a href="<?php echo APP_URL; ?>modules/hr/leave-calendar.php" class="btn btn-outline-info me-1">
<i class="ri-calendar-line"></i> Calendar
</a>
<button type="button" class="btn btn-primary" id="btnApplyLeave">
<i class="ri-add-line"></i> Apply for Leave
</button>
</div>
<h4 class="page-title">Leave Management</h4>
<p class="text-muted mb-0 small">Leave applications, balances, and multi-level approval workflow</p>
</div>
</div>
</div>

<!-- KPI -->
<div class="row" id="leaveStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-file-list-3-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Total</p><h5 class="mb-0" id="statTotal">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-time-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Pending</p><h5 class="mb-0" id="statPending">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-user-follow-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Mgr Approved</p><h5 class="mb-0" id="statManager">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-check-double-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Approved</p><h5 class="mb-0" id="statApproved">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-danger-lighten text-danger rounded p-2 me-2"><i class="ri-close-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Rejected</p><h5 class="mb-0" id="statRejected">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-purple-lighten text-purple rounded p-2 me-2"><i class="ri-calendar-check-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Days Approved</p><h5 class="mb-0" id="statDays">—</h5></div>
</div></div></div>
</div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabApplications" type="button">
<i class="ri-file-list-line"></i> Applications</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBalances" type="button" id="tabBalancesBtn">
<i class="ri-pie-chart-line"></i> Leave Balances</button></li>
</ul>

<div class="tab-content">
<!-- Applications -->
<div class="tab-pane fade show active" id="tabApplications">

<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-2">
<label class="form-label small">Year</label>
<select id="filterYear" class="form-select">
<option value="">All</option>
<?php for ($y = $year - 1; $y <= $year + 1; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Month</label>
<input type="month" id="filterMonth" class="form-control" value="">
</div>
<div class="col-md-2">
<label class="form-label small">Stage</label>
<select id="filterStage" class="form-select">
<option value="">All</option>
<option value="Pending">Pending</option>
<option value="Manager_Approved">Manager Approved</option>
<option value="Approved">Approved</option>
<option value="Rejected">Rejected</option>
<option value="Cancelled">Cancelled</option>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Leave Type</label>
<select id="filterLeaveType" class="form-select">
<option value="">All</option>
<?php foreach ($leaveTypes as $lt): ?>
<option value="<?php echo (int)$lt['id']; ?>"><?php echo htmlspecialchars($lt['leave_code']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php if (!$isStaffRole): ?>
<div class="col-md-2">
<label class="form-label small">Employee</label>
<select id="filterStaff" class="form-select">
<option value="">All</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Department</label>
<select id="filterDepartment" class="form-select">
<option value="">All</option>
<?php foreach ($departments as $d): ?>
<option value="<?php echo htmlspecialchars($d['department']); ?>"><?php echo htmlspecialchars($d['department']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-2">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Name, reason…">
</div>
<?php if (!$isStaffRole): ?>
<div class="col-md-2">
<div class="form-check mt-4">
<input type="checkbox" class="form-check-input" id="filterMine">
<label class="form-check-label" for="filterMine">My leaves only</label>
</div>
</div>
<?php endif; ?>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>

<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="leavesTable">
<thead class="table-light">
<tr>
<th>Employee</th>
<th>Leave Type</th>
<th>Period</th>
<th>Days</th>
<th>Reason</th>
<th>Status</th>
<th>Applied</th>
<th style="min-width:150px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="8" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>

<!-- Balances -->
<div class="tab-pane fade" id="tabBalances">
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<?php if ($canApplyForOthers): ?>
<div class="col-md-4">
<label class="form-label small">Employee</label>
<select id="balanceStaff" class="form-select">
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>" <?php echo (int)$s['id'] === $myStaffId ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<input type="hidden" id="balanceStaff" value="<?php echo $myStaffId; ?>">
<?php endif; ?>
<div class="col-md-2">
<label class="form-label small">Year</label>
<select id="balanceYear" class="form-select">
<?php for ($y = $year - 1; $y <= $year + 1; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col-md-2">
<button type="button" class="btn btn-primary" id="btnLoadBalances"><i class="ri-refresh-line"></i> Load</button>
</div>
</div>
</div></div>
<div id="balanceOutput"><p class="text-muted text-center py-4">Select employee and load balances.</p></div>
</div>
</div>

</div>
</div>
</div>

<!-- Apply -->
<div class="modal fade" id="applyModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="ri-calendar-todo-line"></i> Apply for Leave</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div id="applyBalanceHint" class="alert alert-light border small d-none"></div>
<form id="applyForm">
<div class="row g-3">
<?php if ($canApplyForOthers): ?>
<div class="col-md-6">
<label class="form-label">Employee <span class="text-danger">*</span></label>
<select class="form-select" id="applyStaff" required>
<option value="">— Select —</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>" <?php echo (int)$s['id'] === $myStaffId ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<input type="hidden" id="applyStaff" value="<?php echo $myStaffId; ?>">
<?php endif; ?>
<div class="col-md-6">
<label class="form-label">Leave Type <span class="text-danger">*</span></label>
<select class="form-select" id="applyLeaveType" required>
<option value="">— Select —</option>
<?php foreach ($leaveTypes as $lt): ?>
<option value="<?php echo (int)$lt['id']; ?>" data-days="<?php echo (float)$lt['days_allowed']; ?>">
<?php echo htmlspecialchars($lt['leave_name'] . ' (' . $lt['leave_code'] . ')'); ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Start Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="applyStart" required>
</div>
<div class="col-md-4">
<label class="form-label">End Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="applyEnd" required>
</div>
<div class="col-md-4">
<label class="form-label">Total Days</label>
<input type="number" class="form-control" id="applyDays" readonly step="0.5">
</div>
<div class="col-12">
<label class="form-label">Reason <span class="text-danger">*</span></label>
<textarea class="form-control" id="applyReason" rows="3" required placeholder="Purpose of leave…"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="btnSubmitApply"><i class="ri-send-plane-line"></i> Submit</button>
</div>
</div>
</div>
</div>

<!-- View -->
<div class="modal fade" id="viewModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Leave Application</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" id="viewBody"></div>
<div class="modal-footer">
<a href="#" class="btn btn-outline-secondary btn-sm" id="viewPrintLink" target="_blank"><i class="ri-printer-line"></i> Print</a>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var CAN_APPROVE = <?php echo $canApprove ? 'true' : 'false'; ?>;
    var MY_STAFF_ID = <?php echo $myStaffId; ?>;
    var APP_URL = H.apiUrl();
    var cache = [];
    var applyModal = null;
    var viewModal = null;

    var statusMap = {
        Pending: 'warning',
        Manager_Approved: 'info',
        Approved: 'success',
        Rejected: 'danger',
        Cancelled: 'dark'
    };

    function badge(stage) {
        return H.badge(String(stage).replace(/_/g, ' '), statusMap[stage] || 'secondary');
    }

    function buildQuery() {
        var q = [];
        var year = document.getElementById('filterYear').value;
        if (year) q.push('year=' + year);
        var month = document.getElementById('filterMonth').value;
        if (month) q.push('month=' + month);
        var stage = document.getElementById('filterStage').value;
        if (stage) q.push('approval_stage=' + encodeURIComponent(stage));
        var lt = document.getElementById('filterLeaveType');
        if (lt && lt.value) q.push('leave_type_id=' + lt.value);
        var staff = document.getElementById('filterStaff');
        if (staff && staff.value) q.push('staff_id=' + staff.value);
        var dept = document.getElementById('filterDepartment');
        if (dept && dept.value) q.push('department=' + encodeURIComponent(dept.value));
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        var mine = document.getElementById('filterMine');
        if (mine && mine.checked) q.push('mine=1');
        return q.length ? '?' + q.join('&') : '';
    }

    function updateStats(s) {
        s = s || {};
        document.getElementById('statTotal').textContent = s.total || 0;
        document.getElementById('statPending').textContent = s.pending || 0;
        document.getElementById('statManager').textContent = s.manager_approved || 0;
        document.getElementById('statApproved').textContent = s.approved || 0;
        document.getElementById('statRejected').textContent = s.rejected || 0;
        document.getElementById('statDays').textContent = s.days_approved || 0;
    }

    function actions(l) {
        var stage = l.display_status || l.approval_stage || l.status;
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view" data-id="' + l.id + '" title="View"><i class="ri-eye-line"></i></button>';
        if (CAN_APPROVE) {
            html += '<div class="btn-group btn-group-sm">' +
                '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">';
            if (stage === 'Pending') {
                html += '<li><a class="dropdown-item lv-action" data-id="' + l.id + '" data-action="manager_approve" href="#">Manager approve (L1)</a></li>';
            }
            if (['Pending', 'Manager_Approved'].indexOf(stage) >= 0) {
                html += '<li><a class="dropdown-item text-success lv-action" data-id="' + l.id + '" data-action="hr_approve" href="#">Final approve (HR)</a></li>';
                html += '<li><a class="dropdown-item text-danger lv-action" data-id="' + l.id + '" data-action="reject" href="#">Reject</a></li>';
            }
            html += '</ul></div>';
        }
        if (['Pending', 'Manager_Approved', 'Approved'].indexOf(stage) >= 0 &&
            (CAN_APPROVE || Number(l.staff_id) === MY_STAFF_ID)) {
            html += '<button type="button" class="btn btn-outline-warning btn-cancel" data-id="' + l.id + '" title="Cancel"><i class="ri-close-circle-line"></i></button>';
        }
        html += '</div>';
        return html;
    }

    function load() {
        var tb = document.querySelector('#leavesTable tbody');
        tb.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-leave-applications.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            cache = payload.applications || [];
            updateStats(payload.stats);
            if (!cache.length) {
                tb.innerHTML = '<tr><td colspan="8" class="text-muted text-center py-4">No leave applications found</td></tr>';
                return;
            }
            tb.innerHTML = cache.map(function (l) {
                var stage = l.display_status || l.approval_stage || l.status;
                return '<tr>' +
                    '<td><strong>' + H.escapeHtml(l.first_name + ' ' + l.last_name) + '</strong>' +
                    '<br><small class="text-muted">' + H.escapeHtml(l.employee_code) +
                    (l.department ? ' · ' + H.escapeHtml(l.department) : '') + '</small></td>' +
                    '<td>' + H.escapeHtml(l.leave_name) + '<br><small class="text-muted">' + H.escapeHtml(l.leave_code) + '</small></td>' +
                    '<td><small>' + H.formatDate(l.start_date) + ' — ' + H.formatDate(l.end_date) + '</small></td>' +
                    '<td><strong>' + l.total_days + '</strong></td>' +
                    '<td><small>' + H.escapeHtml((l.reason || '').substring(0, 40)) + ((l.reason || '').length > 40 ? '…' : '') + '</small></td>' +
                    '<td>' + badge(stage) + '</td>' +
                    '<td><small>' + H.formatDate(l.applied_at) + '</small></td>' +
                    '<td>' + actions(l) + '</td></tr>';
            }).join('');
        });
    }

    function viewLeave(id) {
        var l = cache.find(function (x) { return String(x.id) === String(id); });
        if (!l) return;
        var stage = l.display_status || l.approval_stage || l.status;
        document.getElementById('viewBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Employee</strong><br>' + H.escapeHtml(l.first_name + ' ' + l.last_name) +
            '<br><small class="text-muted">' + H.escapeHtml(l.employee_code) + ' · ' + H.escapeHtml(l.designation || '') + '</small></div>' +
            '<div class="col-md-3"><strong>Status</strong><br>' + badge(stage) + '</div>' +
            '<div class="col-md-3"><strong>Balance left</strong><br>' + (l.balance_remaining != null ? l.balance_remaining + ' days' : '—') + '</div>' +
            '<div class="col-md-4"><strong>Leave Type</strong><br>' + H.escapeHtml(l.leave_name) + ' (' + H.escapeHtml(l.leave_code) + ')</div>' +
            '<div class="col-md-4"><strong>Period</strong><br>' + H.formatDate(l.start_date) + ' to ' + H.formatDate(l.end_date) + ' (' + l.total_days + ' days)</div>' +
            '<div class="col-md-4"><strong>Applied</strong><br>' + (l.applied_at || '').substring(0, 16).replace('T', ' ') + '</div>' +
            (l.manager_name ? '<div class="col-md-6"><strong>Manager approved by</strong><br>' + H.escapeHtml(l.manager_name) + '</div>' : '') +
            (l.approved_by_name ? '<div class="col-md-6"><strong>HR approved by</strong><br>' + H.escapeHtml(l.approved_by_name || l.approved_by_username) + '</div>' : '') +
            '<div class="col-12"><strong>Reason</strong><br>' + H.escapeHtml(l.reason || '').replace(/\n/g, '<br>') + '</div>' +
            (l.rejection_reason ? '<div class="col-12"><strong>Rejection reason</strong><br class="text-danger">' + H.escapeHtml(l.rejection_reason) + '</div>' : '') +
            '</div>';
        document.getElementById('viewPrintLink').href = APP_URL + 'modules/hr/view-leave.php?id=' + id;
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
        viewModal.show();
    }

    function doAction(id, action, extra) {
        var payload = { application_id: id };
        if (action === 'hr_approve') {
            payload.approval_action = 'hr_approve';
            payload.status = 'Approved';
        } else if (action === 'manager_approve') {
            payload.approval_action = 'manager_approve';
        } else if (action === 'reject') {
            payload.status = 'Rejected';
            payload.rejection_reason = extra || '';
        } else if (action === 'cancel') {
            payload.status = 'Cancelled';
        }
        H.post('ajax/hr/update-leave-status.php', payload).then(function (r) {
            r.success ? H.success(r.message, load) : H.error(r.message);
        });
    }

    function calcDays() {
        var s = document.getElementById('applyStart').value;
        var e = document.getElementById('applyEnd').value;
        if (!s || !e) { document.getElementById('applyDays').value = ''; return; }
        var start = new Date(s);
        var end = new Date(e);
        if (end < start) { document.getElementById('applyDays').value = ''; return; }
        var diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('applyDays').value = diff;
        updateApplyHint();
    }

    function updateApplyHint() {
        var staffEl = document.getElementById('applyStaff');
        var staffId = staffEl ? staffEl.value : MY_STAFF_ID;
        var typeId = document.getElementById('applyLeaveType').value;
        var hint = document.getElementById('applyBalanceHint');
        if (!staffId || !typeId) { hint.classList.add('d-none'); return; }
        H.get('ajax/hr/get-leave-balances.php?staff_id=' + staffId + '&year=' + new Date().getFullYear()).then(function (res) {
            if (!res.success) return;
            var bal = (res.data.balances || []).find(function (b) { return String(b.leave_type_id) === String(typeId); });
            if (bal) {
                hint.innerHTML = '<i class="ri-information-line"></i> <strong>' + H.escapeHtml(bal.leave_code) + ':</strong> ' +
                    bal.remaining_days + ' days remaining (of ' + bal.allocated_days + ' allocated)';
                hint.classList.remove('d-none');
            }
        });
    }

    function loadBalances() {
        var staffEl = document.getElementById('balanceStaff');
        var staffId = staffEl.tagName === 'SELECT' ? staffEl.value : staffEl.value;
        var year = document.getElementById('balanceYear').value;
        var out = document.getElementById('balanceOutput');
        out.innerHTML = '<p class="text-center text-muted py-4">Loading…</p>';
        H.get('ajax/hr/get-leave-balances.php?staff_id=' + staffId + '&year=' + year).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var d = res.data || {};
            var sum = d.summary || {};
            var html = '<div class="row mb-3"><div class="col-md-4"><div class="card border-primary"><div class="card-body text-center">' +
                '<h4 class="text-primary mb-0">' + sum.remaining + '</h4><small class="text-muted">Days Remaining</small></div></div></div>' +
                '<div class="col-md-4"><div class="card"><div class="card-body text-center">' +
                '<h4 class="mb-0">' + sum.used + '</h4><small class="text-muted">Days Used</small></div></div></div>' +
                '<div class="col-md-4"><div class="card"><div class="card-body text-center">' +
                '<h4 class="mb-0">' + sum.allocated + '</h4><small class="text-muted">Days Allocated</small></div></div></div></div>';
            html += '<div class="row">';
            (d.balances || []).forEach(function (b) {
                var pct = b.allocated_days > 0 ? Math.min(100, Math.round((b.used_days / b.allocated_days) * 100)) : 0;
                html += '<div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body">' +
                    '<div class="d-flex justify-content-between"><strong>' + H.escapeHtml(b.leave_name) + '</strong>' +
                    '<span class="badge bg-secondary">' + H.escapeHtml(b.leave_code) + '</span></div>' +
                    '<div class="mt-2"><span class="h3 text-primary">' + b.remaining_days + '</span> <small class="text-muted">/ ' + b.allocated_days + ' days</small></div>' +
                    '<div class="progress mt-2" style="height:6px"><div class="progress-bar bg-primary" style="width:' + pct + '%"></div></div>' +
                    '<small class="text-muted">Used: ' + b.used_days + ' · Carried: ' + b.carried_forward + '</small>' +
                    '</div></div></div>';
            });
            html += '</div>';
            out.innerHTML = html || '<p class="text-muted">No balance records.</p>';
        });
    }

    document.getElementById('btnApplyLeave').addEventListener('click', function () {
        document.getElementById('applyForm').reset();
        if (MY_STAFF_ID) {
            var sel = document.getElementById('applyStaff');
            if (sel && sel.tagName === 'SELECT') sel.value = MY_STAFF_ID;
        }
        document.getElementById('applyBalanceHint').classList.add('d-none');
        if (!applyModal) applyModal = new bootstrap.Modal(document.getElementById('applyModal'));
        applyModal.show();
    });

    document.getElementById('applyStart').addEventListener('change', calcDays);
    document.getElementById('applyEnd').addEventListener('change', calcDays);
    document.getElementById('applyLeaveType').addEventListener('change', updateApplyHint);
    var applyStaffEl = document.getElementById('applyStaff');
    if (applyStaffEl && applyStaffEl.tagName === 'SELECT') applyStaffEl.addEventListener('change', updateApplyHint);

    document.getElementById('btnSubmitApply').addEventListener('click', function () {
        var staffEl = document.getElementById('applyStaff');
        var staffId = staffEl.tagName === 'SELECT' ? staffEl.value : staffEl.value;
        var payload = {
            staff_id: staffId,
            leave_type_id: document.getElementById('applyLeaveType').value,
            start_date: document.getElementById('applyStart').value,
            end_date: document.getElementById('applyEnd').value,
            total_days: document.getElementById('applyDays').value,
            reason: document.getElementById('applyReason').value.trim()
        };
        if (!payload.staff_id || !payload.leave_type_id || !payload.start_date || !payload.end_date || !payload.total_days || !payload.reason) {
            H.error('Please complete all required fields'); return;
        }
        H.post('ajax/hr/apply-leave.php', payload).then(function (r) {
            if (r.success) {
                bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
                H.success(r.message, load);
            } else H.error(r.message);
        });
    });

    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('filterYear').addEventListener('change', load);
    var filterMine = document.getElementById('filterMine');
    if (filterMine) filterMine.addEventListener('change', load);

    document.getElementById('btnLoadBalances').addEventListener('click', loadBalances);
    document.getElementById('tabBalancesBtn').addEventListener('shown.bs.tab', loadBalances);

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id]');
        if (!t) return;
        var id = t.dataset.id;
        if (t.classList.contains('btn-view')) { viewLeave(id); return; }
        if (t.classList.contains('btn-cancel')) {
            if (confirm('Cancel this leave application?')) doAction(id, 'cancel');
            return;
        }
        if (t.classList.contains('lv-action')) {
            e.preventDefault();
            var action = t.dataset.action;
            if (action === 'reject') {
                Swal.fire({
                    title: 'Reject Leave',
                    input: 'textarea',
                    inputPlaceholder: 'Rejection reason…',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    inputValidator: function (v) { if (!v) return 'Reason required'; }
                }).then(function (r) { if (r.isConfirmed) doAction(id, 'reject', r.value); });
            } else {
                doAction(id, action);
            }
        }
    });

    load();
    if (MY_STAFF_ID) loadBalances();
})();
</script>
