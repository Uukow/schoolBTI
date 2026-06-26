<?php
/**
 * Complaints & Grievance Management
 */
require_once '../../config/config.php';
hrRequireAccess('hr_grievances', 'view');

$pageTitle = 'Grievances';
$currentUser = getCurrentUser();
$canManage = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_grievances', 'manage'));

$branches = fetchAll(executeQuery("SELECT id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name"));
$hrOfficers = fetchAll(executeQuery(
    "SELECT u.id, u.username, TRIM(CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,''))) AS full_name
     FROM users u
     INNER JOIN roles r ON u.role_id = r.id
     LEFT JOIN staff s ON s.user_id = u.id
     WHERE u.is_active = 1 AND r.role_name IN ('Super Admin', 'Admin')
     ORDER BY full_name, u.username"
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
<button type="button" class="btn btn-primary" id="btnSubmitGrievance">
<i class="ri-feedback-line"></i> Submit Grievance
</button>
</div>
<h4 class="page-title">Complaints &amp; Grievances</h4>
<p class="text-muted mb-0 small">Confidential HR complaints — supports anonymous submission and full investigation workflow</p>
</div>
</div>
</div>

<!-- KPI -->
<div class="row" id="grievanceStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-file-list-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Total Cases</p><h4 class="mb-0" id="statTotal">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-time-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Open</p><h4 class="mb-0" id="statOpen">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-search-eye-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Investigating</p><h4 class="mb-0" id="statInvestigating">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-danger-lighten text-danger rounded p-2 me-2"><i class="ri-alarm-warning-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Critical Open</p><h4 class="mb-0" id="statCritical">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-check-double-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Resolved (month)</p><h4 class="mb-0" id="statResolved">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-secondary-lighten text-secondary rounded p-2 me-2"><i class="ri-user-unfollow-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Anonymous</p><h4 class="mb-0" id="statAnonymous">—</h4></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
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
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select">
<option value="">All</option>
<option value="Submitted">Submitted</option>
<option value="Under_Review">Under Review</option>
<option value="Investigating">Investigating</option>
<option value="Escalated">Escalated</option>
<option value="Resolved">Resolved</option>
<option value="Closed">Closed</option>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Category</label>
<select id="filterCategory" class="form-select">
<option value="">All</option>
<option value="Harassment">Harassment</option>
<option value="Discrimination">Discrimination</option>
<option value="Working_Conditions">Working Conditions</option>
<option value="Payroll">Payroll</option>
<option value="Other">Other</option>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Priority</label>
<select id="filterPriority" class="form-select">
<option value="">All</option>
<option value="Critical">Critical</option>
<option value="High">High</option>
<option value="Medium">Medium</option>
<option value="Low">Low</option>
</select>
</div>
<?php if ($canManage && hasRole(['Super Admin'])): ?>
<div class="col-md-2">
<label class="form-label small">Branch</label>
<select id="filterBranch" class="form-select">
<option value="">All</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-2">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Subject, case no…">
</div>
<?php if (!$canManage): ?>
<div class="col-md-2">
<div class="form-check mt-4">
<input type="checkbox" class="form-check-input" id="filterMine" checked>
<label class="form-check-label" for="filterMine">My cases only</label>
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
<table class="table table-hover align-middle" id="grievancesTable">
<thead class="table-light">
<tr>
<th>Case No</th>
<th>Subject</th>
<th>Submitter</th>
<th>Category</th>
<th>Priority</th>
<th>Status</th>
<th>Assigned</th>
<th>Submitted</th>
<th style="min-width:150px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="9" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>

</div>
</div>
</div>

<!-- Submit -->
<div class="modal fade" id="submitModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="ri-feedback-line"></i> Submit Grievance</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="submitForm">
<div class="alert alert-light border small">
<i class="ri-shield-check-line text-success"></i>
Grievances are handled confidentially by HR. You may submit anonymously — your identity will be hidden from investigators.
</div>
<div class="form-check mb-3">
<input type="checkbox" class="form-check-input" id="anonymous">
<label class="form-check-label" for="anonymous">Submit anonymously (hide my name from HR officers)</label>
</div>
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Category</label>
<select class="form-select" id="category">
<option value="Working_Conditions">Working Conditions</option>
<option value="Payroll">Payroll</option>
<option value="Harassment">Harassment</option>
<option value="Discrimination">Discrimination</option>
<option value="Other">Other</option>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Priority</label>
<select class="form-select" id="priority">
<option value="Low">Low</option>
<option value="Medium" selected>Medium</option>
<option value="High">High</option>
<option value="Critical">Critical</option>
</select>
</div>
<div class="col-12">
<label class="form-label">Subject <span class="text-danger">*</span></label>
<input type="text" class="form-control" id="subject" required placeholder="Brief summary of the issue">
</div>
<div class="col-12">
<label class="form-label">Description <span class="text-danger">*</span></label>
<textarea class="form-control" id="description" rows="5" required placeholder="Describe what happened, when, who was involved, and what outcome you seek…"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveSubmitBtn"><i class="ri-send-plane-line"></i> Submit</button>
</div>
</div>
</div>
</div>

<!-- View -->
<div class="modal fade" id="viewModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Grievance Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<!-- Manage (HR) -->
<?php if ($canManage): ?>
<div class="modal fade" id="manageModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="manageModalTitle"><i class="ri-settings-3-line"></i> Manage Case</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" id="manageId" value="">
<div class="row g-3 mb-3">
<div class="col-md-4">
<label class="form-label">Status</label>
<select class="form-select" id="manageStatus">
<option value="Submitted">Submitted</option>
<option value="Under_Review">Under Review</option>
<option value="Investigating">Investigating</option>
<option value="Escalated">Escalated</option>
<option value="Resolved">Resolved</option>
<option value="Closed">Closed</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Assign to</label>
<select class="form-select" id="manageAssign">
<option value="">— Unassigned —</option>
<?php foreach ($hrOfficers as $o): ?>
<option value="<?php echo (int)$o['id']; ?>"><?php echo htmlspecialchars(trim($o['full_name']) ?: $o['username']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4 d-flex align-items-end">
<button type="button" class="btn btn-primary w-100" id="btnSaveManage"><i class="ri-save-line"></i> Update Case</button>
</div>
<div class="col-12">
<label class="form-label">Resolution / outcome notes</label>
<textarea class="form-control" id="manageResolution" rows="3" placeholder="Actions taken, findings, outcome communicated to employee…"></textarea>
</div>
</div>
<hr>
<h6 class="mb-2">Add note</h6>
<div class="row g-2 mb-3">
<div class="col-md-9">
<input type="text" class="form-control" id="noteComment" placeholder="Investigation note or update…">
</div>
<div class="col-md-3">
<div class="form-check mt-2">
<input type="checkbox" class="form-check-input" id="noteInternal" checked>
<label class="form-check-label" for="noteInternal">Internal only</label>
</div>
</div>
<div class="col-12">
<button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddNote"><i class="ri-chat-3-line"></i> Add Note</button>
</div>
</div>
<h6 class="mb-2">Case timeline</h6>
<div id="timelineBody" class="border rounded p-3 bg-light" style="max-height:220px;overflow-y:auto">
<p class="text-muted mb-0">Loading…</p>
</div>
</div>
</div>
</div>
</div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var CAN_MANAGE = <?php echo $canManage ? 'true' : 'false'; ?>;
    var cache = [];
    var submitModal = null;
    var viewModal = null;
    var manageModal = null;

    var statusMap = {
        Submitted: 'secondary',
        Under_Review: 'info',
        Investigating: 'warning',
        Escalated: 'danger',
        Resolved: 'success',
        Closed: 'dark'
    };
    var priorityMap = { Low: 'secondary', Medium: 'info', High: 'warning', Critical: 'danger' };
    var categoryLabels = {
        Working_Conditions: 'Working Conditions',
        Harassment: 'Harassment',
        Discrimination: 'Discrimination',
        Payroll: 'Payroll',
        Other: 'Other'
    };

    function badge(s, map) {
        return H.badge(String(s).replace(/_/g, ' '), (map && map[s]) || 'secondary');
    }

    function submitterLabel(g) {
        if (g.is_anonymous == 1) return '<span class="badge bg-secondary"><i class="ri-user-unfollow-line"></i> Anonymous</span>';
        if (g.first_name) return H.escapeHtml(g.first_name + ' ' + g.last_name) + (g.employee_code ? '<br><small class="text-muted">' + H.escapeHtml(g.employee_code) + '</small>' : '');
        return '—';
    }

    function buildQuery() {
        var q = [];
        var year = document.getElementById('filterYear').value;
        if (year) q.push('year=' + year);
        ['Status', 'Category', 'Priority'].forEach(function (k) {
            var el = document.getElementById('filter' + k);
            if (el && el.value) q.push(k.toLowerCase() + '=' + encodeURIComponent(el.value));
        });
        var br = document.getElementById('filterBranch');
        if (br && br.value) q.push('branch_id=' + br.value);
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        var mine = document.getElementById('filterMine');
        if (mine && mine.checked) q.push('mine=1');
        return q.length ? '?' + q.join('&') : '';
    }

    function updateStats(s) {
        s = s || {};
        document.getElementById('statTotal').textContent = s.total || 0;
        document.getElementById('statOpen').textContent = s.open_cases || 0;
        document.getElementById('statInvestigating').textContent = s.investigating || 0;
        document.getElementById('statCritical').textContent = s.critical_open || 0;
        document.getElementById('statResolved').textContent = s.resolved_month || 0;
        document.getElementById('statAnonymous').textContent = s.anonymous_count || 0;
    }

    function actions(g) {
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view" data-id="' + g.id + '" title="View"><i class="ri-eye-line"></i></button>';
        if (CAN_MANAGE) {
            html += '<button type="button" class="btn btn-outline-primary btn-manage" data-id="' + g.id + '" title="Manage"><i class="ri-settings-3-line"></i></button>' +
                '<div class="btn-group btn-group-sm">' +
                '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">';
            if (g.status === 'Submitted') html += '<li><a class="dropdown-item grv-status" data-id="' + g.id + '" data-status="Under_Review" href="#">Start review</a></li>';
            if (['Submitted', 'Under_Review'].indexOf(g.status) >= 0) html += '<li><a class="dropdown-item grv-status" data-id="' + g.id + '" data-status="Investigating" href="#">Begin investigation</a></li>';
            if (g.status !== 'Escalated' && g.status !== 'Resolved' && g.status !== 'Closed') {
                html += '<li><a class="dropdown-item grv-status" data-id="' + g.id + '" data-status="Escalated" href="#">Escalate</a></li>';
            }
            if (g.status !== 'Resolved' && g.status !== 'Closed') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success grv-status" data-id="' + g.id + '" data-status="Resolved" href="#">Mark resolved</a></li>';
                html += '<li><a class="dropdown-item grv-status" data-id="' + g.id + '" data-status="Closed" href="#">Close case</a></li>';
            }
            html += '</ul></div>';
        }
        html += '</div>';
        return html;
    }

    function load() {
        var tb = document.querySelector('#grievancesTable tbody');
        tb.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-grievances.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            cache = payload.grievances || [];
            updateStats(payload.stats);
            if (!cache.length) {
                tb.innerHTML = '<tr><td colspan="9" class="text-muted text-center py-4">No grievances found</td></tr>';
                return;
            }
            tb.innerHTML = cache.map(function (g) {
                return '<tr>' +
                    '<td><code>' + H.escapeHtml(g.grievance_no) + '</code></td>' +
                    '<td><strong>' + H.escapeHtml(g.subject) + '</strong>' +
                    (g.action_count > 0 ? '<br><small class="text-muted">' + g.action_count + ' update(s)</small>' : '') + '</td>' +
                    '<td>' + submitterLabel(g) + '</td>' +
                    '<td>' + badge(g.category, null) + '</td>' +
                    '<td>' + badge(g.priority, priorityMap) + '</td>' +
                    '<td>' + badge(g.status, statusMap) + '</td>' +
                    '<td><small>' + H.escapeHtml(g.assigned_name || g.assigned_username || '—') + '</small></td>' +
                    '<td>' + H.formatDate(g.created_at) + '</td>' +
                    '<td>' + actions(g) + '</td></tr>';
            }).join('');
        });
    }

    function renderTimeline(actions) {
        if (!actions || !actions.length) return '<p class="text-muted mb-0">No activity yet</p>';
        return '<ul class="list-unstyled mb-0">' + actions.map(function (a) {
            var internal = a.is_internal == 1 ? ' <span class="badge bg-secondary">Internal</span>' : '';
            return '<li class="mb-2 pb-2 border-bottom">' +
                '<strong>' + H.escapeHtml(String(a.action_type).replace(/_/g, ' ')) + '</strong>' + internal +
                '<br><small class="text-muted">' + (a.created_at || '').substring(0, 16).replace('T', ' ') +
                (a.action_by_name ? ' · ' + H.escapeHtml(a.action_by_name) : '') + '</small>' +
                (a.comment ? '<br>' + H.escapeHtml(a.comment) : '') + '</li>';
        }).join('') + '</ul>';
    }

    function loadTimeline(id) {
        var el = document.getElementById('timelineBody');
        if (!el) return;
        el.innerHTML = '<p class="text-muted mb-0">Loading…</p>';
        H.get('ajax/hr/get-grievance-actions.php?grievance_id=' + id).then(function (res) {
            el.innerHTML = res.success ? renderTimeline(res.data) : '<p class="text-danger">Failed to load timeline</p>';
        });
    }

    function viewCase(id) {
        var g = cache.find(function (x) { return String(x.id) === String(id); });
        if (!g) return;
        document.getElementById('viewBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-4"><strong>Case No</strong><br><code>' + H.escapeHtml(g.grievance_no) + '</code></div>' +
            '<div class="col-md-4"><strong>Status</strong><br>' + badge(g.status, statusMap) + '</div>' +
            '<div class="col-md-4"><strong>Priority</strong><br>' + badge(g.priority, priorityMap) + '</div>' +
            '<div class="col-md-6"><strong>Submitter</strong><br>' + submitterLabel(g) + '</div>' +
            '<div class="col-md-6"><strong>Category</strong><br>' + (categoryLabels[g.category] || g.category) + '</div>' +
            '<div class="col-md-6"><strong>Branch</strong><br>' + H.escapeHtml(g.branch_name || '—') + '</div>' +
            '<div class="col-md-6"><strong>Assigned officer</strong><br>' + H.escapeHtml(g.assigned_name || g.assigned_username || '—') + '</div>' +
            '<div class="col-12"><strong>Subject</strong><br>' + H.escapeHtml(g.subject) + '</div>' +
            '<div class="col-12"><strong>Description</strong><br>' + H.escapeHtml(g.description || '').replace(/\n/g, '<br>') + '</div>' +
            (g.resolution ? '<div class="col-12"><strong>Resolution</strong><br>' + H.escapeHtml(g.resolution).replace(/\n/g, '<br>') + '</div>' : '') +
            '<div class="col-12"><strong>Timeline</strong><div id="viewTimeline" class="mt-2">Loading…</div></div>' +
            '</div>';
        H.get('ajax/hr/get-grievance-actions.php?grievance_id=' + id).then(function (res) {
            document.getElementById('viewTimeline').innerHTML = res.success ? renderTimeline(res.data) : '—';
        });
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
        viewModal.show();
    }

    function openManage(id) {
        var g = cache.find(function (x) { return String(x.id) === String(id); });
        if (!g) return;
        document.getElementById('manageId').value = id;
        document.getElementById('manageModalTitle').innerHTML = '<i class="ri-settings-3-line"></i> ' + H.escapeHtml(g.grievance_no) + ' — ' + H.escapeHtml(g.subject);
        document.getElementById('manageStatus').value = g.status;
        document.getElementById('manageAssign').value = g.assigned_to || '';
        document.getElementById('manageResolution').value = g.resolution || '';
        loadTimeline(id);
        if (!manageModal) manageModal = new bootstrap.Modal(document.getElementById('manageModal'));
        manageModal.show();
    }

    function quickStatus(id, status) {
        H.post('ajax/hr/save-grievance.php', { id: id, status: status }).then(function (r) {
            r.success ? H.success(r.message, load) : H.error(r.message);
        });
    }

    document.getElementById('btnSubmitGrievance').addEventListener('click', function () {
        document.getElementById('submitForm').reset();
        if (!submitModal) submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
        submitModal.show();
    });

    document.getElementById('saveSubmitBtn').addEventListener('click', function () {
        var subject = document.getElementById('subject').value.trim();
        var description = document.getElementById('description').value.trim();
        if (!subject || !description) { H.error('Subject and description are required'); return; }
        H.post('ajax/hr/save-grievance.php', {
            is_anonymous: document.getElementById('anonymous').checked ? 1 : 0,
            category: document.getElementById('category').value,
            priority: document.getElementById('priority').value,
            subject: subject,
            description: description
        }).then(function (r) {
            if (r.success) {
                bootstrap.Modal.getInstance(document.getElementById('submitModal')).hide();
                H.success(r.message, load);
            } else H.error(r.message);
        });
    });

    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('filterYear').addEventListener('change', load);
    var filterMine = document.getElementById('filterMine');
    if (filterMine) filterMine.addEventListener('change', load);

    if (CAN_MANAGE) {
        document.getElementById('btnSaveManage').addEventListener('click', function () {
            var id = document.getElementById('manageId').value;
            H.post('ajax/hr/save-grievance.php', {
                id: id,
                status: document.getElementById('manageStatus').value,
                assigned_to: document.getElementById('manageAssign').value,
                resolution: document.getElementById('manageResolution').value
            }).then(function (r) {
                if (r.success) {
                    H.success(r.message, function () { loadTimeline(id); load(); });
                } else H.error(r.message);
            });
        });
        document.getElementById('btnAddNote').addEventListener('click', function () {
            var id = document.getElementById('manageId').value;
            var comment = document.getElementById('noteComment').value.trim();
            if (!comment) { H.error('Enter a note'); return; }
            H.post('ajax/hr/save-grievance.php', {
                id: id,
                action: 'add_note',
                comment: comment,
                is_internal: document.getElementById('noteInternal').checked ? 1 : 0
            }).then(function (r) {
                if (r.success) {
                    document.getElementById('noteComment').value = '';
                    H.success(r.message, function () { loadTimeline(id); });
                } else H.error(r.message);
            });
        });
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id]');
        if (!t) return;
        var id = t.dataset.id;
        if (t.classList.contains('btn-view')) { viewCase(id); return; }
        if (t.classList.contains('btn-manage')) { openManage(id); return; }
        if (t.classList.contains('grv-status')) {
            e.preventDefault();
            quickStatus(id, t.dataset.status);
        }
    });

    load();
})();
</script>
