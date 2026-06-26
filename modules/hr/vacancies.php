<?php
/**
 * Recruitment & Hiring — vacancies, applications, interviews, offers
 */
require_once '../../config/config.php';
hrRequirePage('hr_recruitment', 'view');

$pageTitle = 'Recruitment';
$branches = fetchAll(executeQuery("SELECT id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name"));
$canEdit = hasRole(['Super Admin', 'Admin']) || (function_exists('canPerform') && canPerform('hr_recruitment', 'edit'));

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
<a href="<?php echo APP_URL; ?>careers.php" target="_blank" class="btn btn-outline-info me-2">
<i class="ri-external-link-line"></i> Careers Portal
</a>
<?php if ($canEdit): ?>
<button type="button" class="btn btn-primary" id="btnNewVacancy">
<i class="ri-add-line"></i> New Vacancy
</button>
<?php endif; ?>
</div>
<h4 class="page-title">Recruitment &amp; Hiring</h4>
</div>
</div>
</div>

<!-- KPI Cards -->
<div class="row" id="recruitStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-briefcase-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Open Jobs</p><h4 class="mb-0" id="statOpen">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-file-user-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Applications</p><h4 class="mb-0" id="statApps">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-user-voice-line font-22"></i></div>
<div><p class="text-muted mb-1 small">In Interview</p><h4 class="mb-0" id="statInterview">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-mail-send-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Offers</p><h4 class="mb-0" id="statOffers">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-secondary-lighten text-secondary rounded p-2 me-2"><i class="ri-calendar-check-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Scheduled</p><h4 class="mb-0" id="statScheduled">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-user-star-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Hired</p><h4 class="mb-0" id="statHired">—</h4></div>
</div></div></div>
</div>
</div>

<ul class="nav nav-tabs mb-3" id="recruitTabs">
<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabVac">Vacancies</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabApp">Applications</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabInt">Interviews</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabOffer">Offer Letters</a></li>
</ul>

<div class="tab-content">

<!-- Vacancies -->
<div class="tab-pane fade show active" id="tabVac">
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-3">
<label class="form-label small">Search</label>
<input type="text" id="vacFilterSearch" class="form-control" placeholder="Title, department, vacancy no…">
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="vacFilterStatus" class="form-select">
<option value="">All</option>
<option value="Draft">Draft</option>
<option value="Published">Published</option>
<option value="Closed">Closed</option>
<option value="Filled">Filled</option>
<option value="Cancelled">Cancelled</option>
</select>
</div>
<?php if (hasRole(['Super Admin'])): ?>
<div class="col-md-3">
<label class="form-label small">Branch</label>
<select id="vacFilterBranch" class="form-select">
<option value="">All Branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnVacFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>
<div class="card"><div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="vacTable">
<thead class="table-light">
<tr>
<th>Vacancy No</th>
<th>Position</th>
<th>Department</th>
<th>Branch</th>
<th>Deadline</th>
<th>Openings</th>
<th>Apps</th>
<th>Status</th>
<th style="min-width:140px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="9" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div></div>
</div>

<!-- Applications -->
<div class="tab-pane fade" id="tabApp">
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-3">
<label class="form-label small">Search</label>
<input type="text" id="appFilterSearch" class="form-control" placeholder="Name, email, application no…">
</div>
<div class="col-md-3">
<label class="form-label small">Vacancy</label>
<select id="appFilterVacancy" class="form-select"><option value="">All vacancies</option></select>
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="appFilterStatus" class="form-select">
<option value="">All</option>
<option value="Applied">Applied</option>
<option value="Screening">Screening</option>
<option value="Shortlisted">Shortlisted</option>
<option value="Interview">Interview</option>
<option value="Offer">Offer</option>
<option value="Hired">Hired</option>
<option value="Rejected">Rejected</option>
</select>
</div>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnAppFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>
<div class="card"><div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="appTable">
<thead class="table-light">
<tr>
<th>Application No</th>
<th>Candidate</th>
<th>Position</th>
<th>Contact</th>
<th>Applied</th>
<th>Status</th>
<th>CV</th>
<th style="min-width:120px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="8" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div></div>
</div>

