<?php
/**
 * HR Reports & Analytics
 */
require_once '../../config/config.php';
hrRequirePage('hr_reports', 'view', ['Accountant']);

$pageTitle = 'HR Reports';
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$canExport = hasRole(['Super Admin', 'Admin', 'Accountant'])
    || (function_exists('canPerform') && canPerform('hr_reports', 'export'));

$branches = fetchAll(executeQuery("SELECT id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name"));
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
<?php if ($canExport): ?>
<div class="btn-group">
<button type="button" class="btn btn-outline-danger" id="btnExportPdf" title="Download PDF">
<i class="ri-file-pdf-line"></i> Export PDF
</button>
<button type="button" class="btn btn-outline-success" id="btnExportCsv">
<i class="ri-file-excel-line"></i> Export CSV
</button>
<button type="button" class="btn btn-outline-secondary" id="btnPrint">
<i class="ri-printer-line"></i> Print
</button>
</div>
<?php endif; ?>
</div>
<h4 class="page-title">HR Reports &amp; Analytics</h4>
<p class="text-muted mb-0 small">Workforce, payroll, attendance, and operations — export to professionally designed PDF</p>
</div>
</div>
</div>

<!-- Live KPI strip -->
<div class="row" id="kpiStrip">
<div class="col-md-2"><div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-team-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Active Staff</p><h5 class="mb-0" id="kpiStaff">—</h5></div>
</div></div></div></div>
<div class="col-md-2"><div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-money-dollar-circle-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Payroll MTD</p><h5 class="mb-0" id="kpiPayroll">—</h5></div>
</div></div></div></div>
<div class="col-md-2"><div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-calendar-todo-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Pending Leaves</p><h5 class="mb-0" id="kpiLeaves">—</h5></div>
</div></div></div></div>
<div class="col-md-2"><div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-danger-lighten text-danger rounded p-2 me-2"><i class="ri-feedback-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Open Grievances</p><h5 class="mb-0" id="kpiGrievances">—</h5></div>
</div></div></div></div>
<div class="col-md-2"><div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-checkbox-circle-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Avg Attendance</p><h5 class="mb-0" id="kpiAttendance">—</h5></div>
</div></div></div></div>
<div class="col-md-2"><div class="card widget-stat-card"><div class="card-body py-3">
<div class="d-flex align-items-center">
<div class="stat-icon bg-purple-lighten text-purple rounded p-2 me-2"><i class="ri-briefcase-line font-20"></i></div>
<div><p class="text-muted mb-0 small">Open Vacancies</p><h5 class="mb-0" id="kpiVacancies">—</h5></div>
</div></div></div></div>
</div>

<div class="row">
<!-- Report catalog -->
<div class="col-lg-3">
<div class="card">
<div class="card-header bg-light py-2"><h6 class="mb-0"><i class="ri-folder-chart-line"></i> Report Catalog</h6></div>
<div class="card-body p-2" id="reportCatalog" style="max-height:520px;overflow-y:auto"></div>
</div>
</div>

