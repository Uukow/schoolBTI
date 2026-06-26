<?php
/**
 * Attendance Corrections & Adjustments
 */
require_once '../../config/config.php';
hrRequireAccess('hr_attendance', 'view');

$pageTitle = 'Attendance Corrections';
$currentUser = getCurrentUser();
$isStaffRole = hasRole(['Teacher', 'Staff']);
$canApprove = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_attendance', 'approve'));
$canSubmitForOthers = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_attendance', 'create'));

$staffRow = fetchOne(executeQuery(
    "SELECT id, staff_id, first_name, last_name, department FROM staff WHERE user_id = ?",
    'i',
    [$currentUser['id']]
));
$myStaffId = (int)($staffRow['id'] ?? 0);

$staff = fetchAll(executeQuery(
    "SELECT id, staff_id, first_name, last_name, designation, department
     FROM staff WHERE status = 'Active' ORDER BY first_name, last_name"
));
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
<button type="button" class="btn btn-primary" id="btnRequestCorrection">
<i class="ri-edit-line"></i> Request Correction
</button>
</div>
<h4 class="page-title">Attendance Corrections</h4>
<p class="text-muted mb-0 small">Request and approve attendance record adjustments — two-level approval workflow</p>
</div>
</div>
</div>

<!-- KPI -->
<div class="row" id="corrStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-file-list-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Total</p><h5 class="mb-0" id="statTotal">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-time-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Pending</p><h5 class="mb-0" id="statSubmitted">—</h5></div>
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
<div><p class="text-muted mb-0 small">HR Approved</p><h5 class="mb-0" id="statApproved">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-danger-lighten text-danger rounded p-2 me-2"><i class="ri-close-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Rejected</p><h5 class="mb-0" id="statRejected">—</h5></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-2">
<label class="form-label small">Year</label>
<select id="filterYear" class="form-select">
<option value="" selected>All</option>
<?php for ($y = $year - 1; $y <= $year + 1; $y++): ?>
<option value="<?php echo $y; ?>"><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Month</label>
<input type="month" id="filterMonth" class="form-control" value="">
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select">
<option value="">All</option>
<option value="Submitted">Submitted</option>
<option value="Manager_Approved">Manager Approved</option>
<option value="HR_Approved">HR Approved</option>
<option value="Rejected">Rejected</option>
</select>
</div>
<?php if (!$isStaffRole): ?>
<div class="col-md-2">
<label class="form-label small">Employee</label>
<select id="filterStaff" class="form-select">
<option value="">All Staff</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>">
<?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
</option>
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
<?php if ($myStaffId && !$isStaffRole): ?>
<div class="col-md-2">
<div class="form-check mt-4">
<input type="checkbox" class="form-check-input" id="filterMine">
<label class="form-check-label" for="filterMine">My requests only</label>
</div>
</div>
<?php endif; ?>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>

<!-- Table -->
<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="correctionsTable">
<thead class="table-light">
<tr>
<th>Employee</th>
<th>Date</th>
<th>Current → Requested</th>
<th>Check In / Out</th>
<th>Reason</th>
<th>Status</th>
<th>Submitted</th>
<th style="min-width:140px">Actions</th>
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

<!-- Request modal -->
<div class="modal fade" id="correctionModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="ri-edit-line"></i> Request Attendance Correction</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="alert alert-light border small mb-3">
<i class="ri-information-line text-primary"></i>
Submit a correction when your attendance was recorded incorrectly. HR will review and update the official record after approval.
</div>
<form id="correctionForm">
<div class="row g-3">
<?php if ($canSubmitForOthers): ?>
<div class="col-md-6">
<label class="form-label">Employee <span class="text-danger">*</span></label>
<select class="form-select" id="corrStaffId" required>
<option value="">— Select employee —</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>" <?php echo (int)$s['id'] === $myStaffId ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
<?php if ($s['department']): ?> (<?php echo htmlspecialchars($s['department']); ?>)<?php endif; ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<input type="hidden" id="corrStaffId" value="<?php echo $myStaffId; ?>">
<div class="col-md-6">
<label class="form-label">Employee</label>
<input type="text" class="form-control bg-light" readonly
value="<?php echo htmlspecialchars(($staffRow['staff_id'] ?? '') . ' — ' . ($staffRow['first_name'] ?? '') . ' ' . ($staffRow['last_name'] ?? '')); ?>">
</div>
<?php endif; ?>
<div class="col-md-6">
<label class="form-label">Attendance Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="corrDate" required max="<?php echo date('Y-m-d'); ?>">
</div>
<div class="col-md-6">
<label class="form-label">Requested Status <span class="text-danger">*</span></label>
<select class="form-select" id="corrStatus" required>
<option value="Present">Present</option>
<option value="Late">Late</option>
<option value="Half Day">Half Day</option>
<option value="Absent">Absent</option>
<option value="Leave">On Leave</option>
</select>
</div>
<div class="col-md-3">
<label class="form-label">Check In</label>
<input type="time" class="form-control" id="corrCheckIn">
</div>
<div class="col-md-3">
<label class="form-label">Check Out</label>
<input type="time" class="form-control" id="corrCheckOut">
</div>
<div class="col-12">
<label class="form-label">Reason <span class="text-danger">*</span></label>
<textarea class="form-control" id="corrReason" rows="3" required placeholder="Explain why this correction is needed…"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="submitCorrectionBtn"><i class="ri-send-plane-line"></i> Submit Request</button>
</div>
</div>
</div>
</div>