<!-- Interviews -->
<div class="tab-pane fade" id="tabInt">
<div class="card"><div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="intTable">
<thead class="table-light">
<tr>
<th>Candidate</th>
<th>Position</th>
<th>Date &amp; Time</th>
<th>Type</th>
<th>Location / Link</th>
<th>Status</th>
<th>Rating</th>
<th>Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="8" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div></div>
</div>

<!-- Offers -->
<div class="tab-pane fade" id="tabOffer">
<div class="card"><div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="offerTable">
<thead class="table-light">
<tr>
<th>Candidate</th>
<th>Position</th>
<th>Salary</th>
<th>Start Date</th>
<th>Expiry</th>
<th>Status</th>
<th style="min-width:140px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="7" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div></div>
</div>

</div><!-- tab-content -->
</div>
</div>
</div>

<!-- Vacancy modal -->
<div class="modal fade" id="vacModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="vacModalTitle"><i class="ri-briefcase-line"></i> New Vacancy</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="vacForm">
<input type="hidden" id="vacId" value="">
<div class="row g-3">
<div class="col-md-8">
<label class="form-label">Job Title <span class="text-danger">*</span></label>
<input type="text" id="jobTitle" class="form-control" required placeholder="e.g. Mathematics Teacher">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select id="vacStatus" class="form-select">
<option value="Draft">Draft</option>
<option value="Published">Published</option>
<option value="Closed">Closed</option>
<option value="Filled">Filled</option>
<option value="Cancelled">Cancelled</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Department</label>
<input type="text" id="department" class="form-control" placeholder="e.g. Academic">
</div>
<div class="col-md-4">
<label class="form-label">Employment Type</label>
<select id="employmentType" class="form-select">
<option value="Full Time">Full Time</option>
<option value="Part Time">Part Time</option>
<option value="Contract">Contract</option>
<option value="Internship">Internship</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Branch</label>
<select id="vacBranch" class="form-select">
<option value="">All / HQ</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Application Deadline</label>
<input type="date" id="deadline" class="form-control">
</div>
<div class="col-md-4">
<label class="form-label">Openings</label>
<input type="number" id="openings" class="form-control" min="1" value="1">
</div>
<div class="col-md-4">
<label class="form-label">Salary Range (<?php echo CURRENCY_SYMBOL; ?>)</label>
<div class="input-group">
<input type="number" step="0.01" id="salaryMin" class="form-control" placeholder="Min">
<span class="input-group-text">–</span>
<input type="number" step="0.01" id="salaryMax" class="form-control" placeholder="Max">
</div>
</div>
<div class="col-12">
<label class="form-label">Job Description</label>
<textarea id="description" class="form-control" rows="4" placeholder="Role summary, responsibilities…"></textarea>
</div>
<div class="col-12">
<label class="form-label">Requirements</label>
<textarea id="requirements" class="form-control" rows="3" placeholder="Qualifications, experience, skills…"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveVacBtn"><i class="ri-save-line"></i> Save Vacancy</button>
</div>
</div>
</div>
</div>

<!-- View vacancy -->
<div class="modal fade" id="viewVacModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Vacancy Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewVacBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<!-- View application -->
<div class="modal fade" id="viewAppModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Application Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewAppBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<!-- Interview modal -->
<div class="modal fade" id="intModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Schedule Interview</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" id="intAppId">
<p class="text-muted mb-2" id="intCandidate"></p>
<label class="form-label">Date &amp; Time</label>
<input type="datetime-local" id="intDate" class="form-control mb-3">
<label class="form-label">Type</label>
<select id="intType" class="form-select mb-3">
<option value="In_Person">In Person</option>
<option value="Phone">Phone</option>
<option value="Video">Video</option>
<option value="Panel">Panel</option>
</select>
<label class="form-label">Location or meeting link</label>
<input type="text" id="intLocation" class="form-control mb-3">
<label class="form-label">Notes</label>
<textarea id="intComments" class="form-control mb-3" rows="2"></textarea>
<div class="form-check">
<input type="checkbox" class="form-check-input" id="intNotify" checked>
<label class="form-check-label" for="intNotify">Email candidate</label>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveIntBtn">Schedule</button>
</div>
</div>
</div>
</div>

