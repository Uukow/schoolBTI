<?php
/**
 * PPDP — Professional & Pedagogical Development Programs
 */
require_once '../../config/config.php';
hrRequireAccess('hr_ppdp', 'view');

$pageTitle = 'PPDP Programs';
$currentUser = getCurrentUser();
$canManage = hasRole(['Super Admin', 'Admin'])
    || (function_exists('canPerform') && canPerform('hr_ppdp', 'manage'));

$branches = fetchAll(executeQuery("SELECT id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name"));
$facilitators = fetchAll(executeQuery(
    "SELECT id, staff_id, first_name, last_name, designation
     FROM staff WHERE status = 'Active' ORDER BY first_name, last_name"
));
$staffForEnroll = $facilitators;

$currentStaffId = 0;
$currentStaffName = '';
if (!$canManage) {
    $cs = fetchOne(executeQuery(
        "SELECT id, first_name, last_name FROM staff WHERE user_id = ?",
        'i', [$currentUser['id']]
    ));
    $currentStaffId = (int)($cs['id'] ?? 0);
    $currentStaffName = trim(($cs['first_name'] ?? '') . ' ' . ($cs['last_name'] ?? ''));
}

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
<button type="button" class="btn btn-primary" id="btnNewProgram">
<i class="ri-graduation-cap-line"></i> New Program
</button>
<?php endif; ?>
</div>
<h4 class="page-title">PPDP Program Management</h4>
<p class="text-muted mb-0 small">Professional &amp; Pedagogical Development Programs for staff growth and certification</p>
</div>
</div>
</div>