<!-- View modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Correction Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
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
    var cache = [];
    var correctionModal = null;
    var viewModal = null;

    var statusMap = {
        Submitted: 'warning',
        Manager_Approved: 'info',
        HR_Approved: 'success',
        Rejected: 'danger'
    };

    function badge(s) {
        return H.badge(String(s).replace(/_/g, ' '), statusMap[s] || 'secondary');
    }

    function buildQuery() {
        var q = ['action=list'];
        var year = document.getElementById('filterYear').value;
        if (year) q.push('year=' + year);
        var month = document.getElementById('filterMonth').value;
        if (month) q.push('month=' + month);
        var st = document.getElementById('filterStatus').value;
        if (st) q.push('status=' + encodeURIComponent(st));
        var staff = document.getElementById('filterStaff');
        if (staff && staff.value) q.push('staff_id=' + staff.value);
        var dept = document.getElementById('filterDepartment');
        if (dept && dept.value) q.push('department=' + encodeURIComponent(dept.value));
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        var mine = document.getElementById('filterMine');
        if (mine && mine.checked) q.push('mine=1');
        return '?' + q.join('&');
    }

    function updateStats(s) {
        s = s || {};
        document.getElementById('statTotal').textContent = s.total || 0;
        document.getElementById('statSubmitted').textContent = s.submitted || 0;
        document.getElementById('statManager').textContent = s.manager_approved || 0;
        document.getElementById('statApproved').textContent = s.hr_approved || 0;
        document.getElementById('statRejected').textContent = s.rejected || 0;
    }

    function statusChange(c) {
        var cur = c.current_status || '—';
        return '<span class="text-muted">' + H.escapeHtml(cur) + '</span> → <strong>' + H.escapeHtml(c.requested_status) + '</strong>';
    }

    function actions(c) {
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view" data-id="' + c.id + '" title="View"><i class="ri-eye-line"></i></button>';
        if (CAN_APPROVE) {
            html += '<div class="btn-group btn-group-sm">' +
                '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">';
            if (c.status === 'Submitted') {
                html += '<li><a class="dropdown-item corr-action" data-id="' + c.id + '" data-decision="manager_approve" href="#">Manager approve (L1)</a></li>';
                html += '<li><a class="dropdown-item corr-action text-success" data-id="' + c.id + '" data-decision="hr_approve" href="#">HR approve (skip L1)</a></li>';
            }
            if (c.status === 'Manager_Approved') {
                html += '<li><a class="dropdown-item corr-action text-success" data-id="' + c.id + '" data-decision="hr_approve" href="#">HR final approve</a></li>';
            }
            if (['Submitted', 'Manager_Approved'].indexOf(c.status) >= 0) {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item corr-action text-danger" data-id="' + c.id + '" data-decision="reject" href="#">Reject</a></li>';
            }
            html += '</ul></div>';
        }
        html += '</div>';
        return html;
    }

    function load() {
        var tb = document.querySelector('#correctionsTable tbody');
        tb.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/attendance-corrections.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            cache = Array.isArray(payload) ? payload : (payload.corrections || []);
            if (!Array.isArray(payload)) {
                updateStats(payload.stats);
            }
            if (!cache.length) {
                tb.innerHTML = '<tr><td colspan="8" class="text-muted text-center py-4">No correction requests found</td></tr>';
                return;
            }
            tb.innerHTML = cache.map(function (c) {
                return '<tr>' +
                    '<td><strong>' + H.escapeHtml(c.first_name + ' ' + c.last_name) + '</strong>' +
                    '<br><small class="text-muted">' + H.escapeHtml(c.staff_code) +
                    (c.department ? ' · ' + H.escapeHtml(c.department) : '') + '</small></td>' +
                    '<td>' + H.formatDate(c.attendance_date) + '</td>' +
                    '<td>' + statusChange(c) + '</td>' +
                    '<td><small>' + (c.requested_check_in ? c.requested_check_in.substring(0, 5) : '—') +
                    ' / ' + (c.requested_check_out ? c.requested_check_out.substring(0, 5) : '—') + '</small></td>' +
                    '<td><small>' + H.escapeHtml((c.reason || '').substring(0, 40)) + ((c.reason || '').length > 40 ? '…' : '') + '</small></td>' +
                    '<td>' + badge(c.status) + '</td>' +
                    '<td><small>' + H.formatDate(c.created_at) + '</small></td>' +
                    '<td>' + actions(c) + '</td></tr>';
            }).join('');
        });
    }

    function viewCorrection(id) {
        var c = cache.find(function (x) { return String(x.id) === String(id); });
        if (!c) return;
        document.getElementById('viewBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Employee</strong><br>' + H.escapeHtml(c.first_name + ' ' + c.last_name) +
            '<br><small class="text-muted">' + H.escapeHtml(c.staff_code) + ' · ' + H.escapeHtml(c.designation || '') + '</small></div>' +
            '<div class="col-md-3"><strong>Date</strong><br>' + H.formatDate(c.attendance_date) + '</div>' +
            '<div class="col-md-3"><strong>Status</strong><br>' + badge(c.status) + '</div>' +
            '<div class="col-md-6"><strong>Current record</strong><br>' +
            (c.current_status ? H.escapeHtml(c.current_status) + ' — ' + (c.current_check_in || '—') + ' / ' + (c.current_check_out || '—') : '<span class="text-muted">No record (new entry)</span>') + '</div>' +
            '<div class="col-md-6"><strong>Requested</strong><br>' + H.escapeHtml(c.requested_status) + ' — ' +
            (c.requested_check_in || '—') + ' / ' + (c.requested_check_out || '—') + '</div>' +
            '<div class="col-12"><strong>Reason</strong><br>' + H.escapeHtml(c.reason || '').replace(/\n/g, '<br>') + '</div>' +
            (c.rejection_reason ? '<div class="col-12"><strong class="text-danger">Rejection</strong><br>' + H.escapeHtml(c.rejection_reason) + '</div>' : '') +
            '<div class="col-md-6"><strong>Branch</strong><br>' + H.escapeHtml(c.branch_name || '—') + '</div>' +
            '<div class="col-md-6"><strong>Submitted</strong><br>' + (c.created_at || '').substring(0, 16).replace('T', ' ') + '</div>' +
            '</div>';
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
        viewModal.show();
    }

    function approve(id, decision, reason) {
        H.post('ajax/hr/attendance-corrections.php', {
            action: 'approve',
            id: id,
            decision: decision,
            rejection_reason: reason || ''
        }).then(function (r) {
            r.success ? H.success(r.message, load) : H.error(r.message);
        });
    }

    document.getElementById('btnRequestCorrection').addEventListener('click', function () {
        document.getElementById('correctionForm').reset();
        if (MY_STAFF_ID) {
            var sel = document.getElementById('corrStaffId');
            if (sel && sel.tagName === 'SELECT') sel.value = MY_STAFF_ID;
        }
        if (!correctionModal) correctionModal = new bootstrap.Modal(document.getElementById('correctionModal'));
        correctionModal.show();
    });

    document.getElementById('submitCorrectionBtn').addEventListener('click', function () {
        var staffEl = document.getElementById('corrStaffId');
        var staffId = staffEl.tagName === 'SELECT' ? staffEl.value : staffEl.value;
        var payload = {
            action: 'submit',
            staff_id: staffId,
            attendance_date: document.getElementById('corrDate').value,
            requested_status: document.getElementById('corrStatus').value,
            requested_check_in: document.getElementById('corrCheckIn').value,
            requested_check_out: document.getElementById('corrCheckOut').value,
            reason: document.getElementById('corrReason').value.trim()
        };
        if (!staffId || !payload.attendance_date || !payload.reason) {
            H.error('Employee, date, and reason are required'); return;
        }
        H.post('ajax/hr/attendance-corrections.php', payload).then(function (r) {
            if (r.success) {
                bootstrap.Modal.getInstance(document.getElementById('correctionModal')).hide();
                H.success(r.message, load);
            } else H.error(r.message);
        });
    });

    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('filterYear').addEventListener('change', load);
    var filterMine = document.getElementById('filterMine');
    if (filterMine) filterMine.addEventListener('change', load);

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id]');
        if (!t) return;
        var id = t.dataset.id;
        if (t.classList.contains('btn-view')) { viewCorrection(id); return; }
        if (t.classList.contains('corr-action')) {
            e.preventDefault();
            var decision = t.dataset.decision;
            if (decision === 'reject') {
                Swal.fire({
                    title: 'Reject Correction',
                    input: 'textarea',
                    inputPlaceholder: 'Rejection reason…',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    inputValidator: function (v) { if (!v) return 'Reason required'; }
                }).then(function (r) { if (r.isConfirmed) approve(id, decision, r.value); });
            } else {
                Swal.fire({
                    title: decision === 'hr_approve' ? 'HR Approve?' : 'Manager Approve?',
                    text: 'This will advance the correction in the approval workflow.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, approve'
                }).then(function (r) { if (r.isConfirmed) approve(id, decision); });
            }
        }
    });

    load();
})();
</script>
