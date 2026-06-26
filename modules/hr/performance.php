<?php
/**
 * Employee Performance Reviews
 */
require_once '../../config/config.php';
hrRequirePage('hr_reports', 'view', ['Accountant']);

$pageTitle = 'Performance Reviews';
$currentUser = getCurrentUser();
$canManage = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_reports', 'create'));

$staff = fetchAll(executeQuery(
    "SELECT id, staff_id, first_name, last_name, designation, department
     FROM staff WHERE status = 'Active' ORDER BY first_name, last_name"
));
$departments = fetchAll(executeQuery(
    "SELECT DISTINCT department FROM staff
     WHERE department IS NOT NULL AND department != '' ORDER BY department"
));
$reviewers = fetchAll(executeQuery(
    "SELECT u.id, u.username, TRIM(CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,''))) AS full_name
     FROM users u
     INNER JOIN roles r ON u.role_id = r.id
     LEFT JOIN staff s ON s.user_id = u.id
     WHERE u.is_active = 1 AND r.role_name IN ('Super Admin', 'Admin', 'Accountant')
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
<?php if ($canManage): ?>
<button type="button" class="btn btn-primary" id="btnNewReview">
<i class="ri-add-line"></i> New Review
</button>
<?php endif; ?>
</div>
<h4 class="page-title">Employee Performance Reviews</h4>
<p class="text-muted mb-0 small">Structured appraisals with KPI scoring, goals, and acknowledgement workflow</p>
</div>
</div>
</div>

<!-- KPI -->
<div class="row" id="perfStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-file-chart-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Total Reviews</p><h4 class="mb-0" id="statTotal">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-secondary-lighten text-secondary rounded p-2 me-2"><i class="ri-draft-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Draft</p><h4 class="mb-0" id="statDraft">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-send-plane-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Pending Ack.</p><h4 class="mb-0" id="statSubmitted">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-star-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Avg Rating (YTD)</p><h4 class="mb-0" id="statAvgRating">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-trophy-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Top Performers</p><h4 class="mb-0" id="statTop">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-purple-lighten text-purple rounded p-2 me-2"><i class="ri-calendar-check-line font-22"></i></div>
<div><p class="text-muted mb-1 small">This Quarter</p><h4 class="mb-0" id="statQuarter">—</h4></div>
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
<?php for ($y = $year - 2; $y <= $year + 1; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select">
<option value="">All</option>
<option value="Draft">Draft</option>
<option value="Submitted">Submitted</option>
<option value="Acknowledged">Acknowledged</option>
<option value="Archived">Archived</option>
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
<div class="col-md-2">
<label class="form-label small">Employee</label>
<select id="filterStaff" class="form-select">
<option value="">All</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Period, name, code…">
</div>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>

<!-- Table -->
<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="reviewsTable">
<thead class="table-light">
<tr>
<th>Employee</th>
<th>Period</th>
<th>Rating</th>
<th>Reviewer</th>
<th>Review Date</th>
<th>Status</th>
<th style="min-width:140px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="7" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>

</div>
</div>
</div>

<!-- View -->
<div class="modal fade" id="viewModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title"><i class="ri-eye-line"></i> Review Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<?php if ($canManage): ?>
<!-- Create / Edit -->
<div class="modal fade" id="reviewModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="reviewModalTitle"><i class="ri-file-chart-line"></i> Performance Review</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" id="reviewId" value="">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Employee <span class="text-danger">*</span></label>
<select class="form-select" id="formStaff">
<option value="">— Select —</option>
<?php foreach ($staff as $s): ?>
<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Reviewer</label>
<select class="form-select" id="formReviewer">
<?php foreach ($reviewers as $rv): ?>
<option value="<?php echo (int)$rv['id']; ?>" <?php echo (int)$rv['id'] === (int)$currentUser['id'] ? 'selected' : ''; ?>>
<?php echo htmlspecialchars(trim($rv['full_name']) ?: $rv['username']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Review Period <span class="text-danger">*</span></label>
<input type="text" class="form-control" id="formPeriod" list="periodPresets" placeholder="e.g. Q1 2026, Annual 2025">
<datalist id="periodPresets">
<option value="Q1 <?php echo $year; ?>">
<option value="Q2 <?php echo $year; ?>">
<option value="Q3 <?php echo $year; ?>">
<option value="Q4 <?php echo $year; ?>">
<option value="Annual <?php echo $year; ?>">
<option value="Probation <?php echo $year; ?>">
</datalist>
</div>
<div class="col-md-3">
<label class="form-label">Review Date</label>
<input type="date" class="form-control" id="formDate" value="<?php echo date('Y-m-d'); ?>">
</div>
<div class="col-md-3">
<label class="form-label">Overall Rating (1–5)</label>
<input type="number" step="0.1" min="1" max="5" class="form-control" id="formRating" placeholder="Auto from KPIs">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select class="form-select" id="formStatus">
<option value="Draft">Draft</option>
<option value="Submitted">Submitted</option>
<option value="Acknowledged">Acknowledged</option>
<option value="Archived">Archived</option>
</select>
</div>
</div>

<hr class="my-3">
<h6 class="mb-2"><i class="ri-bar-chart-box-line"></i> KPI Scores</h6>
<p class="text-muted small">Add measurable criteria (1–5). Overall rating auto-calculates from KPI average when left blank.</p>
<div class="table-responsive mb-2">
<table class="table table-sm table-bordered" id="kpiTable">
<thead class="table-light"><tr><th>KPI / Criterion</th><th style="width:100px">Score</th><th style="width:50px"></th></tr></thead>
<tbody id="kpiBody"></tbody>
</table>
</div>
<button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btnAddKpi"><i class="ri-add-line"></i> Add KPI</button>

<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Strengths</label>
<textarea class="form-control" id="formStrengths" rows="3" placeholder="Key strengths observed…"></textarea>
</div>
<div class="col-md-6">
<label class="form-label">Areas for Improvement</label>
<textarea class="form-control" id="formImprovements" rows="3" placeholder="Development areas…"></textarea>
</div>
<div class="col-12">
<label class="form-label">Goals for Next Period</label>
<textarea class="form-control" id="formGoals" rows="2" placeholder="SMART goals for the upcoming review cycle…"></textarea>
</div>
<div class="col-12">
<label class="form-label">Reviewer Comments</label>
<textarea class="form-control" id="formComments" rows="3" placeholder="Summary narrative…"></textarea>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="btnSaveReview"><i class="ri-save-line"></i> Save Review</button>
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
    var viewModal = null;
    var reviewModal = null;

    var statusMap = { Draft: 'secondary', Submitted: 'warning', Acknowledged: 'success', Archived: 'dark' };

    function ratingBadge(r) {
        if (r === null || r === '' || r === undefined) return '<span class="text-muted">—</span>';
        var n = parseFloat(r);
        var cls = n >= 4.5 ? 'success' : (n >= 3.5 ? 'info' : (n >= 2.5 ? 'warning' : 'danger'));
        var stars = '';
        for (var i = 1; i <= 5; i++) {
            stars += '<i class="ri-star' + (i <= Math.round(n) ? '-fill text-warning' : '-line text-muted') + '"></i>';
        }
        return '<span class="badge bg-' + cls + '-lighten text-' + cls + ' me-1">' + n.toFixed(1) + '</span>' + stars;
    }

    function badge(s) {
        return H.badge(s, statusMap[s] || 'secondary');
    }

    function buildQuery() {
        var q = [];
        var year = document.getElementById('filterYear').value;
        if (year) q.push('year=' + year);
        var st = document.getElementById('filterStatus').value;
        if (st) q.push('status=' + encodeURIComponent(st));
        var dept = document.getElementById('filterDepartment').value;
        if (dept) q.push('department=' + encodeURIComponent(dept));
        var staff = document.getElementById('filterStaff').value;
        if (staff) q.push('staff_id=' + staff);
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        return q.length ? '?' + q.join('&') : '';
    }

    function updateStats(s) {
        s = s || {};
        document.getElementById('statTotal').textContent = s.total || 0;
        document.getElementById('statDraft').textContent = s.draft || 0;
        document.getElementById('statSubmitted').textContent = s.submitted || 0;
        document.getElementById('statAvgRating').textContent = s.avg_rating_year != null ? s.avg_rating_year : '—';
        document.getElementById('statTop').textContent = s.top_performers || 0;
        document.getElementById('statQuarter').textContent = s.this_quarter || 0;
    }

    function kpiRow(name, score) {
        return '<tr class="kpi-row">' +
            '<td><input type="text" class="form-control form-control-sm kpi-name" value="' + H.escapeHtml(name || '') + '" placeholder="e.g. Punctuality"></td>' +
            '<td><input type="number" class="form-control form-control-sm kpi-score" min="0" max="5" step="0.5" value="' + (score != null ? score : '') + '"></td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-kpi"><i class="ri-delete-bin-line"></i></button></td></tr>';
    }

    function resetKpis(rows) {
        var tb = document.getElementById('kpiBody');
        if (!tb) return;
        rows = rows && rows.length ? rows : [{ name: 'Job Knowledge', score: '' }, { name: 'Quality of Work', score: '' }, { name: 'Teamwork', score: '' }];
        tb.innerHTML = rows.map(function (k) { return kpiRow(k.name, k.score); }).join('');
    }

    function collectKpis() {
        var rows = [];
        document.querySelectorAll('#kpiBody .kpi-row').forEach(function (tr) {
            var name = tr.querySelector('.kpi-name').value.trim();
            var score = tr.querySelector('.kpi-score').value;
            if (name) rows.push({ name: name, score: score });
        });
        return rows;
    }

    function actions(r) {
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view" data-id="' + r.id + '" title="View"><i class="ri-eye-line"></i></button>';
        if (CAN_MANAGE) {
            html += '<button type="button" class="btn btn-outline-primary btn-edit" data-id="' + r.id + '" title="Edit"><i class="ri-pencil-line"></i></button>' +
                '<div class="btn-group btn-group-sm">' +
                '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">';
            if (r.status === 'Draft') html += '<li><a class="dropdown-item perf-status" data-id="' + r.id + '" data-status="Submitted" href="#">Submit to employee</a></li>';
            if (r.status === 'Submitted') html += '<li><a class="dropdown-item perf-status" data-id="' + r.id + '" data-status="Acknowledged" href="#">Mark acknowledged</a></li>';
            if (r.status !== 'Archived') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item perf-status" data-id="' + r.id + '" data-status="Archived" href="#">Archive</a></li>';
            }
            html += '</ul></div>';
        }
        html += '</div>';
        return html;
    }

    function load() {
        var tb = document.querySelector('#reviewsTable tbody');
        tb.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-performance-reviews.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            cache = payload.reviews || [];
            updateStats(payload.stats);
            if (!cache.length) {
                tb.innerHTML = '<tr><td colspan="7" class="text-muted text-center py-4">No performance reviews found</td></tr>';
                return;
            }
            tb.innerHTML = cache.map(function (r) {
                return '<tr>' +
                    '<td><strong>' + H.escapeHtml(r.first_name + ' ' + r.last_name) + '</strong>' +
                    '<br><small class="text-muted">' + H.escapeHtml(r.staff_code) +
                    (r.department ? ' · ' + H.escapeHtml(r.department) : '') + '</small></td>' +
                    '<td>' + H.escapeHtml(r.review_period) + '</td>' +
                    '<td>' + ratingBadge(r.rating) + '</td>' +
                    '<td><small>' + H.escapeHtml(r.reviewer_name || r.reviewer_username || '—') + '</small></td>' +
                    '<td>' + H.formatDate(r.review_date) + '</td>' +
                    '<td>' + badge(r.status) + '</td>' +
                    '<td>' + actions(r) + '</td></tr>';
            }).join('');
        });
    }

    function renderKpiTable(kpis) {
        if (!kpis || !kpis.length) return '<p class="text-muted mb-0">No KPIs recorded</p>';
        return '<table class="table table-sm table-bordered mb-0"><thead><tr><th>KPI</th><th>Score</th></tr></thead><tbody>' +
            kpis.map(function (k) {
                return '<tr><td>' + H.escapeHtml(k.name) + '</td><td>' + (k.score != null ? k.score + '/5' : '—') + '</td></tr>';
            }).join('') + '</tbody></table>';
    }

    function viewReview(id) {
        var r = cache.find(function (x) { return String(x.id) === String(id); });
        if (!r) return;
        var kpis = Array.isArray(r.kpis) ? r.kpis : [];
        document.getElementById('viewBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Employee</strong><br>' + H.escapeHtml(r.first_name + ' ' + r.last_name) +
            '<br><small class="text-muted">' + H.escapeHtml(r.staff_code) + (r.designation ? ' · ' + H.escapeHtml(r.designation) : '') + '</small></div>' +
            '<div class="col-md-3"><strong>Period</strong><br>' + H.escapeHtml(r.review_period) + '</div>' +
            '<div class="col-md-3"><strong>Status</strong><br>' + badge(r.status) + '</div>' +
            '<div class="col-md-4"><strong>Overall Rating</strong><br>' + ratingBadge(r.rating) + '</div>' +
            '<div class="col-md-4"><strong>Reviewer</strong><br>' + H.escapeHtml(r.reviewer_name || r.reviewer_username || '—') + '</div>' +
            '<div class="col-md-4"><strong>Review Date</strong><br>' + H.formatDate(r.review_date) + '</div>' +
            '<div class="col-12"><strong>KPI Scores</strong><div class="mt-1">' + renderKpiTable(kpis) + '</div></div>' +
            (r.strengths ? '<div class="col-md-6"><strong>Strengths</strong><br>' + H.escapeHtml(r.strengths).replace(/\n/g, '<br>') + '</div>' : '') +
            (r.improvements ? '<div class="col-md-6"><strong>Areas for Improvement</strong><br>' + H.escapeHtml(r.improvements).replace(/\n/g, '<br>') + '</div>' : '') +
            (r.goals ? '<div class="col-12"><strong>Goals</strong><br>' + H.escapeHtml(r.goals).replace(/\n/g, '<br>') + '</div>' : '') +
            (r.comments ? '<div class="col-12"><strong>Comments</strong><br>' + H.escapeHtml(r.comments).replace(/\n/g, '<br>') + '</div>' : '') +
            '</div>';
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
        viewModal.show();
    }

    function openForm(id) {
        var r = id ? cache.find(function (x) { return String(x.id) === String(id); }) : null;
        document.getElementById('reviewId').value = r ? r.id : '';
        document.getElementById('reviewModalTitle').innerHTML = r
            ? '<i class="ri-pencil-line"></i> Edit Review — ' + H.escapeHtml(r.first_name + ' ' + r.last_name)
            : '<i class="ri-add-line"></i> New Performance Review';
        document.getElementById('formStaff').value = r ? r.staff_id : '';
        document.getElementById('formReviewer').value = r ? r.reviewer_id : '<?php echo (int)$currentUser['id']; ?>';
        document.getElementById('formPeriod').value = r ? r.review_period : '';
        document.getElementById('formDate').value = r ? (r.review_date || '').substring(0, 10) : '<?php echo date('Y-m-d'); ?>';
        document.getElementById('formRating').value = r && r.rating != null ? r.rating : '';
        document.getElementById('formStatus').value = r ? r.status : 'Draft';
        document.getElementById('formStrengths').value = r ? (r.strengths || '') : '';
        document.getElementById('formImprovements').value = r ? (r.improvements || '') : '';
        document.getElementById('formGoals').value = r ? (r.goals || '') : '';
        document.getElementById('formComments').value = r ? (r.comments || '') : '';
        resetKpis(r && Array.isArray(r.kpis) ? r.kpis : null);
        if (!reviewModal) reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
        reviewModal.show();
    }

    function quickStatus(id, status) {
        H.post('ajax/hr/save-performance-review.php', { id: id, status: status }).then(function (r) {
            r.success ? H.success(r.message, load) : H.error(r.message);
        });
    }

    function saveReview() {
        var staffId = document.getElementById('formStaff').value;
        var period = document.getElementById('formPeriod').value.trim();
        if (!staffId || !period) { H.error('Employee and review period are required'); return; }
        var payload = {
            id: document.getElementById('reviewId').value || undefined,
            staff_id: staffId,
            reviewer_id: document.getElementById('formReviewer').value,
            review_period: period,
            review_date: document.getElementById('formDate').value,
            rating: document.getElementById('formRating').value,
            status: document.getElementById('formStatus').value,
            strengths: document.getElementById('formStrengths').value,
            improvements: document.getElementById('formImprovements').value,
            goals: document.getElementById('formGoals').value,
            comments: document.getElementById('formComments').value,
            kpis: collectKpis()
        };
        H.post('ajax/hr/save-performance-review.php', payload).then(function (r) {
            if (r.success) {
                bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                H.success(r.message, load);
            } else H.error(r.message);
        });
    }

    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('filterYear').addEventListener('change', load);

    if (CAN_MANAGE) {
        document.getElementById('btnNewReview').addEventListener('click', function () { openForm(null); });
        document.getElementById('btnSaveReview').addEventListener('click', saveReview);
        document.getElementById('btnAddKpi').addEventListener('click', function () {
            document.getElementById('kpiBody').insertAdjacentHTML('beforeend', kpiRow('', ''));
        });
        document.getElementById('kpiBody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-kpi')) {
                var rows = document.querySelectorAll('#kpiBody .kpi-row');
                if (rows.length > 1) e.target.closest('tr').remove();
            }
        });
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id]');
        if (!t) return;
        var id = t.dataset.id;
        if (t.classList.contains('btn-view')) { viewReview(id); return; }
        if (t.classList.contains('btn-edit')) { openForm(id); return; }
        if (t.classList.contains('perf-status')) {
            e.preventDefault();
            quickStatus(id, t.dataset.status);
        }
    });

    load();
})();
</script>