<!-- Filters + output -->
<div class="col-lg-9">
<div class="card mb-3">
<div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-3" id="filterMonthWrap">
<label class="form-label small">Period (Month)</label>
<input type="month" id="filterMonth" class="form-control" value="<?php echo date('Y-m'); ?>">
</div>
<div class="col-md-2" id="filterYearWrap" style="display:none">
<label class="form-label small">Year</label>
<select id="filterYear" class="form-select">
<?php for ($y = $year - 2; $y <= $year + 1; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
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
<div class="col-md-2">
<label class="form-label small">Department</label>
<select id="filterDepartment" class="form-select">
<option value="">All</option>
<?php foreach ($departments as $d): ?>
<option value="<?php echo htmlspecialchars($d['department']); ?>"><?php echo htmlspecialchars($d['department']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-2" id="filterStatusWrap" style="display:none">
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select"><option value="">All</option></select>
</div>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnLoad"><i class="ri-refresh-line"></i> Generate</button>
</div>
</div>
</div>
</div>

<div class="card" id="reportCard">
<div class="card-header d-flex justify-content-between align-items-center bg-light">
<div>
<h5 class="mb-0" id="reportTitle">Executive Summary</h5>
<small class="text-muted" id="reportSubtitle">—</small>
</div>
<span class="badge bg-primary" id="reportTypeBadge">summary</span>
</div>
<div class="card-body" id="reportOutput">
<p class="text-muted text-center py-5">Select a report from the catalog and click Generate.</p>
</div>
</div>
</div>
</div>

</div>
</div>
</div>

<!-- Print-only area (hidden on screen) -->
<div id="printArea" class="d-none"></div>

<style>
.report-kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:20px}
.report-kpi{border:1px solid #e2e8f0;border-radius:8px;padding:14px;text-align:center;background:linear-gradient(180deg,#f8fafc,#fff)}
.report-kpi .val{font-size:1.5rem;font-weight:700;color:#1a56db}
.report-kpi .lbl{font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.03em}
.report-catalog-group{font-size:.7rem;font-weight:600;text-transform:uppercase;color:#94a3b8;padding:8px 10px 4px;margin:0}
.report-item{display:block;width:100%;text-align:left;border:1px solid transparent;border-radius:6px;padding:8px 10px;margin-bottom:2px;background:transparent;cursor:pointer}
.report-item:hover{background:#f1f5f9}
.report-item.active{background:#eff6ff;border-color:#93c5fd}
.report-item .ri{font-size:1.1rem;vertical-align:-2px;margin-right:6px;color:#1a56db}
.report-item small{display:block;color:#64748b;font-size:.72rem;margin-left:22px}
.bar-cell{min-width:120px}
.bar-track{height:6px;background:#e2e8f0;border-radius:3px;margin-top:4px;overflow:hidden}
.bar-fill{height:100%;background:linear-gradient(90deg,#1a56db,#3b82f6);border-radius:3px}
@media print{
  .content-page,.report-preview-print{padding:0}
  .no-print{display:none!important}
  #printArea{display:block!important}
}
</style>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var CAN_EXPORT = <?php echo $canExport ? 'true' : 'false'; ?>;
    var CURRENCY = <?php echo json_encode(CURRENCY_SYMBOL); ?>;
    var currentType = 'summary';
    var lastReport = null;

    var YEAR_REPORTS = ['leave_balance', 'performance', 'ppdp'];
    var STATUS_OPTIONS = {
        employee_master: ['Active', 'Inactive', 'Resigned', 'Terminated'],
        payroll: ['Draft', 'Pending_Approval', 'Approved', 'Paid', 'Cancelled'],
        advances: ['Pending', 'Approved', 'Rejected', 'Disbursed', 'Fully_Recovered'],
        grievances: ['Submitted', 'Under_Review', 'Investigating', 'Escalated', 'Resolved', 'Closed'],
        performance: ['Draft', 'Submitted', 'Acknowledged', 'Archived']
    };

    var CATALOG = [
        { group: 'Workforce', items: [
            { id: 'summary', name: 'Executive Summary', icon: 'ri-dashboard-3-line', desc: 'KPI dashboard snapshot' },
            { id: 'headcount', name: 'Headcount by Dept', icon: 'ri-pie-chart-line', desc: 'Active staff distribution' },
            { id: 'employee_master', name: 'Employee Master', icon: 'ri-contacts-line', desc: 'Full employee listing' },
            { id: 'department', name: 'Department Overview', icon: 'ri-building-line', desc: 'Headcount, attendance & payroll' }
        ]},
        { group: 'Payroll & Finance', items: [
            { id: 'payroll', name: 'Payroll Register', icon: 'ri-money-dollar-box-line', desc: 'Monthly salary payments' },
            { id: 'advances', name: 'Salary Advances', icon: 'ri-hand-coin-line', desc: 'Outstanding advances' }
        ]},
        { group: 'Attendance & Leave', items: [
            { id: 'attendance', name: 'Attendance Summary', icon: 'ri-calendar-check-line', desc: 'Monthly attendance rates' },
            { id: 'attendance_late', name: 'Late Arrivals', icon: 'ri-time-line', desc: 'Chronic lateness tracking' },
            { id: 'leave', name: 'Leave Utilization', icon: 'ri-calendar-event-line', desc: 'Approved leave by type' },
            { id: 'leave_balance', name: 'Leave Balances', icon: 'ri-stack-line', desc: 'Remaining leave days' }
        ]},
        { group: 'HR Operations', items: [
            { id: 'grievances', name: 'Grievance Summary', icon: 'ri-feedback-line', desc: 'Cases by category & status' },
            { id: 'performance', name: 'Performance Reviews', icon: 'ri-star-line', desc: 'Appraisal summary' },
            { id: 'ppdp', name: 'PPDP Completion', icon: 'ri-graduation-cap-line', desc: 'Training program stats' },
            { id: 'recruitment', name: 'Recruitment Pipeline', icon: 'ri-user-search-line', desc: 'Vacancy funnel analysis' }
        ]}
    ];

    function renderCatalog() {
        var html = '';
        CATALOG.forEach(function (g) {
            html += '<div class="report-catalog-group">' + H.escapeHtml(g.group) + '</div>';
            g.items.forEach(function (item) {
                var active = item.id === currentType ? ' active' : '';
                html += '<button type="button" class="report-item' + active + '" data-type="' + item.id + '">' +
                    '<i class="' + item.icon + '"></i> ' + H.escapeHtml(item.name) +
                    '<small>' + H.escapeHtml(item.desc) + '</small></button>';
            });
        });
        document.getElementById('reportCatalog').innerHTML = html;
    }

    function toggleFilters() {
        var isYear = YEAR_REPORTS.indexOf(currentType) >= 0;
        document.getElementById('filterMonthWrap').style.display = isYear ? 'none' : '';
        document.getElementById('filterYearWrap').style.display = isYear ? '' : 'none';

        var statusWrap = document.getElementById('filterStatusWrap');
        var opts = STATUS_OPTIONS[currentType];
        if (opts) {
            statusWrap.style.display = '';
            document.getElementById('filterStatus').innerHTML = '<option value="">All</option>' +
                opts.map(function (s) { return '<option value="' + s + '">' + s.replace(/_/g, ' ') + '</option>'; }).join('');
        } else {
            statusWrap.style.display = 'none';
        }
    }

    function buildQuery() {
        var q = ['type=' + encodeURIComponent(currentType)];
        if (YEAR_REPORTS.indexOf(currentType) >= 0) {
            q.push('year=' + document.getElementById('filterYear').value);
        } else {
            q.push('month=' + document.getElementById('filterMonth').value);
        }
        var dept = document.getElementById('filterDepartment').value;
        if (dept) q.push('department=' + encodeURIComponent(dept));
        var br = document.getElementById('filterBranch');
        if (br && br.value) q.push('branch_id=' + br.value);
        var st = document.getElementById('filterStatus');
        if (st && document.getElementById('filterStatusWrap').style.display !== 'none' && st.value) {
            q.push('status=' + encodeURIComponent(st.value));
        }
        return '?' + q.join('&');
    }

    function formatMoney(v) {
        if (v == null || v === '') return '—';
        return CURRENCY + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatCell(val, col) {
        if (val == null || val === '') return '<span class="text-muted">—</span>';
        if (col.format === 'money') return formatMoney(val);
        return H.escapeHtml(String(val));
    }

    function updateKpis(kpis) {
        if (!kpis || !kpis.length) return;
        var map = {};
        kpis.forEach(function (k) { map[k.label] = k.value; });
        document.getElementById('kpiStaff').textContent = map['Active Staff'] || '—';
        document.getElementById('kpiPayroll').textContent = map['Payroll MTD'] || '—';
        document.getElementById('kpiLeaves').textContent = map['Pending Leaves'] || '—';
        document.getElementById('kpiGrievances').textContent = map['Open Grievances'] || '—';
        document.getElementById('kpiAttendance').textContent = map['Avg Attendance %'] || '—';
        document.getElementById('kpiVacancies').textContent = map['Open Vacancies'] || '—';
    }

    function renderDashboard(report) {
        var html = '<div class="report-kpi-grid">';
        (report.kpis || []).forEach(function (k) {
            html += '<div class="report-kpi"><div class="val">' + H.escapeHtml(String(k.value)) + '</div>' +
                '<div class="lbl">' + H.escapeHtml(k.label) + '</div></div>';
        });
        html += '</div>';
        if (report.summary && report.summary.outstanding_advances > 0) {
            html += '<div class="alert alert-light border"><i class="ri-hand-coin-line text-warning"></i> ' +
                '<strong>Outstanding Advances:</strong> ' + formatMoney(report.summary.outstanding_advances) + '</div>';
        }
        return html;
    }

    function renderTable(report) {
        if (!report.rows || !report.rows.length) {
            return '<p class="text-muted text-center py-4">No data for the selected filters.</p>';
        }
        var maxBar = 0;
        if (report.type === 'headcount') {
            maxBar = Math.max.apply(null, report.rows.map(function (r) { return Number(r.headcount) || 0; }).concat([1]));
        }

        var html = '<div class="table-responsive"><table class="table table-hover align-middle table-sm">';
        html += '<thead class="table-light"><tr>';
        report.columns.forEach(function (c) {
            var cls = c.align === 'right' ? ' class="text-end"' : '';
            html += '<th' + cls + '>' + H.escapeHtml(c.label) + '</th>';
        });
        html += '</tr></thead><tbody>';

        report.rows.forEach(function (row) {
            html += '<tr>';
            report.columns.forEach(function (c) {
                var cls = c.align === 'right' ? ' class="text-end"' : '';
                var cell = formatCell(row[c.key], c);
                if (c.key === 'headcount' && maxBar > 0) {
                    var pct = Math.round((Number(row.headcount) / maxBar) * 100);
                    cell += '<div class="bar-track"><div class="bar-fill" style="width:' + pct + '%"></div></div>';
                }
                if (c.key === 'attendance_pct' || (c.key === 'share_pct')) {
                    /* already formatted */
                }
                html += '<td' + cls + '>' + cell + '</td>';
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';

        if (report.summary && Object.keys(report.summary).length) {
            html += '<div class="mt-3 p-3 bg-light rounded small"><strong>Summary:</strong> ';
            var parts = [];
            Object.keys(report.summary).forEach(function (k) {
                var v = report.summary[k];
                if (v == null) return;
                var label = k.replace(/_/g, ' ');
                if (k.indexOf('total') >= 0 && typeof v === 'number' && k.indexOf('count') < 0 && k !== 'total_days') {
                    parts.push(label + ': ' + formatMoney(v));
                } else {
                    parts.push(label + ': ' + v);
                }
            });
            html += parts.join(' &nbsp;·&nbsp; ');
            html += '</div>';
        }
        return html;
    }

    function renderReport(report) {
        document.getElementById('reportTitle').textContent = report.title || 'Report';
        document.getElementById('reportSubtitle').textContent = report.subtitle || '';
        document.getElementById('reportTypeBadge').textContent = report.type || currentType;

        if (report.format === 'dashboard') {
            updateKpis(report.kpis);
            return renderDashboard(report);
        }
        return renderTable(report);
    }

    function loadReport() {
        var out = document.getElementById('reportOutput');
        out.innerHTML = '<p class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm"></span> Generating report…</p>';

        H.get('ajax/hr/get-hr-reports.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); out.innerHTML = '<p class="text-danger text-center">Failed to load report</p>'; return; }
            lastReport = res.data;
            out.innerHTML = renderReport(lastReport);
            if (lastReport.format === 'dashboard') updateKpis(lastReport.kpis);
        });
    }

    function exportUrl(format) {
        return H.apiUrl() + 'ajax/hr/export-hr-report.php' + buildQuery() + '&format=' + format;
    }

    function loadSummaryKpis() {
        H.get('ajax/hr/get-hr-reports.php?type=summary&month=' + document.getElementById('filterMonth').value).then(function (res) {
            if (res.success && res.data && res.data.kpis) updateKpis(res.data.kpis);
        });
    }

    document.getElementById('reportCatalog').addEventListener('click', function (e) {
        var btn = e.target.closest('[data-type]');
        if (!btn) return;
        currentType = btn.dataset.type;
        renderCatalog();
        toggleFilters();
        loadReport();
    });

    document.getElementById('btnLoad').addEventListener('click', loadReport);
    document.getElementById('filterMonth').addEventListener('change', function () {
        if (currentType === 'summary') loadSummaryKpis();
    });

    if (CAN_EXPORT) {
        document.getElementById('btnExportPdf').addEventListener('click', function () {
            if (!lastReport) { H.error('Generate a report first'); return; }
            window.open(exportUrl('pdf'), '_blank');
        });
        document.getElementById('btnExportCsv').addEventListener('click', function () {
            if (!lastReport) { H.error('Generate a report first'); return; }
            window.location.href = exportUrl('csv');
        });
        document.getElementById('btnPrint').addEventListener('click', function () {
            if (!lastReport) { H.error('Generate a report first'); return; }
            window.open(exportUrl('pdf'), '_blank');
        });
    }

    renderCatalog();
    toggleFilters();
    loadSummaryKpis();
    loadReport();
})();
</script>