<!-- Evaluate interview -->
<div class="modal fade" id="evalModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Interview Evaluation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" id="evalIntId">
<label class="form-label">Overall rating (1–5)</label>
<input type="number" step="0.1" min="1" max="5" id="evalRating" class="form-control mb-3">
<label class="form-label">Recommendation</label>
<select id="evalRec" class="form-select mb-3">
<option value="Recommend">Recommend</option>
<option value="Hold">Hold</option>
<option value="Reject">Reject</option>
</select>
<label class="form-label">Comments</label>
<textarea id="evalComments" class="form-control" rows="3"></textarea>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveEvalBtn">Save Evaluation</button>
</div>
</div>
</div>
</div>

<!-- Offer modal -->
<div class="modal fade" id="offerModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Generate Offer Letter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" id="offerAppId">
<p class="text-muted" id="offerCandidate"></p>
<label class="form-label">Offered salary (<?php echo CURRENCY_SYMBOL; ?>)</label>
<input type="number" step="0.01" id="offerSalary" class="form-control mb-3">
<label class="form-label">Start date</label>
<input type="date" id="offerStart" class="form-control mb-3">
<label class="form-label">Expiry date</label>
<input type="date" id="offerExpiry" class="form-control mb-3">
<label class="form-label">Status</label>
<select id="offerStatus" class="form-select">
<option value="Draft">Draft</option>
<option value="Sent">Sent</option>
</select>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveOfferBtn">Create Offer</button>
</div>
</div>
</div>
</div>