<!-- KPI Cards -->
<div class="row" id="ppdpStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-book-open-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Total Programs</p><h4 class="mb-0" id="statTotal">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-door-open-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Open</p><h4 class="mb-0" id="statOpen">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-play-circle-line font-22"></i></div>
<div><p class="text-muted mb-1 small">In Progress</p><h4 class="mb-0" id="statProgress">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-calendar-check-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Active Now</p><h4 class="mb-0" id="statActive">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-secondary-lighten text-secondary rounded p-2 me-2"><i class="ri-group-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Enrollments</p><h4 class="mb-0" id="statEnroll">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-award-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Completions</p><h4 class="mb-0" id="statComplete">—</h4></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-2">
<label class="form-label small">Year</label>
<select id="filterYear" class="form-select">
<option value="">All years</option>
<?php for ($y = $year - 1; $y <= $year + 2; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select">
<option value="">All statuses</option>
<option value="Planned">Planned</option>
<option value="Open">Open</option>
<option value="In_Progress">In Progress</option>
<option value="Completed">Completed</option>
<option value="Cancelled">Cancelled</option>
</select>
</div>
<?php if (hasRole(['Super Admin'])): ?>
<div class="col-md-3">
<label class="form-label small">Branch</label>
<select id="filterBranch" class="form-select">
<option value="">All branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-3">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Program name, code…">
</div>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>

<!-- Programs table -->
<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="programsTable">
<thead class="table-light">
<tr>
<th>Code</th>
<th>Program</th>
<th>Schedule</th>
<th>Facilitator</th>
<th>Branch</th>
<th>Enrollment</th>
<th>Completion</th>
<th>Status</th>
<th style="min-width:160px">Actions</th>
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

<!-- Create / Edit Program -->
<div class="modal fade" id="programModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="programModalTitle"><i class="ri-graduation-cap-line"></i> New PPDP Program</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="programForm">
<input type="hidden" id="programId" value="">
<div class="row g-3">
<div class="col-md-8">
<label class="form-label">Program Name <span class="text-danger">*</span></label>
<input type="text" class="form-control" id="programName" required placeholder="e.g. Classroom Management Excellence">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select class="form-select" id="programStatus">
<option value="Planned">Planned</option>
<option value="Open">Open (registration)</option>
<option value="In_Progress">In Progress</option>
<option value="Completed">Completed</option>
<option value="Cancelled">Cancelled</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Start Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="startDate" required>
</div>
<div class="col-md-4">
<label class="form-label">End Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="endDate" required>
</div>
<div class="col-md-4">
<label class="form-label">Duration</label>
<input type="text" class="form-control bg-light" id="programDuration" readonly value="—">
</div>
<div class="col-md-4">
<label class="form-label">Capacity</label>
<input type="number" class="form-control" id="capacity" min="1" value="30">
</div>
<div class="col-md-4">
<label class="form-label">Branch</label>
<select class="form-select" id="programBranch">
<option value="">All branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Facilitator</label>
<select class="form-select" id="facilitatorId">
<option value="">— Not assigned —</option>
<?php foreach ($facilitators as $f): ?>
<option value="<?php echo (int)$f['id']; ?>"><?php echo htmlspecialchars($f['staff_id'] . ' — ' . $f['first_name'] . ' ' . $f['last_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-12">
<label class="form-label">Description &amp; objectives</label>
<textarea class="form-control" id="description" rows="4" placeholder="Learning outcomes, target audience, delivery mode…"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveProgramBtn"><i class="ri-save-line"></i> Save Program</button>
</div>
</div>
</div>
</div>

<!-- View Program -->
<div class="modal fade" id="viewProgramModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Program Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewProgramBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<!-- Participants -->
<div class="modal fade" id="participantsModal" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="participantsModalTitle"><i class="ri-group-line"></i> Participants</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<?php if ($canManage): ?>
<div class="row g-2 mb-3 align-items-end">
<div class="col-md-8">
<label class="form-label small">Add participant</label>
<select id="addStaffId" class="form-select">
<option value="">Select staff member…</option>
<?php foreach ($staffForEnroll as $s): ?>
<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name'] . ' ' . $s['last_name'] . ($s['designation'] ? ' (' . $s['designation'] . ')' : '')); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4">
<button type="button" class="btn btn-primary w-100" id="btnAddParticipant"><i class="ri-user-add-line"></i> Enroll Staff</button>
</div>
</div>
<?php endif; ?>
<input type="hidden" id="participantsProgramId" value="">
<div class="table-responsive">
<table class="table table-sm table-hover align-middle" id="participantsTable">
<thead class="table-light">
<tr>
<th>Staff</th>
<th>Department</th>
<th>Registered</th>
<th>Progress</th>
<th>Score</th>
<th>Status</th>
<th>Certificate</th>
<?php if ($canManage): ?><th>Actions</th><?php endif; ?>
</tr>
</thead>
<tbody><tr><td colspan="<?php echo $canManage ? 8 : 7; ?>" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var CAN_MANAGE = <?php echo $canManage ? 'true' : 'false'; ?>;
    var STAFF_ID = <?php echo (int)$currentStaffId; ?>;
    var STAFF_NAME = <?php echo json_encode($currentStaffName); ?>;

    var programsCache = [];
    var participantsCache = [];
    var programModal = null;
    var viewModal = null;
    var participantsModal = null;
    var activeProgramId = 0;

    var statusMap = {
        Planned: 'secondary',
        Open: 'success',
        In_Progress: 'warning',
        Completed: 'info',
        Cancelled: 'danger'
    };
    var partStatusMap = {
        Registered: 'secondary',
        Attending: 'primary',
        Completed: 'success',
        Dropped: 'warning',
        Failed: 'danger'
    };

    function statusBadge(s, map) {
        return H.badge(String(s).replace(/_/g, ' '), (map && map[s]) || 'secondary');
    }

    function formatPeriod(p) {
        var a = H.formatDate(p.start_date);
        var b = H.formatDate(p.end_date);
        var days = p.duration_days || 1;
        return a + ' → ' + b + ' <span class="badge bg-light text-dark">' + days + 'd</span>';
    }

    function calcDuration() {
        var start = document.getElementById('startDate').value;
        var end = document.getElementById('endDate').value;
        var el = document.getElementById('programDuration');
        if (!start) { el.value = '—'; return; }
        if (!end || end < start) end = start;
        var days = Math.round((new Date(end) - new Date(start)) / 86400000) + 1;
        el.value = days + (days === 1 ? ' day' : ' days');
    }

    function buildQuery() {
        var q = [];
        var year = document.getElementById('filterYear').value;
        if (year) q.push('year=' + year);
        var st = document.getElementById('filterStatus').value;
        if (st) q.push('status=' + encodeURIComponent(st));
        var br = document.getElementById('filterBranch');
        if (br && br.value) q.push('branch_id=' + br.value);
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        return q.length ? '?' + q.join('&') : '';
    }

    function updateStats(stats) {
        stats = stats || {};
        document.getElementById('statTotal').textContent = stats.total_programs || 0;
        document.getElementById('statOpen').textContent = stats.open_programs || 0;
        document.getElementById('statProgress').textContent = stats.in_progress || 0;
        document.getElementById('statActive').textContent = stats.active_now || 0;
        document.getElementById('statEnroll').textContent = stats.total_enrollments || 0;
        document.getElementById('statComplete').textContent = stats.completions || 0;
    }

    function enrollmentBar(p) {
        var cap = parseInt(p.capacity, 10) || 1;
        var cnt = parseInt(p.participant_count, 10) || 0;
        var pct = Math.min(100, Math.round((cnt / cap) * 100));
        var cls = pct >= 100 ? 'bg-danger' : (pct >= 80 ? 'bg-warning' : 'bg-success');
        return '<div class="d-flex align-items-center gap-2">' +
            '<div class="progress flex-grow-1" style="height:6px;min-width:60px"><div class="progress-bar ' + cls + '" style="width:' + pct + '%"></div></div>' +
            '<small>' + cnt + '/' + cap + '</small></div>';
    }

    function completionRate(p) {
        var cnt = parseInt(p.participant_count, 10) || 0;
        var done = parseInt(p.completed_count, 10) || 0;
        if (!cnt) return '<span class="text-muted">—</span>';
        return '<span class="badge bg-light text-dark">' + Math.round((done / cnt) * 100) + '%</span> <small class="text-muted">(' + done + ')</small>';
    }

    function programActions(p) {
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view" data-id="' + p.id + '" title="View"><i class="ri-eye-line"></i></button>' +
            '<button type="button" class="btn btn-outline-secondary btn-participants" data-id="' + p.id + '" data-name="' + H.escapeHtml(p.program_name) + '" title="Participants"><i class="ri-group-line"></i></button>';

        if (CAN_MANAGE) {
            html += '<button type="button" class="btn btn-outline-primary btn-edit" data-id="' + p.id + '" title="Edit"><i class="ri-edit-line"></i></button>' +
                '<div class="btn-group btn-group-sm">' +
                '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="More"></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">';
            if (p.status === 'Planned') html += '<li><a class="dropdown-item prog-status" data-id="' + p.id + '" data-status="Open" href="#">Open registration</a></li>';
            if (p.status === 'Open') html += '<li><a class="dropdown-item prog-status" data-id="' + p.id + '" data-status="In_Progress" href="#">Start program</a></li>';
            if (p.status === 'In_Progress') html += '<li><a class="dropdown-item prog-status" data-id="' + p.id + '" data-status="Completed" href="#">Mark completed</a></li>';
            if (p.status !== 'Cancelled' && p.status !== 'Completed') {
                html += '<li><a class="dropdown-item text-danger prog-status" data-id="' + p.id + '" data-status="Cancelled" href="#">Cancel program</a></li>';
            }
            html += '</ul></div>';
        } else if (p.status === 'Open' && STAFF_ID) {
            var full = parseInt(p.participant_count, 10) >= parseInt(p.capacity, 10);
            html += '<button type="button" class="btn btn-outline-success btn-register" data-id="' + p.id + '"' +
                (full ? ' disabled title="Full"' : '') + '><i class="ri-user-add-line"></i></button>';
        }

        html += '</div>';
        return html;
    }

    function loadPrograms() {
        var tb = document.querySelector('#programsTable tbody');
        tb.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-ppdp-programs.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            programsCache = payload.programs || [];
            updateStats(payload.stats);
            if (!programsCache.length) {
                tb.innerHTML = '<tr><td colspan="9" class="text-muted text-center py-4">No programs found</td></tr>';
                return;
            }
            tb.innerHTML = programsCache.map(function (p) {
                return '<tr>' +
                    '<td><code>' + H.escapeHtml(p.program_code) + '</code></td>' +
                    '<td><strong>' + H.escapeHtml(p.program_name) + '</strong>' +
                    (p.description ? '<br><small class="text-muted">' + H.escapeHtml(p.description).substring(0, 70) + (p.description.length > 70 ? '…' : '') + '</small>' : '') + '</td>' +
                    '<td>' + formatPeriod(p) + '</td>' +
                    '<td>' + H.escapeHtml(p.facilitator_name || '—') + '</td>' +
                    '<td>' + H.escapeHtml(p.branch_name || 'All') + '</td>' +
                    '<td>' + enrollmentBar(p) + '</td>' +
                    '<td>' + completionRate(p) + '</td>' +
                    '<td>' + statusBadge(p.status, statusMap) + '</td>' +
                    '<td>' + programActions(p) + '</td></tr>';
            }).join('');
        }).catch(function (e) { H.error(e.message || 'Failed to load programs'); });
    }

    function openProgramForm(data) {
        document.getElementById('programForm').reset();
        document.getElementById('programId').value = data ? data.id : '';
        document.getElementById('programModalTitle').innerHTML = data
            ? '<i class="ri-edit-line"></i> Edit PPDP Program'
            : '<i class="ri-graduation-cap-line"></i> New PPDP Program';
        if (data) {
            document.getElementById('programName').value = data.program_name || '';
            document.getElementById('programStatus').value = data.status || 'Planned';
            document.getElementById('startDate').value = (data.start_date || '').substring(0, 10);
            document.getElementById('endDate').value = (data.end_date || '').substring(0, 10);
            document.getElementById('capacity').value = data.capacity || 30;
            document.getElementById('programBranch').value = data.branch_id || '';
            document.getElementById('facilitatorId').value = data.facilitator_id || '';
            document.getElementById('description').value = data.description || '';
        } else {
            var today = new Date().toISOString().slice(0, 10);
            document.getElementById('startDate').value = today;
            document.getElementById('endDate').value = today;
            document.getElementById('programStatus').value = 'Planned';
        }
        calcDuration();
        if (!programModal) programModal = new bootstrap.Modal(document.getElementById('programModal'));
        programModal.show();
    }

    function viewProgram(id) {
        var p = programsCache.find(function (x) { return String(x.id) === String(id); });
        if (!p) return;
        document.getElementById('viewProgramBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-4"><strong>Code</strong><br><code>' + H.escapeHtml(p.program_code) + '</code></div>' +
            '<div class="col-md-4"><strong>Status</strong><br>' + statusBadge(p.status, statusMap) + '</div>' +
            '<div class="col-md-4"><strong>Capacity</strong><br>' + (p.participant_count || 0) + ' / ' + (p.capacity || 0) + ' enrolled</div>' +
            '<div class="col-12"><strong>Program</strong><br>' + H.escapeHtml(p.program_name) + '</div>' +
            '<div class="col-md-6"><strong>Schedule</strong><br>' + H.formatDate(p.start_date) + ' → ' + H.formatDate(p.end_date) + ' (' + (p.duration_days || 1) + ' days)</div>' +
            '<div class="col-md-6"><strong>Completion rate</strong><br>' + completionRate(p) + '</div>' +
            '<div class="col-md-6"><strong>Facilitator</strong><br>' + H.escapeHtml(p.facilitator_name || 'Not assigned') + '</div>' +
            '<div class="col-md-6"><strong>Branch</strong><br>' + H.escapeHtml(p.branch_name || 'All branches') + '</div>' +
            '<div class="col-12"><strong>Description</strong><br>' + H.escapeHtml(p.description || '—').replace(/\n/g, '<br>') + '</div>' +
            '</div>';
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewProgramModal'));
        viewModal.show();
    }

    function loadParticipants(programId) {
        activeProgramId = programId;
        document.getElementById('participantsProgramId').value = programId;
        var tb = document.querySelector('#participantsTable tbody');
        var cols = CAN_MANAGE ? 8 : 7;
        tb.innerHTML = '<tr><td colspan="' + cols + '" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-ppdp-participants.php?program_id=' + programId).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            participantsCache = res.data || [];
            if (!participantsCache.length) {
                tb.innerHTML = '<tr><td colspan="' + cols + '" class="text-muted text-center py-3">No participants yet</td></tr>';
                return;
            }
            tb.innerHTML = participantsCache.map(function (pt) {
                var progress = '<div class="progress" style="height:6px;width:80px"><div class="progress-bar" style="width:' + (pt.progress_percent || 0) + '%"></div></div> ' +
                    '<small>' + (pt.progress_percent || 0) + '%</small>';
                var acts = '';
                if (CAN_MANAGE) {
                    acts = '<div class="btn-group btn-group-sm">' +
                        '<button type="button" class="btn btn-outline-success pt-complete" data-id="' + pt.id + '" title="Mark completed"><i class="ri-check-line"></i></button>' +
                        '<button type="button" class="btn btn-outline-primary pt-attend" data-id="' + pt.id + '" title="Mark attending"><i class="ri-play-line"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger pt-remove" data-id="' + pt.id + '" title="Remove"><i class="ri-delete-bin-line"></i></button>' +
                        '</div>';
                }
                return '<tr>' +
                    '<td><strong>' + H.escapeHtml(pt.first_name + ' ' + pt.last_name) + '</strong><br><small class="text-muted">' + H.escapeHtml(pt.staff_id) + '</small></td>' +
                    '<td>' + H.escapeHtml(pt.department || pt.designation || '—') + '</td>' +
                    '<td>' + H.formatDate(pt.registration_date) + '</td>' +
                    '<td>' + progress + '</td>' +
                    '<td>' + (pt.assessment_score != null ? pt.assessment_score : '—') + '</td>' +
                    '<td>' + statusBadge(pt.status, partStatusMap) + '</td>' +
                    '<td>' + (pt.certificate_no ? '<code>' + H.escapeHtml(pt.certificate_no) + '</code>' : '—') + '</td>' +
                    (CAN_MANAGE ? '<td>' + acts + '</td>' : '') + '</tr>';
            }).join('');
        });
    }

    function openParticipants(id, name) {
        document.getElementById('participantsModalTitle').innerHTML =
            '<i class="ri-group-line"></i> Participants — ' + H.escapeHtml(name || '');
        loadParticipants(id);
        if (!participantsModal) participantsModal = new bootstrap.Modal(document.getElementById('participantsModal'));
        participantsModal.show();
    }

    function setProgramStatus(id, status) {
        H.post('ajax/hr/save-ppdp-program.php', { id: id, status: status }).then(function (r) {
            r.success ? H.success(r.message, loadPrograms) : H.error(r.message);
        });
    }

    function registerSelf(programId) {
        if (!STAFF_ID) {
            H.error('Your user account is not linked to a staff record. Contact HR.');
            return;
        }
        H.post('ajax/hr/save-ppdp-program.php', { id: programId, register_staff_id: STAFF_ID }).then(function (r) {
            r.success ? H.success(r.message, loadPrograms) : H.error(r.message);
        });
    }

    if (CAN_MANAGE) {
        document.getElementById('btnNewProgram').addEventListener('click', function () { openProgramForm(null); });
        document.getElementById('saveProgramBtn').addEventListener('click', function () {
            var name = document.getElementById('programName').value.trim();
            var start = document.getElementById('startDate').value;
            var end = document.getElementById('endDate').value;
            if (!name || !start || !end) { H.error('Name and dates are required'); return; }
            if (end < start) { H.error('End date cannot be before start date'); return; }
            H.post('ajax/hr/save-ppdp-program.php', {
                id: document.getElementById('programId').value,
                program_name: name,
                description: document.getElementById('description').value,
                start_date: start,
                end_date: end,
                capacity: document.getElementById('capacity').value,
                branch_id: document.getElementById('programBranch').value,
                facilitator_id: document.getElementById('facilitatorId').value,
                status: document.getElementById('programStatus').value
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('programModal')).hide();
                    H.success(r.message, loadPrograms);
                } else H.error(r.message);
            });
        });
        document.getElementById('btnAddParticipant').addEventListener('click', function () {
            var staffId = document.getElementById('addStaffId').value;
            if (!staffId || !activeProgramId) { H.error('Select a staff member'); return; }
            H.post('ajax/hr/save-ppdp-participant.php', {
                action: 'add',
                program_id: activeProgramId,
                staff_id: staffId
            }).then(function (r) {
                if (r.success) {
                    document.getElementById('addStaffId').value = '';
                    H.success(r.message, function () { loadParticipants(activeProgramId); loadPrograms(); });
                } else H.error(r.message);
            });
        });
    }

    document.getElementById('btnFilter').addEventListener('click', loadPrograms);
    document.getElementById('filterYear').addEventListener('change', loadPrograms);
    document.getElementById('startDate').addEventListener('change', function () {
        var end = document.getElementById('endDate');
        if (!end.value || end.value < this.value) end.value = this.value;
        calcDuration();
    });
    document.getElementById('endDate').addEventListener('change', calcDuration);

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id]');
        if (!t) return;
        var id = t.dataset.id;

        if (t.classList.contains('btn-view')) { viewProgram(id); return; }
        if (t.classList.contains('btn-edit')) {
            var p = programsCache.find(function (x) { return String(x.id) === String(id); });
            if (p) openProgramForm(p);
            return;
        }
        if (t.classList.contains('btn-participants')) {
            openParticipants(id, t.dataset.name || '');
            return;
        }
        if (t.classList.contains('prog-status')) {
            e.preventDefault();
            setProgramStatus(id, t.dataset.status);
            return;
        }
        if (t.classList.contains('btn-register')) {
            registerSelf(id);
            return;
        }
        if (t.classList.contains('pt-complete')) {
            H.post('ajax/hr/save-ppdp-participant.php', { participant_id: id, status: 'Completed', progress_percent: 100 })
                .then(function (r) { r.success ? H.success(r.message, function () { loadParticipants(activeProgramId); loadPrograms(); }) : H.error(r.message); });
            return;
        }
        if (t.classList.contains('pt-attend')) {
            H.post('ajax/hr/save-ppdp-participant.php', { participant_id: id, status: 'Attending', progress_percent: 50 })
                .then(function (r) { r.success ? H.success(r.message, function () { loadParticipants(activeProgramId); }) : H.error(r.message); });
            return;
        }
        if (t.classList.contains('pt-remove')) {
            if (!confirm('Remove this participant from the program?')) return;
            H.post('ajax/hr/save-ppdp-participant.php', { action: 'remove', participant_id: id })
                .then(function (r) { r.success ? H.success(r.message, function () { loadParticipants(activeProgramId); loadPrograms(); }) : H.error(r.message); });
        }
    });

    loadPrograms();
})();
</script>
