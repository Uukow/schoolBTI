<?php
/**
 * Public Holidays Management
 */

require_once '../../config/config.php';
hrRequirePage('hr_attendance', 'view');

$pageTitle = 'Public Holidays';
$currentUser = getCurrentUser();
$branches = fetchAll(executeQuery("SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name"));
$year = (int)($_GET['year'] ?? date('Y'));

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
<button type="button" class="btn btn-primary" id="btnNewHoliday">
<i class="ri-calendar-event-line"></i> Add Holiday
</button>
</div>
<h4 class="page-title">Public Holidays &amp; Days Off</h4>
</div>
</div>
</div>

<!-- KPI Cards -->
<div class="row" id="holidayStats">
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-3"><i class="ri-calendar-line font-24"></i></div>
<div><p class="text-muted mb-1">Holidays (<?php echo $year; ?>)</p><h3 class="mb-0" id="statTotal">—</h3></div>
</div></div></div>
</div>
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-3"><i class="ri-sun-line font-24"></i></div>
<div><p class="text-muted mb-1">Total Days Off</p><h3 class="mb-0" id="statDays">—</h3></div>
</div></div></div>
</div>
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-3"><i class="ri-government-line font-24"></i></div>
<div><p class="text-muted mb-1">Public Holidays</p><h3 class="mb-0" id="statPublic">—</h3></div>
</div></div></div>
</div>
<div class="col-md-3">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-3"><i class="ri-time-line font-24"></i></div>
<div><p class="text-muted mb-1">Upcoming</p><h3 class="mb-0" id="statUpcoming">—</h3></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-2">
<label class="form-label small">Year</label>
<select id="filterYear" class="form-select">
<?php for ($y = date('Y') - 1; $y <= date('Y') + 3; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<?php if (hasRole(['Super Admin'])): ?>
<div class="col-md-3">
<label class="form-label small">Branch</label>
<select id="filterBranch" class="form-select">
<option value="">All Branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-2">
<label class="form-label small">Type</label>
<select id="filterType" class="form-select">
<option value="">All Types</option>
<option value="Public">Public</option>
<option value="Institutional">Institutional</option>
<option value="Optional">Optional</option>
</select>
</div>
<div class="col-md-3">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Holiday name…">
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
<table class="table table-hover align-middle" id="holidaysTable">
<thead class="table-light">
<tr>
<th>Holiday</th>
<th>Period</th>
<th>Days</th>
<th>Type</th>
<th>Branch</th>
<th>Recurring</th>
<th style="min-width:120px">Actions</th>
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

<!-- Add / Edit Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="holidayModalTitle"><i class="ri-calendar-event-line"></i> Add Holiday</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="holidayForm">
<input type="hidden" id="holidayId" value="">
<div class="row g-3">
<div class="col-12">
<label class="form-label">Holiday Name <span class="text-danger">*</span></label>
<input type="text" class="form-control" id="holidayName" required placeholder="e.g. Eid Al-Fitr, Summer Break">
</div>
<div class="col-md-4">
<label class="form-label">Start Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="holidayStart" required>
</div>
<div class="col-md-4">
<label class="form-label">End Date <span class="text-danger">*</span></label>
<input type="date" class="form-control" id="holidayEnd" required>
<small class="text-muted">Same as start for a single day</small>
</div>
<div class="col-md-4">
<label class="form-label">Duration</label>
<input type="text" class="form-control bg-light" id="holidayDuration" readonly value="1 day">
</div>
<div class="col-md-4">
<label class="form-label">Type</label>
<select class="form-select" id="holidayType">
<option value="Public">Public</option>
<option value="Institutional">Institutional</option>
<option value="Optional">Optional</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Branch</label>
<select class="form-select" id="holidayBranch">
<option value="">All Branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4 d-flex align-items-end">
<div class="form-check mb-2">
<input type="checkbox" class="form-check-input" id="holidayRecurring" value="1">
<label class="form-check-label" for="holidayRecurring">Recurring annually</label>
</div>
</div>
<div class="col-12">
<label class="form-label">Description</label>
<textarea class="form-control" id="holidayDescription" rows="2" placeholder="Optional notes for staff and HR"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveHolidayBtn"><i class="ri-save-line"></i> Save Holiday</button>
</div>
</div>
</div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewHolidayModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Holiday Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewHolidayBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var holidaysCache = [];
    var holidayModal = null;
    var viewModal = null;

    function calcDuration() {
        var start = document.getElementById('holidayStart').value;
        var end = document.getElementById('holidayEnd').value;
        var el = document.getElementById('holidayDuration');
        if (!start) { el.value = '—'; return; }
        if (!end || end < start) end = start;
        var days = Math.round((new Date(end) - new Date(start)) / 86400000) + 1;
        el.value = days + (days === 1 ? ' day' : ' days');
    }

    function formatPeriod(h) {
        var start = H.formatDate(h.holiday_date);
        var end = H.formatDate(h.end_date_resolved || h.end_date || h.holiday_date);
        return start === end ? start : start + ' → ' + end;
    }

    function typeBadge(t) {
        var cls = t === 'Public' ? 'danger' : (t === 'Institutional' ? 'info' : 'secondary');
        return H.badge(t, cls);
    }

    function buildQuery() {
        var q = ['year=' + document.getElementById('filterYear').value];
        var branch = document.getElementById('filterBranch');
        if (branch && branch.value) q.push('branch_id=' + branch.value);
        var type = document.getElementById('filterType').value;
        if (type) q.push('holiday_type=' + encodeURIComponent(type));
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        return '?' + q.join('&');
    }

    function load() {
        var tb = document.querySelector('#holidaysTable tbody');
        tb.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-holidays.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data;
            holidaysCache = payload.holidays || [];
            var stats = payload.stats || {};
            document.getElementById('statTotal').textContent = stats.total || 0;
            document.getElementById('statDays').textContent = stats.total_days || 0;
            document.getElementById('statPublic').textContent = stats.public || 0;
            document.getElementById('statUpcoming').textContent = stats.upcoming || 0;

            if (!holidaysCache.length) {
                tb.innerHTML = '<tr><td colspan="7" class="text-muted text-center py-4">No holidays for this period</td></tr>';
                return;
            }
            tb.innerHTML = holidaysCache.map(function (h) {
                var days = h.duration_days || 1;
                return '<tr>' +
                    '<td><strong>' + H.escapeHtml(h.holiday_name) + '</strong>' +
                    (h.description ? '<br><small class="text-muted">' + H.escapeHtml(h.description).substring(0, 60) + '</small>' : '') + '</td>' +
                    '<td>' + formatPeriod(h) + '</td>' +
                    '<td><span class="badge bg-light text-dark">' + days + 'd</span></td>' +
                    '<td>' + typeBadge(h.holiday_type) + '</td>' +
                    '<td>' + H.escapeHtml(h.branch_name || 'All Branches') + '</td>' +
                    '<td>' + (h.is_recurring == 1 ? '<i class="ri-check-line text-success"></i> Yes' : 'No') + '</td>' +
                    '<td><div class="btn-group btn-group-sm">' +
                    '<button type="button" class="btn btn-outline-info btn-view" data-id="' + h.id + '"><i class="ri-eye-line"></i></button>' +
                    '<button type="button" class="btn btn-outline-primary btn-edit" data-id="' + h.id + '"><i class="ri-edit-line"></i></button>' +
                    '<button type="button" class="btn btn-outline-danger btn-delete" data-id="' + h.id + '"><i class="ri-delete-bin-line"></i></button>' +
                    '</div></td></tr>';
            }).join('');
        }).catch(function (e) { H.error(e.message || 'Failed to load'); });
    }

    function openForm(data) {
        document.getElementById('holidayForm').reset();
        document.getElementById('holidayId').value = data ? data.id : '';
        document.getElementById('holidayModalTitle').innerHTML = data
            ? '<i class="ri-edit-line"></i> Edit Holiday'
            : '<i class="ri-calendar-event-line"></i> Add Holiday';
        if (data) {
            document.getElementById('holidayName').value = data.holiday_name;
            document.getElementById('holidayStart').value = (data.holiday_date || '').substring(0, 10);
            document.getElementById('holidayEnd').value = (data.end_date_resolved || data.end_date || data.holiday_date || '').substring(0, 10);
            document.getElementById('holidayType').value = data.holiday_type;
            document.getElementById('holidayBranch').value = data.branch_id || '';
            document.getElementById('holidayRecurring').checked = data.is_recurring == 1;
            document.getElementById('holidayDescription').value = data.description || '';
        } else {
            var today = new Date().toISOString().slice(0, 10);
            document.getElementById('holidayStart').value = today;
            document.getElementById('holidayEnd').value = today;
        }
        calcDuration();
        if (!holidayModal) holidayModal = new bootstrap.Modal(document.getElementById('holidayModal'));
        holidayModal.show();
    }

    function viewHoliday(id) {
        var h = holidaysCache.find(function (x) { return String(x.id) === String(id); });
        if (!h) return;
        document.getElementById('viewHolidayBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-12"><strong>Name</strong><br>' + H.escapeHtml(h.holiday_name) + '</div>' +
            '<div class="col-6"><strong>Start</strong><br>' + H.formatDate(h.holiday_date) + '</div>' +
            '<div class="col-6"><strong>End</strong><br>' + H.formatDate(h.end_date_resolved || h.end_date || h.holiday_date) + '</div>' +
            '<div class="col-6"><strong>Duration</strong><br>' + (h.duration_days || 1) + ' day(s)</div>' +
            '<div class="col-6"><strong>Type</strong><br>' + typeBadge(h.holiday_type) + '</div>' +
            '<div class="col-6"><strong>Branch</strong><br>' + H.escapeHtml(h.branch_name || 'All Branches') + '</div>' +
            '<div class="col-6"><strong>Recurring</strong><br>' + (h.is_recurring == 1 ? 'Yes' : 'No') + '</div>' +
            '<div class="col-12"><strong>Description</strong><br>' + H.escapeHtml(h.description || '—') + '</div>' +
            '</div>';
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewHolidayModal'));
        viewModal.show();
    }

    document.getElementById('btnNewHoliday').addEventListener('click', function () { openForm(null); });
    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('filterYear').addEventListener('change', load);
    document.getElementById('holidayStart').addEventListener('change', function () {
        var end = document.getElementById('holidayEnd');
        if (!end.value || end.value < this.value) end.value = this.value;
        calcDuration();
    });
    document.getElementById('holidayEnd').addEventListener('change', calcDuration);

    document.getElementById('saveHolidayBtn').addEventListener('click', function () {
        var start = document.getElementById('holidayStart').value;
        var end = document.getElementById('holidayEnd').value;
        var name = document.getElementById('holidayName').value.trim();
        if (!name || !start) { H.error('Name and start date are required'); return; }
        if (!end) end = start;
        if (end < start) { H.error('End date cannot be before start date'); return; }
        H.post('ajax/hr/save-holiday.php', {
            id: document.getElementById('holidayId').value,
            holiday_name: name,
            start_date: start,
            end_date: end,
            holiday_type: document.getElementById('holidayType').value,
            branch_id: document.getElementById('holidayBranch').value,
            is_recurring: document.getElementById('holidayRecurring').checked ? 1 : 0,
            description: document.getElementById('holidayDescription').value
        }).then(function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('holidayModal')).hide();
                H.success(res.message, load);
            } else H.error(res.message);
        });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        if (btn.classList.contains('btn-view')) viewHoliday(btn.dataset.id);
        if (btn.classList.contains('btn-edit')) {
            var h = holidaysCache.find(function (x) { return String(x.id) === btn.dataset.id; });
            if (h) openForm(h);
        }
        if (btn.classList.contains('btn-delete')) {
            Swal.fire({
                title: 'Delete holiday?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                H.post('ajax/hr/delete-holiday.php', { id: btn.dataset.id })
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