<!-- Hire modal -->
<div class="modal fade" id="hireModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Hire Candidate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" id="hireAppId">
<p class="text-muted" id="hireCandidate"></p>
<label class="form-label">Designation</label>
<input type="text" id="hireDesignation" class="form-control mb-3">
<label class="form-label">Department</label>
<input type="text" id="hireDepartment" class="form-control mb-3">
<label class="form-label">Gender</label>
<select id="hireGender" class="form-select mb-3">
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>
<label class="form-label">Date of birth</label>
<input type="date" id="hireDob" class="form-control mb-3" value="1990-01-01">
<label class="form-label">Joining date</label>
<input type="date" id="hireJoining" class="form-control mb-3" value="<?php echo date('Y-m-d'); ?>">
<label class="form-label">Basic salary (optional)</label>
<input type="number" step="0.01" id="hireSalary" class="form-control mb-3">
<label class="form-label">Branch</label>
<select id="hireBranch" class="form-select mb-3">
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-success" id="saveHireBtn">Confirm Hire</button>
</div>
</div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var CAN_EDIT = <?php echo $canEdit ? 'true' : 'false'; ?>;
    var CURRENCY = <?php echo json_encode(CURRENCY_SYMBOL); ?>;
    var vacCache = [];
    var appCache = [];
    var vacModal = null;
    var viewVacModal = null;
    var viewAppModal = null;

    function statusBadge(s, map) {
        var cls = (map && map[s]) || 'secondary';
        return H.badge(s, cls);
    }
    var vacStatusMap = { Draft: 'secondary', Published: 'success', Closed: 'warning', Filled: 'info', Cancelled: 'danger' };
    var appStatusMap = { Applied: 'secondary', Screening: 'info', Shortlisted: 'primary', Interview: 'warning', Offer: 'success', Hired: 'success', Rejected: 'danger' };

    function formatSalary(min, max) {
        if (min == null && max == null) return '—';
        var fmt = function (n) { return Number(n).toLocaleString(); };
        if (min != null && max != null) return CURRENCY + fmt(min) + ' – ' + CURRENCY + fmt(max);
        if (min != null) return 'From ' + CURRENCY + fmt(min);
        return 'Up to ' + CURRENCY + fmt(max);
    }

    function vacQuery() {
        var q = [];
        var s = document.getElementById('vacFilterSearch').value.trim();
        if (s) q.push('q=' + encodeURIComponent(s));
        var st = document.getElementById('vacFilterStatus').value;
        if (st) q.push('status=' + encodeURIComponent(st));
        var br = document.getElementById('vacFilterBranch');
        if (br && br.value) q.push('branch_id=' + br.value);
        return q.length ? '?' + q.join('&') : '';
    }

    function appQuery() {
        var q = [];
        var s = document.getElementById('appFilterSearch').value.trim();
        if (s) q.push('q=' + encodeURIComponent(s));
        var v = document.getElementById('appFilterVacancy').value;
        if (v) q.push('vacancy_id=' + v);
        var st = document.getElementById('appFilterStatus').value;
        if (st) q.push('status=' + encodeURIComponent(st));
        return q.length ? '?' + q.join('&') : '';
    }

    function updateStats(stats) {
        stats = stats || {};
        document.getElementById('statOpen').textContent = stats.open_vacancies || 0;
        document.getElementById('statApps').textContent = stats.total_applications || 0;
        document.getElementById('statInterview').textContent = stats.in_interview || 0;
        document.getElementById('statOffers').textContent = stats.offers_pending || 0;
        document.getElementById('statScheduled').textContent = stats.scheduled_interviews || 0;
        document.getElementById('statHired').textContent = stats.hired_total || 0;
    }

    function fillVacancyFilter() {
        var sel = document.getElementById('appFilterVacancy');
        var cur = sel.value;
        sel.innerHTML = '<option value="">All vacancies</option>' +
            vacCache.map(function (v) {
                return '<option value="' + v.id + '">' + H.escapeHtml(v.vacancy_no + ' — ' + v.job_title) + '</option>';
            }).join('');
        if (cur) sel.value = cur;
    }

    function vacActions(v) {
        if (!CAN_EDIT) {
            return '<button type="button" class="btn btn-sm btn-outline-info btn-view-vac" data-id="' + v.id + '"><i class="ri-eye-line"></i></button>';
        }
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view-vac" data-id="' + v.id + '" title="View"><i class="ri-eye-line"></i></button>' +
            '<button type="button" class="btn btn-outline-primary btn-edit-vac" data-id="' + v.id + '" title="Edit"><i class="ri-edit-line"></i></button>' +
            '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>' +
            '<ul class="dropdown-menu dropdown-menu-end">';
        if (v.status === 'Draft') html += '<li><a class="dropdown-item vac-status" data-id="' + v.id + '" data-status="Published" href="#">Publish</a></li>';
        if (v.status === 'Published') html += '<li><a class="dropdown-item vac-status" data-id="' + v.id + '" data-status="Closed" href="#">Close posting</a></li>';
        if (v.status !== 'Cancelled' && v.status !== 'Filled') html += '<li><a class="dropdown-item vac-status" data-id="' + v.id + '" data-status="Cancelled" href="#">Cancel</a></li>';
        html += '</ul></div></div>';
        return html;
    }

    function loadVac() {
        var tb = document.querySelector('#vacTable tbody');
        tb.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-vacancies.php' + vacQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            vacCache = payload.vacancies || [];
            updateStats(payload.stats);
            fillVacancyFilter();
            if (!vacCache.length) {
                tb.innerHTML = '<tr><td colspan="9" class="text-muted text-center py-4">No vacancies found</td></tr>';
                return;
            }
            tb.innerHTML = vacCache.map(function (v) {
                return '<tr>' +
                    '<td><code>' + H.escapeHtml(v.vacancy_no) + '</code></td>' +
                    '<td><strong>' + H.escapeHtml(v.job_title) + '</strong><br><small class="text-muted">' + H.escapeHtml(v.employment_type || '') + '</small></td>' +
                    '<td>' + H.escapeHtml(v.department || '—') + '</td>' +
                    '<td>' + H.escapeHtml(v.branch_name || 'All') + '</td>' +
                    '<td>' + H.formatDate(v.application_deadline) + '</td>' +
                    '<td>' + (v.openings || 1) + '</td>' +
                    '<td><span class="badge bg-light text-dark">' + (v.application_count || 0) + '</span>' +
                    (v.hired_count > 0 ? ' <small class="text-success">(' + v.hired_count + ' hired)</small>' : '') + '</td>' +
                    '<td>' + statusBadge(v.status, vacStatusMap) + '</td>' +
                    '<td>' + vacActions(v) + '</td></tr>';
            }).join('');
        }).catch(function (e) { H.error(e.message || 'Failed to load vacancies'); });
    }

    function appDropdown(a) {
        if (!CAN_EDIT || a.status === 'Hired' || a.status === 'Rejected') {
            return '<button type="button" class="btn btn-sm btn-outline-info btn-view-app" data-id="' + a.id + '"><i class="ri-eye-line"></i></button>';
        }
        var name = H.escapeHtml(a.first_name + ' ' + a.last_name);
        var job = H.escapeHtml(a.job_title || '');
        var dept = H.escapeHtml(a.department || '');
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view-app" data-id="' + a.id + '"><i class="ri-eye-line"></i></button>' +
            '<button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">Actions</button>' +
            '<ul class="dropdown-menu dropdown-menu-end">';
        if (a.status === 'Applied' || a.status === 'Screening') {
            html += '<li><a class="dropdown-item app-act" data-id="' + a.id + '" data-act="Shortlisted" href="#">Shortlist</a></li>';
            html += '<li><a class="dropdown-item app-act" data-id="' + a.id + '" data-act="Screening" href="#">Move to screening</a></li>';
        }
        if (['Shortlisted', 'Interview', 'Offer'].indexOf(a.status) >= 0 || a.status === 'Applied') {
            html += '<li><a class="dropdown-item sch-int" data-id="' + a.id + '" data-name="' + name + '" href="#">Schedule interview</a></li>';
        }
        if (['Shortlisted', 'Interview', 'Offer'].indexOf(a.status) >= 0) {
            html += '<li><a class="dropdown-item mk-offer" data-id="' + a.id + '" data-name="' + name + '" data-job="' + job + '" href="#">Create offer</a></li>';
            html += '<li><a class="dropdown-item hire" data-id="' + a.id + '" data-name="' + name + '" data-job="' + job + '" data-dept="' + dept + '" href="#">Hire to staff</a></li>';
        }
        html += '<li><hr class="dropdown-divider"></li>';
        html += '<li><a class="dropdown-item text-danger app-act" data-id="' + a.id + '" data-act="Rejected" href="#">Reject</a></li>';
        html += '</ul></div>';
        return html;
    }

    function loadApp() {
        var tb = document.querySelector('#appTable tbody');
        tb.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-job-applications.php' + appQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            appCache = res.data || [];
            if (!appCache.length) {
                tb.innerHTML = '<tr><td colspan="8" class="text-muted text-center py-4">No applications found</td></tr>';
                return;
            }
            tb.innerHTML = appCache.map(function (a) {
                var cv = a.cv_path ? '<a href="' + H.apiUrl() + a.cv_path + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="ri-file-line"></i></a>' : '—';
                return '<tr>' +
                    '<td><code>' + H.escapeHtml(a.application_no) + '</code></td>' +
                    '<td><strong>' + H.escapeHtml(a.first_name + ' ' + a.last_name) + '</strong></td>' +
                    '<td>' + H.escapeHtml(a.job_title) + '<br><small class="text-muted">' + H.escapeHtml(a.vacancy_no || '') + '</small></td>' +
                    '<td><small>' + H.escapeHtml(a.email) + '<br>' + H.escapeHtml(a.phone || '') + '</small></td>' +
                    '<td>' + H.formatDate(a.applied_at) + '</td>' +
                    '<td>' + statusBadge(a.status, appStatusMap) + '</td>' +
                    '<td>' + cv + '</td>' +
                    '<td>' + appDropdown(a) + '</td></tr>';
            }).join('');
        }).catch(function (e) { H.error(e.message || 'Failed to load applications'); });
    }

    function loadInt() {
        var tb = document.querySelector('#intTable tbody');
        tb.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-interviews.php').then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var rows = res.data || [];
            if (!rows.length) {
                tb.innerHTML = '<tr><td colspan="8" class="text-muted text-center py-4">No interviews scheduled</td></tr>';
                return;
            }
            tb.innerHTML = rows.map(function (i) {
                var act = (CAN_EDIT && i.status === 'Scheduled')
                    ? '<button type="button" class="btn btn-sm btn-primary eval-int" data-id="' + i.id + '">Evaluate</button>' : '—';
                return '<tr>' +
                    '<td>' + H.escapeHtml(i.first_name + ' ' + i.last_name) + '</td>' +
                    '<td>' + H.escapeHtml(i.job_title) + '</td>' +
                    '<td>' + (i.interview_date || '—').replace('T', ' ').substring(0, 16) + '</td>' +
                    '<td>' + H.escapeHtml(String(i.interview_type || '').replace(/_/g, ' ')) + '</td>' +
                    '<td><small>' + H.escapeHtml(i.location_or_link || '—') + '</small></td>' +
                    '<td>' + statusBadge(i.status, { Scheduled: 'warning', Completed: 'success', Cancelled: 'danger', No_Show: 'secondary' }) + '</td>' +
                    '<td>' + (i.overall_rating != null ? i.overall_rating : '—') + '</td>' +
                    '<td>' + act + '</td></tr>';
            }).join('');
        });
    }

    function loadOffer() {
        var tb = document.querySelector('#offerTable tbody');
        tb.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-offer-letters.php').then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var rows = res.data || [];
            if (!rows.length) {
                tb.innerHTML = '<tr><td colspan="7" class="text-muted text-center py-4">No offer letters yet</td></tr>';
                return;
            }
            tb.innerHTML = rows.map(function (o) {
                var acts = '<a href="' + H.apiUrl() + 'modules/hr/download-offer-letter-pdf.php?id=' + o.id + '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-file-pdf-line"></i> PDF</a> ';
                if (CAN_EDIT && o.status !== 'Sent' && o.status !== 'Accepted') {
                    acts += '<button type="button" class="btn btn-sm btn-success send-offer" data-id="' + o.id + '">Send</button>';
                }
                return '<tr>' +
                    '<td>' + H.escapeHtml(o.first_name + ' ' + o.last_name) + '</td>' +
                    '<td>' + H.escapeHtml(o.job_title) + '</td>' +
                    '<td>' + CURRENCY + Number(o.offered_salary || 0).toLocaleString() + '</td>' +
                    '<td>' + H.formatDate(o.start_date) + '</td>' +
                    '<td>' + H.formatDate(o.expiry_date) + '</td>' +
                    '<td>' + statusBadge(o.status, { Draft: 'secondary', Sent: 'info', Accepted: 'success', Declined: 'danger', Expired: 'warning' }) + '</td>' +
                    '<td>' + acts + '</td></tr>';
            }).join('');
        });
    }

    function openVacForm(data) {
        document.getElementById('vacForm').reset();
        document.getElementById('vacId').value = data ? data.id : '';
        document.getElementById('vacModalTitle').innerHTML = data
            ? '<i class="ri-edit-line"></i> Edit Vacancy'
            : '<i class="ri-briefcase-line"></i> New Vacancy';
        if (data) {
            document.getElementById('jobTitle').value = data.job_title || '';
            document.getElementById('department').value = data.department || '';
            document.getElementById('employmentType').value = data.employment_type || 'Full Time';
            document.getElementById('vacBranch').value = data.branch_id || '';
            document.getElementById('deadline').value = (data.application_deadline || '').substring(0, 10);
            document.getElementById('openings').value = data.openings || 1;
            document.getElementById('salaryMin').value = data.salary_range_min != null ? data.salary_range_min : '';
            document.getElementById('salaryMax').value = data.salary_range_max != null ? data.salary_range_max : '';
            document.getElementById('description').value = data.description || '';
            document.getElementById('requirements').value = data.requirements || '';
            document.getElementById('vacStatus').value = data.status || 'Draft';
        }
        if (!vacModal) vacModal = new bootstrap.Modal(document.getElementById('vacModal'));
        vacModal.show();
    }

    function viewVacancy(id) {
        var v = vacCache.find(function (x) { return String(x.id) === String(id); });
        if (!v) return;
        document.getElementById('viewVacBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Vacancy No</strong><br><code>' + H.escapeHtml(v.vacancy_no) + '</code></div>' +
            '<div class="col-md-6"><strong>Status</strong><br>' + statusBadge(v.status, vacStatusMap) + '</div>' +
            '<div class="col-12"><strong>Position</strong><br>' + H.escapeHtml(v.job_title) + '</div>' +
            '<div class="col-md-4"><strong>Department</strong><br>' + H.escapeHtml(v.department || '—') + '</div>' +
            '<div class="col-md-4"><strong>Employment</strong><br>' + H.escapeHtml(v.employment_type || '—') + '</div>' +
            '<div class="col-md-4"><strong>Branch</strong><br>' + H.escapeHtml(v.branch_name || 'All') + '</div>' +
            '<div class="col-md-4"><strong>Deadline</strong><br>' + H.formatDate(v.application_deadline) + '</div>' +
            '<div class="col-md-4"><strong>Openings</strong><br>' + (v.openings || 1) + '</div>' +
            '<div class="col-md-4"><strong>Salary range</strong><br>' + formatSalary(v.salary_range_min, v.salary_range_max) + '</div>' +
            '<div class="col-12"><strong>Description</strong><br>' + H.escapeHtml(v.description || '—').replace(/\n/g, '<br>') + '</div>' +
            '<div class="col-12"><strong>Requirements</strong><br>' + H.escapeHtml(v.requirements || '—').replace(/\n/g, '<br>') + '</div>' +
            '</div>';
        if (!viewVacModal) viewVacModal = new bootstrap.Modal(document.getElementById('viewVacModal'));
        viewVacModal.show();
    }

    function viewApplication(id) {
        var a = appCache.find(function (x) { return String(x.id) === String(id); });
        if (!a) return;
        var cv = a.cv_path ? '<a href="' + H.apiUrl() + a.cv_path + '" target="_blank">Download CV</a>' : '—';
        document.getElementById('viewAppBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Application No</strong><br><code>' + H.escapeHtml(a.application_no) + '</code></div>' +
            '<div class="col-md-6"><strong>Status</strong><br>' + statusBadge(a.status, appStatusMap) + '</div>' +
            '<div class="col-md-6"><strong>Candidate</strong><br>' + H.escapeHtml(a.first_name + ' ' + a.last_name) + '</div>' +
            '<div class="col-md-6"><strong>Applied for</strong><br>' + H.escapeHtml(a.job_title) + ' (' + H.escapeHtml(a.vacancy_no || '') + ')</div>' +
            '<div class="col-md-6"><strong>Email</strong><br>' + H.escapeHtml(a.email) + '</div>' +
            '<div class="col-md-6"><strong>Phone</strong><br>' + H.escapeHtml(a.phone || '—') + '</div>' +
            '<div class="col-md-6"><strong>Applied at</strong><br>' + H.formatDate(a.applied_at) + '</div>' +
            '<div class="col-md-6"><strong>CV</strong><br>' + cv + '</div>' +
            '<div class="col-12"><strong>Cover letter</strong><br>' + H.escapeHtml(a.cover_letter || '—').replace(/\n/g, '<br>') + '</div>' +
            '</div>';
        if (!viewAppModal) viewAppModal = new bootstrap.Modal(document.getElementById('viewAppModal'));
        viewAppModal.show();
    }

    function setVacancyStatus(id, status) {
        H.post('ajax/hr/save-vacancy.php', { id: id, status: status }).then(function (r) {
            r.success ? H.success(r.message, loadVac) : H.error(r.message);
        });
    }

    if (CAN_EDIT) {
        document.getElementById('btnNewVacancy').addEventListener('click', function () { openVacForm(null); });
        document.getElementById('saveVacBtn').addEventListener('click', function () {
            var title = document.getElementById('jobTitle').value.trim();
            if (!title) { H.error('Job title is required'); return; }
            H.post('ajax/hr/save-vacancy.php', {
                id: document.getElementById('vacId').value,
                job_title: title,
                department: document.getElementById('department').value,
                employment_type: document.getElementById('employmentType').value,
                branch_id: document.getElementById('vacBranch').value,
                application_deadline: document.getElementById('deadline').value,
                openings: document.getElementById('openings').value,
                salary_range_min: document.getElementById('salaryMin').value,
                salary_range_max: document.getElementById('salaryMax').value,
                description: document.getElementById('description').value,
                requirements: document.getElementById('requirements').value,
                status: document.getElementById('vacStatus').value
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('vacModal')).hide();
                    H.success(r.message, function () { loadVac(); loadApp(); });
                } else H.error(r.message);
            });
        });
        document.getElementById('saveIntBtn').addEventListener('click', function () {
            H.post('ajax/hr/schedule-interview.php', {
                application_id: document.getElementById('intAppId').value,
                interview_date: document.getElementById('intDate').value,
                interview_type: document.getElementById('intType').value,
                location_or_link: document.getElementById('intLocation').value,
                comments: document.getElementById('intComments').value,
                notify_candidate: document.getElementById('intNotify').checked
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('intModal')).hide();
                    H.success(r.message, function () { loadApp(); loadInt(); });
                } else H.error(r.message);
            });
        });
        document.getElementById('saveEvalBtn').addEventListener('click', function () {
            H.post('ajax/hr/complete-interview.php', {
                interview_id: document.getElementById('evalIntId').value,
                overall_rating: document.getElementById('evalRating').value,
                recommendation: document.getElementById('evalRec').value,
                comments: document.getElementById('evalComments').value
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('evalModal')).hide();
                    H.success(r.message, loadInt);
                } else H.error(r.message);
            });
        });
        document.getElementById('saveOfferBtn').addEventListener('click', function () {
            H.post('ajax/hr/save-offer-letter.php', {
                application_id: document.getElementById('offerAppId').value,
                offered_salary: document.getElementById('offerSalary').value,
                start_date: document.getElementById('offerStart').value,
                expiry_date: document.getElementById('offerExpiry').value,
                status: document.getElementById('offerStatus').value
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('offerModal')).hide();
                    H.success(r.message, function () { loadApp(); loadOffer(); });
                } else H.error(r.message);
            });
        });
        document.getElementById('saveHireBtn').addEventListener('click', function () {
            H.post('ajax/hr/hire-application.php', {
                application_id: document.getElementById('hireAppId').value,
                designation: document.getElementById('hireDesignation').value,
                department: document.getElementById('hireDepartment').value,
                gender: document.getElementById('hireGender').value,
                date_of_birth: document.getElementById('hireDob').value,
                joining_date: document.getElementById('hireJoining').value,
                basic_salary: document.getElementById('hireSalary').value,
                branch_id: document.getElementById('hireBranch').value
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('hireModal')).hide();
                    H.success(r.message + (r.data && r.data.staff_code ? ' (Staff: ' + r.data.staff_code + ')' : ''), function () { loadApp(); loadVac(); });
                } else H.error(r.message);
            });
        });
    }

    document.getElementById('btnVacFilter').addEventListener('click', loadVac);
    document.getElementById('btnAppFilter').addEventListener('click', loadApp);
    document.querySelector('a[href="#tabInt"]').addEventListener('shown.bs.tab', loadInt);
    document.querySelector('a[href="#tabOffer"]').addEventListener('shown.bs.tab', loadOffer);

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id]');
        if (!t) return;
        var id = t.dataset.id;

        if (t.classList.contains('btn-view-vac')) { viewVacancy(id); return; }
        if (t.classList.contains('btn-edit-vac')) {
            var v = vacCache.find(function (x) { return String(x.id) === String(id); });
            if (v) openVacForm(v);
            return;
        }
        if (t.classList.contains('vac-status')) {
            e.preventDefault();
            setVacancyStatus(id, t.dataset.status);
            return;
        }
        if (t.classList.contains('btn-view-app')) { viewApplication(id); return; }
        if (t.classList.contains('app-act')) {
            e.preventDefault();
            H.post('ajax/hr/update-application-status.php', { application_id: id, status: t.dataset.act })
                .then(function (r) { r.success ? H.success(r.message, loadApp) : H.error(r.message); });
            return;
        }
        if (t.classList.contains('sch-int')) {
            e.preventDefault();
            document.getElementById('intAppId').value = id;
            document.getElementById('intCandidate').textContent = t.dataset.name;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('intModal')).show();
            return;
        }
        if (t.classList.contains('eval-int')) {
            document.getElementById('evalIntId').value = id;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('evalModal')).show();
            return;
        }
        if (t.classList.contains('mk-offer')) {
            e.preventDefault();
            document.getElementById('offerAppId').value = id;
            document.getElementById('offerCandidate').textContent = t.dataset.name + ' — ' + t.dataset.job;
            document.getElementById('offerStart').value = new Date().toISOString().slice(0, 10);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('offerModal')).show();
            return;
        }
        if (t.classList.contains('hire')) {
            e.preventDefault();
            document.getElementById('hireAppId').value = id;
            document.getElementById('hireCandidate').textContent = t.dataset.name;
            document.getElementById('hireDesignation').value = t.dataset.job || '';
            document.getElementById('hireDepartment').value = t.dataset.dept || '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('hireModal')).show();
            return;
        }
        if (t.classList.contains('send-offer')) {
            H.post('ajax/hr/send-offer-letter.php', { offer_id: id })
                .then(function (r) { r.success ? H.success(r.message, loadOffer) : H.error(r.message); });
        }
    });

    loadVac();
    loadApp();
})();
</script>
