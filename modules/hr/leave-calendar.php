<?php
/**
 * Leave Calendar — unified leaves, holidays, and team availability
 */
require_once '../../config/config.php';
hrRequireAccess('hr_leave', 'view');

$pageTitle = 'Leave Calendar';
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isStaffRole = hasRole(['Teacher', 'Staff']);

$staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
$myStaffId = (int)($staffRow['id'] ?? 0);

$branches = fetchAll(executeQuery("SELECT id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name"));
$departments = fetchAll(executeQuery(
    "SELECT DISTINCT department FROM staff WHERE department IS NOT NULL AND department != '' ORDER BY department"
));
$staff = fetchAll(executeQuery(
    "SELECT id, staff_id, first_name, last_name FROM staff WHERE status = 'Active' ORDER BY first_name"
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
<a href="<?php echo APP_URL; ?>modules/hr/leaves.php" class="btn btn-outline-primary me-1">
<i class="ri-file-list-line"></i> Leave Applications
</a>
<a href="<?php echo APP_URL; ?>modules/hr/holidays.php" class="btn btn-outline-secondary">
<i class="ri-calendar-event-line"></i> Manage Holidays
</a>
</div>
<h4 class="page-title">Leave Calendar</h4>
<p class="text-muted mb-0 small">Unified view of approved &amp; pending leaves alongside public holidays</p>
</div>
</div>
</div>

<!-- KPI -->
<div class="row" id="calStats">
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
<div class="stat-icon bg-danger-lighten text-danger rounded p-2 me-2"><i class="ri-calendar-event-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Holidays</p><h5 class="mb-0" id="statHolidays">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-user-unfollow-line font-20"></i></div>
<div><p class="text-muted mb-0 small">On Leave Today</p><h5 class="mb-0" id="statToday">—</h5></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-purple-lighten text-purple rounded p-2 me-2"><i class="ri-stack-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Total Events</p><h5 class="mb-0" id="statTotal">—</h5></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<?php if ($isSuperAdmin): ?>
<div class="col-md-2">
<label class="form-label small">Branch</label>
<select id="filterBranch" class="form-select">
<option value="">All Branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<?php if (!$isStaffRole): ?>
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
<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['staff_id'] . ' — ' . $s['first_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-2">
<label class="form-label small d-block">Show</label>
<div class="form-check form-check-inline">
<input type="checkbox" class="form-check-input" id="showLeaves" checked>
<label class="form-check-label" for="showLeaves">Leaves</label>
</div>
<div class="form-check form-check-inline">
<input type="checkbox" class="form-check-input" id="showHolidays" checked>
<label class="form-check-label" for="showHolidays">Holidays</label>
</div>
</div>
<?php if ($myStaffId && !$isStaffRole): ?>
<div class="col-md-2">
<div class="form-check mt-4">
<input type="checkbox" class="form-check-input" id="filterMine">
<label class="form-check-label" for="filterMine">My leaves only</label>
</div>
</div>
<?php endif; ?>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnRefresh"><i class="ri-refresh-line"></i> Refresh</button>
</div>
</div>
<div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
<span class="text-muted small me-1">Legend:</span>
<span class="badge bg-success">Approved Leave</span>
<span class="badge bg-warning text-dark">Pending Leave</span>
<span class="badge bg-info">Manager Approved</span>
<span class="badge bg-danger">Public Holiday</span>
<span class="badge bg-purple">Institutional</span>
<span class="badge bg-orange">Optional Holiday</span>
</div>
</div></div>

<div class="row">
<div class="col-lg-9">
<div class="card">
<div class="card-body">
<div id="leaveCalendar"></div>
</div>
</div>
</div>
<div class="col-lg-3">
<div class="card">
<div class="card-header bg-light py-2">
<h6 class="mb-0"><i class="ri-calendar-schedule-line"></i> Upcoming</h6>
</div>
<div class="card-body p-0" id="upcomingList" style="max-height:520px;overflow-y:auto">
<p class="text-muted text-center py-4 mb-0 small">Loading…</p>
</div>
</div>
</div>
</div>

</div>
</div>
</div>

<style>
#leaveCalendar .fc-event { cursor: pointer; font-size: .78rem; border-radius: 3px; }
#leaveCalendar .fc-toolbar-title { font-size: 1.15rem; font-weight: 600; }
.badge.bg-purple { background-color: #6f42c1 !important; color: #fff; }
.badge.bg-orange { background-color: #fd7e14 !important; color: #fff; }
.upcoming-item { padding: 12px 16px; border-bottom: 1px solid #eef2f7; cursor: pointer; transition: background .15s; }
.upcoming-item:hover { background: #f8fafc; }
.upcoming-item:last-child { border-bottom: none; }
.upcoming-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
.swal-leave-detail { text-align: left; font-size: .9rem; }
.swal-leave-detail dt { color: #64748b; font-weight: 600; margin-top: 8px; }
.swal-leave-detail dd { margin-bottom: 0; margin-left: 0; }
</style>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/fullcalendar/index.global.min.js"></script>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var APP_URL = H.apiUrl();
    var MY_STAFF_ID = <?php echo $myStaffId; ?>;
    var eventsCache = [];
    var calendar = null;

    function buildFilterQuery(start, end) {
        var q = ['start=' + encodeURIComponent(start), 'end=' + encodeURIComponent(end)];
        var br = document.getElementById('filterBranch');
        if (br && br.value) q.push('branch_id=' + br.value);
        var dept = document.getElementById('filterDepartment');
        if (dept && dept.value) q.push('department=' + encodeURIComponent(dept.value));
        var staff = document.getElementById('filterStaff');
        if (staff && staff.value) q.push('staff_id=' + staff.value);
        if (!document.getElementById('showLeaves').checked) q.push('show_leaves=0');
        if (!document.getElementById('showHolidays').checked) q.push('show_holidays=0');
        var mine = document.getElementById('filterMine');
        if (mine && mine.checked) q.push('mine=1');
        return '?' + q.join('&');
    }

    function updateStats(s) {
        s = s || {};
        document.getElementById('statApproved').textContent = s.approved || 0;
        document.getElementById('statPending').textContent = s.pending || 0;
        document.getElementById('statManager').textContent = s.manager_approved || 0;
        document.getElementById('statHolidays').textContent = s.holidays || 0;
        document.getElementById('statToday').textContent = s.on_leave_today || 0;
        document.getElementById('statTotal').textContent = s.total_events || 0;
    }

    function formatDate(d) {
        if (!d) return '—';
        return H.formatDate(d);
    }

    function statusLabel(stage) {
        return String(stage || '').replace(/_/g, ' ');
    }

    function buildDetailHtml(e) {
        if (e.type === 'holiday') {
            return '<dl class="swal-leave-detail">' +
                '<dt><i class="ri-calendar-event-line"></i> Holiday</dt>' +
                '<dd><strong>' + H.escapeHtml(e.title) + '</strong></dd>' +
                '<dt>Period</dt><dd>' + formatDate(e.date_start) +
                (e.date_end && e.date_end !== e.date_start ? ' → ' + formatDate(e.date_end) : '') +
                ' (' + (e.duration_days || 1) + ' day(s))</dd>' +
                '<dt>Type</dt><dd>' + H.escapeHtml(e.status || 'Public') + '</dd>' +
                '<dt>Branch</dt><dd>' + H.escapeHtml(e.branch_name || 'All Branches') + '</dd>' +
                (e.is_recurring ? '<dt>Recurring</dt><dd>Yes — repeats annually</dd>' : '') +
                (e.description ? '<dt>Description</dt><dd>' + H.escapeHtml(e.description) + '</dd>' : '') +
                '</dl>';
        }
        return '<dl class="swal-leave-detail">' +
            '<dt><i class="ri-user-line"></i> Employee</dt>' +
            '<dd><strong>' + H.escapeHtml(e.employee_name || '') + '</strong>' +
            (e.employee_code ? ' <small class="text-muted">(' + H.escapeHtml(e.employee_code) + ')</small>' : '') + '</dd>' +
            (e.department ? '<dt>Department</dt><dd>' + H.escapeHtml(e.department) + '</dd>' : '') +
            '<dt>Leave Type</dt><dd>' + H.escapeHtml(e.leave_name || '') + ' (' + H.escapeHtml(e.leave_code || '') + ')</dd>' +
            '<dt>Period</dt><dd>' + formatDate(e.date_start) + ' → ' + formatDate(e.date_end) +
            ' <strong>(' + (e.total_days || '—') + ' days)</strong></dd>' +
            '<dt>Status</dt><dd>' + H.escapeHtml(statusLabel(e.status)) + '</dd>' +
            (e.branch_name ? '<dt>Branch</dt><dd>' + H.escapeHtml(e.branch_name) + '</dd>' : '') +
            (e.reason ? '<dt>Reason</dt><dd>' + H.escapeHtml(e.reason) + '</dd>' : '') +
            '</dl>';
    }

    function showEventDetail(e) {
        var isLeave = e.type === 'leave';
        var icon = isLeave
            ? (e.status === 'Approved' ? 'success' : (e.status === 'Manager_Approved' ? 'info' : 'warning'))
            : 'error';

        Swal.fire({
            title: isLeave ? 'Leave Application' : 'Public Holiday',
            html: buildDetailHtml(e),
            icon: icon,
            width: 520,
            showCancelButton: isLeave && e.leave_id,
            confirmButtonText: isLeave && e.leave_id ? '<i class="ri-eye-line"></i> View Application' : 'Close',
            cancelButtonText: 'Close',
            confirmButtonColor: isLeave ? '#1a56db' : '#6c757d',
            customClass: { popup: 'text-start' }
        }).then(function (result) {
            if (result.isConfirmed && isLeave && e.leave_id) {
                window.open(APP_URL + 'modules/hr/view-leave.php?id=' + e.leave_id, '_blank');
            }
        });
    }

    function renderUpcoming(list) {
        var el = document.getElementById('upcomingList');
        if (!list || !list.length) {
            el.innerHTML = '<p class="text-muted text-center py-4 mb-0 small">No upcoming events in this period</p>';
            return;
        }
        el.innerHTML = list.map(function (e, idx) {
            return '<div class="upcoming-item" data-idx="' + idx + '">' +
                '<div class="d-flex align-items-start">' +
                '<span class="upcoming-dot mt-1" style="background:' + (e.color || '#6c757d') + '"></span>' +
                '<div class="flex-grow-1">' +
                '<div class="fw-semibold small">' + H.escapeHtml(e.title) + '</div>' +
                '<div class="text-muted" style="font-size:.75rem">' + formatDate(e.date_start || e.start) +
                (e.date_end && e.date_end !== e.date_start ? ' — ' + formatDate(e.date_end) : '') + '</div>' +
                '<span class="badge bg-light text-dark mt-1" style="font-size:.65rem">' +
                (e.type === 'holiday' ? 'Holiday' : statusLabel(e.status)) + '</span>' +
                '</div></div></div>';
        }).join('');

        el.querySelectorAll('.upcoming-item').forEach(function (item) {
            item.addEventListener('click', function () {
                var e = list[parseInt(item.dataset.idx, 10)];
                if (e) showEventDetail(e);
            });
        });
    }

    function mapEvents(data) {
        eventsCache = data || [];
        return eventsCache.map(function (e) {
            var props = Object.assign({}, e);
            delete props.id;
            delete props.title;
            delete props.start;
            delete props.end;
            delete props.color;
            return {
                id: e.id,
                title: e.title,
                start: e.start,
                end: e.end,
                backgroundColor: e.color,
                borderColor: e.color,
                extendedProps: props
            };
        });
    }

    function fetchEvents(info, successCallback, failureCallback) {
        H.get('ajax/hr/get-leave-calendar.php' + buildFilterQuery(
            info.startStr.split('T')[0],
            info.endStr.split('T')[0]
        )).then(function (res) {
            if (!res.success) {
                H.error(res.message);
                failureCallback();
                return;
            }
            var payload = res.data || {};
            updateStats(payload.stats);
            renderUpcoming(payload.upcoming);
            successCallback(mapEvents(payload.events));
        }).catch(function () { failureCallback(); });
    }

    function initCalendar() {
        var calendarEl = document.getElementById('leaveCalendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            height: 'auto',
            firstDay: 0,
            eventDisplay: 'block',
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            events: fetchEvents,
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                var e = Object.assign({ title: info.event.title }, info.event.extendedProps);
                showEventDetail(e);
            },
            datesSet: function () {
                /* stats refresh via events fetch */
            }
        });
        calendar.render();
    }

    function refreshCalendar() {
        if (calendar) calendar.refetchEvents();
    }

    document.getElementById('btnRefresh').addEventListener('click', refreshCalendar);
    ['filterBranch', 'filterDepartment', 'filterStaff', 'filterMine'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', refreshCalendar);
    });
    document.getElementById('showLeaves').addEventListener('change', refreshCalendar);
    document.getElementById('showHolidays').addEventListener('change', refreshCalendar);

    initCalendar();
})();
</script>
