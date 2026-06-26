<?php
/**
 * HR Analytics Dashboard
 */

require_once '../../config/config.php';

hrRequirePage('hr_attendance', 'view', ['Accountant']);

$pageTitle = 'HR Dashboard';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $currentUser['branch_id'] ?? null;

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">HR & Payroll Dashboard</h4>
                    </div>
                </div>
            </div>

            <div class="row" id="hr-kpi-cards">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <h5 class="text-muted">Active Staff</h5>
                            <h2 class="mb-0" id="kpi-active-staff">—</h2>
                            <small class="text-muted"><span id="kpi-total-staff">0</span> total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <h5 class="text-muted">Attendance Today</h5>
                            <h2 class="mb-0"><span id="kpi-attendance-rate">0</span>%</h2>
                            <small class="text-muted"><span id="kpi-late-today">0</span> late</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <h5 class="text-muted">Pending Leaves</h5>
                            <h2 class="mb-0" id="kpi-pending-leaves">—</h2>
                            <small class="text-muted"><span id="kpi-pending-corrections">0</span> attendance corrections</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <h5 class="text-muted">Payroll MTD</h5>
                            <h2 class="mb-0"><?php echo CURRENCY_SYMBOL; ?><span id="kpi-payroll-mtd">0</span></h2>
                            <small class="text-muted"><span id="kpi-pending-payments">0</span> pending payments</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="header-title">7-Day Attendance Trend</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceTrendChart" height="120"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="header-title">Staff by Department</h4>
                        </div>
                        <div class="card-body">
                            <div id="department-list"><p class="text-muted">Loading...</p></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="header-title mb-0">Recent HR Activity</h4>
                            <div>
                                <a href="<?php echo APP_URL; ?>modules/hr/staff.php" class="btn btn-sm btn-outline-primary">Staff</a>
                                <a href="<?php echo APP_URL; ?>modules/hr/payroll.php" class="btn btn-sm btn-outline-primary">Payroll</a>
                                <a href="<?php echo APP_URL; ?>modules/hr/leaves.php" class="btn btn-sm btn-outline-primary">Leaves</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Module</th>
                                            <th>Description</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-activity-body">
                                        <tr><td colspan="5" class="text-muted">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const APP_URL = '<?php echo APP_URL; ?>';
let trendChart = null;

function loadHrDashboard() {
    fetch(APP_URL + 'ajax/hr/get-hr-dashboard.php')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const k = res.data.kpis;
            document.getElementById('kpi-active-staff').textContent = k.active_staff;
            document.getElementById('kpi-total-staff').textContent = k.total_staff;
            document.getElementById('kpi-attendance-rate').textContent = k.attendance_rate;
            document.getElementById('kpi-late-today').textContent = k.late_today;
            document.getElementById('kpi-pending-leaves').textContent = k.pending_leaves;
            document.getElementById('kpi-pending-corrections').textContent = k.pending_corrections;
            document.getElementById('kpi-payroll-mtd').textContent = Number(k.payroll_mtd).toLocaleString();
            document.getElementById('kpi-pending-payments').textContent = k.pending_payments;

            const deptHtml = res.data.departments.map(d =>
                `<div class="d-flex justify-content-between mb-2">
                    <span>${d.department}</span>
                    <span class="badge bg-primary">${d.count}</span>
                </div>`
            ).join('') || '<p class="text-muted">No data</p>';
            document.getElementById('department-list').innerHTML = deptHtml;

            renderTrendChart(res.data.attendance_trend);

            const actHtml = res.data.recent_activity.map(a =>
                `<tr>
                    <td>${a.username || 'System'}</td>
                    <td>${a.action}</td>
                    <td>${a.module}</td>
                    <td>${a.description || ''}</td>
                    <td>${a.created_at}</td>
                </tr>`
            ).join('') || '<tr><td colspan="5" class="text-muted">No recent activity</td></tr>';
            document.getElementById('recent-activity-body').innerHTML = actHtml;
        });
}

function renderTrendChart(trend) {
    const labels = trend.map(t => t.attendance_date);
    const present = trend.map(t => parseInt(t.present_count));
    const absent = trend.map(t => parseInt(t.absent_count));
    const ctx = document.getElementById('attendanceTrendChart');
    if (trendChart) trendChart.destroy();
    trendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Present/Late', data: present, backgroundColor: '#28a745' },
                { label: 'Absent', data: absent, backgroundColor: '#dc3545' }
            ]
        },
        options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
    });
}

document.addEventListener('DOMContentLoaded', loadHrDashboard);
</script>

<?php include '../../includes/footer.php'; ?>
