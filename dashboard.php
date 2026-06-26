<?php
/**
 * Comprehensive Admin Dashboard
 * 
 * Expert-level dashboard with KPIs, metrics, insights, and real-time updates
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once 'config/config.php';

requireLogin();

$pageTitle = 'Admin Dashboard';

// Get current user
$currentUser = getCurrentUser();
$roleName = $currentUser['role_name'];
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);

// Redirect students to student dashboard
if (hasRole(['Student']) && !$isSuperAdmin) {
    redirect(APP_URL . 'modules/student/dashboard.php');
}

// Redirect teachers to teacher dashboard
if (hasRole(['Teacher']) && !$isSuperAdmin && !$isAdmin) {
    redirect(APP_URL . 'modules/teacher/dashboard.php');
}

// Get branch filter
$branchId = null;
if ($isSuperAdmin) {
    $branchId = $_GET['branch_id'] ?? null;
} else {
    $branchId = $currentUser['branch_id'] ?? null;
}

// Get current session
$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? null;

// Get comprehensive dashboard statistics
$dashboardStats = [];
if ($isSuperAdmin || $isAdmin) {
    $dashboardStats = getDashboardStats($branchId, $sessionId, true);
    
    // Get additional trend data (not cached)
    $dashboardStats['fee_trend'] = getFeeCollectionTrend($branchId);
    $dashboardStats['attendance_trend'] = getAttendanceTrend($branchId);
    $dashboardStats['top_classes'] = getTopPerformingClasses(5, $branchId, $sessionId);
    
    // Get chart data for new visualizations
    $dashboardStats['student_status_dist'] = getStudentStatusDistribution($branchId);
    $dashboardStats['fee_status_dist'] = getFeeStatusDistribution($branchId);
    $dashboardStats['class_wise_dist'] = getClassWiseDistribution($branchId, 10);
    $dashboardStats['staff_dist'] = getStaffDistribution($branchId);
    $dashboardStats['revenue_vs_outstanding'] = getRevenueVsOutstanding($branchId);
    $dashboardStats['exam_performance'] = getExamPerformanceByClass($branchId, $sessionId, 8);
    $dashboardStats['performance_metrics'] = getOverallPerformanceMetrics($branchId, $sessionId);
}

// Get branches for filter (Super Admin only)
$branches = [];
if ($isSuperAdmin) {
    $branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
    $branches = fetchAll(executeQuery($branchesSql));
}

// Get recent activities
$recentActivities = getDashboardActivities(10, $branchId);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <?php if ($isSuperAdmin && !empty($branches)): ?>
                            <form class="d-inline-block me-2">
                                <select class="form-select form-select-sm" id="branchFilter" onchange="filterByBranch(this.value)">
                                    <option value="">All Branches</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($branch['branch_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="refreshDashboard()" title="Refresh Data">
                                <i class="ri-refresh-line"></i> Refresh
                            </button>
                        </div>
                        <h4 class="page-title">
                            <i class="ri-dashboard-3-line"></i> Admin Dashboard
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Welcome Banner -->
            <div class="row">
                <div class="col-12">
                    <div class="card text-white" style="background-color: #262261;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="mb-1">Welcome back, <?php echo htmlspecialchars($currentUser['username']); ?>!</h3>
                                    <p class="mb-0 opacity-75">
                                        <i class="ri-calendar-line"></i> <?php echo date('l, F j, Y'); ?> | 
                                        <i class="ri-user-line"></i> <?php echo htmlspecialchars($roleName); ?>
                                        <?php if ($currentSession): ?>
                                        | <i class="ri-book-open-line"></i> <?php echo htmlspecialchars($currentSession['session_name']); ?>
                                        <?php endif; ?>
                                        <?php if ($branchId && isset($branches)): ?>
                                            <?php foreach ($branches as $b): ?>
                                                <?php if ($b['id'] == $branchId): ?>
                                                | <i class="ri-building-line"></i> <?php echo htmlspecialchars($b['branch_name']); ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-light btn-sm" onclick="exportDashboard()">
                                            <i class="ri-download-line"></i> Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isSuperAdmin || $isAdmin): ?>
            
            <!-- Key Metrics Row 1: Core KPIs -->
            <div class="row" id="kpiRow1">
                <!-- Total Students -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-user-3-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Students</h5>
                                    <h2 class="mb-0" id="stat-total-students"><?php echo number_format($dashboardStats['students']['total'] ?? 0); ?></h2>
                                    <small class="text-muted">
                                        <span class="text-success"><?php echo number_format($dashboardStats['students']['active'] ?? 0); ?> Active</span> | 
                                        <span class="text-info"><?php echo number_format($dashboardStats['students']['graduated'] ?? 0); ?> Graduated</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/students/list.php" class="text-primary">
                                View All <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Staff -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-user-settings-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Staff</h5>
                                    <h2 class="mb-0" id="stat-total-staff"><?php echo number_format($dashboardStats['staff']['total'] ?? 0); ?></h2>
                                    <small class="text-muted">
                                        <span class="text-success"><?php echo number_format($dashboardStats['staff']['teachers'] ?? 0); ?> Teachers</span> | 
                                        <span class="text-info"><?php echo number_format($dashboardStats['staff']['staff'] ?? 0); ?> Staff</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/hr/staff.php" class="text-success">
                                View All <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active Classes -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-book-open-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Active Classes</h5>
                                    <h2 class="mb-0" id="stat-active-classes"><?php echo number_format($dashboardStats['classes']['active'] ?? 0); ?></h2>
                                    <small class="text-muted">
                                        <span class="text-info"><?php echo number_format($dashboardStats['classes']['total'] ?? 0); ?> Total</span> | 
                                        <span class="text-warning"><?php echo number_format($dashboardStats['classes']['graduated'] ?? 0); ?> Graduated</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/academics/classes.php" class="text-info">
                                View All <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Today's Attendance -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-calendar-check-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Today's Attendance</h5>
                                    <h2 class="mb-0" id="stat-attendance-today">
                                        <?php echo number_format($dashboardStats['attendance_today']['percentage'] ?? 0); ?>%
                                    </h2>
                                    <small class="text-muted">
                                        <span class="text-success"><?php echo number_format($dashboardStats['attendance_today']['present'] ?? 0); ?> Present</span> | 
                                        <span class="text-danger"><?php echo number_format($dashboardStats['attendance_today']['absent'] ?? 0); ?> Absent</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/attendance/list.php" class="text-warning">
                                View Details <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics Row 2: Financial KPIs -->
            <div class="row" id="kpiRow2">
                <!-- Monthly Revenue -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-money-dollar-circle-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Monthly Revenue</h5>
                                    <h2 class="mb-0" id="stat-monthly-revenue"><?php echo formatCurrency($dashboardStats['revenue_month'] ?? 0); ?></h2>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/fees/payments.php" class="text-success">
                                View Payments <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Fees -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-file-warning-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Outstanding Fees</h5>
                                    <h2 class="mb-0" id="stat-outstanding-fees"><?php echo formatCurrency($dashboardStats['outstanding_fees'] ?? 0); ?></h2>
                                    <small class="text-muted">Unpaid & Overdue</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/fees/defaulters.php" class="text-danger">
                                View Defaulters <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Fee Collection Rate -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-pie-chart-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Collection Rate</h5>
                                    <h2 class="mb-0" id="stat-collection-rate">
                                        <?php 
                                        $totalFees = ($dashboardStats['fees']['paid'] ?? 0) + ($dashboardStats['fees']['unpaid'] ?? 0);
                                        $collectionRate = $totalFees > 0 ? round((($dashboardStats['fees']['paid'] ?? 0) / $totalFees) * 100, 1) : 0;
                                        echo $collectionRate . '%';
                                        ?>
                                    </h2>
                                    <small class="text-muted">
                                        <?php echo formatCurrency($dashboardStats['fees']['paid'] ?? 0); ?> / <?php echo formatCurrency($totalFees); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/fees/reports.php" class="text-info">
                                View Reports <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Discounts Given -->
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-price-tag-3-line font-28"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Discounts (Month)</h5>
                                    <h2 class="mb-0" id="stat-discounts"><?php echo formatCurrency($dashboardStats['discounts_month'] ?? 0); ?></h2>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="<?php echo APP_URL; ?>modules/fees/invoices.php" class="text-warning">
                                View Invoices <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Insights Row -->
            <div class="row">
                <!-- Classes in Session -->
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="ri-book-open-line text-primary"></i> Classes in Session
                            </h5>
                            <div class="text-center">
                                <h2 class="text-primary mb-1" id="stat-classes-session"><?php echo number_format($dashboardStats['active_classes'] ?? 0); ?></h2>
                                <p class="text-muted mb-0">Active Classes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects Taught Today -->
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="ri-book-2-line text-success"></i> Subjects Today
                            </h5>
                            <div class="text-center">
                                <h2 class="text-success mb-1" id="stat-subjects-today"><?php echo number_format($dashboardStats['subjects_today'] ?? 0); ?></h2>
                                <p class="text-muted mb-0">Scheduled Subjects</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Completion -->
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="ri-calendar-check-line text-info"></i> Attendance Status
                            </h5>
                            <div class="text-center">
                                <h2 class="text-info mb-1" id="stat-attendance-completion">
                                    <?php echo number_format($dashboardStats['attendance_completion']['percentage'] ?? 0); ?>%
                                </h2>
                                <p class="text-muted mb-0">
                                    <?php echo number_format($dashboardStats['attendance_completion']['completed'] ?? 0); ?> / 
                                    <?php echo number_format($dashboardStats['attendance_completion']['total_classes'] ?? 0); ?> Classes
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Progress -->
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="ri-file-list-3-line text-warning"></i> Exam Progress
                            </h5>
                            <div class="text-center">
                                <h2 class="text-warning mb-1" id="stat-exams-total"><?php echo number_format($dashboardStats['exams']['total'] ?? 0); ?></h2>
                                <p class="text-muted mb-0">
                                    <span class="text-success"><?php echo number_format($dashboardStats['exams']['completed'] ?? 0); ?> Done</span> | 
                                    <span class="text-info"><?php echo number_format($dashboardStats['exams']['ongoing'] ?? 0); ?> Ongoing</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Visualizations Row 1 -->
            <div class="row">
                <!-- Fee Collection Trend -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-line-chart-line"></i> Fee Collection Trend (Last 6 Months)
                            </h4>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="feeTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Trend -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-bar-chart-line"></i> Attendance Trend (Last 30 Days)
                            </h4>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="attendanceTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Visualizations Row 2: Distribution Charts -->
            <div class="row">
                <!-- Student Status Distribution -->
                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-pie-chart-line"></i> Student Status Distribution
                            </h4>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="studentStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Payment Status -->
                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-donut-chart-line"></i> Fee Payment Status
                            </h4>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="feeStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Distribution -->
                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-pie-chart-2-line"></i> Staff Distribution by Type
                            </h4>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="staffDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Visualizations Row 3: Performance Charts -->
            <div class="row">
                <!-- Revenue vs Outstanding Fees -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-stack-line"></i> Revenue vs Outstanding Fees (Last 6 Months)
                            </h4>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="revenueVsOutstandingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Performance Metrics (Radar) -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-radar-line"></i> Overall Performance Metrics
                            </h4>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="performanceRadarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Visualizations Row 4: Class Performance -->
            <div class="row">
                <!-- Class-wise Student Distribution -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-bar-chart-horizontal-line"></i> Top Classes by Student Count
                            </h4>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="classWiseDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Performance by Class -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-bar-chart-box-line"></i> Exam Performance by Class
                            </h4>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="examPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administrative Alerts and Notifications -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-alert-line text-warning"></i> Administrative Alerts
                            </h4>
                            <div class="list-group list-group-flush" id="adminAlerts">
                                <!-- Pending Admissions -->
                                <?php if (($dashboardStats['pending_admissions'] ?? 0) > 0): ?>
                                <a href="<?php echo APP_URL; ?>modules/admissions/list.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-file-user-line text-warning me-2"></i>
                                            <strong>Pending Admissions</strong>
                                            <p class="mb-0 text-muted"><?php echo number_format($dashboardStats['pending_admissions']); ?> applications require review</p>
                                        </div>
                                        <span class="badge bg-warning rounded-pill"><?php echo number_format($dashboardStats['pending_admissions']); ?></span>
                                    </div>
                                </a>
                                <?php endif; ?>

                                <!-- Overdue Invoices -->
                                <?php if (($dashboardStats['overdue_invoices'] ?? 0) > 0): ?>
                                <a href="<?php echo APP_URL; ?>modules/fees/defaulters.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-file-warning-line text-danger me-2"></i>
                                            <strong>Overdue Fee Invoices</strong>
                                            <p class="mb-0 text-muted"><?php echo number_format($dashboardStats['overdue_invoices']); ?> invoices are overdue</p>
                                        </div>
                                        <span class="badge bg-danger rounded-pill"><?php echo number_format($dashboardStats['overdue_invoices']); ?></span>
                                    </div>
                                </a>
                                <?php endif; ?>

                                <!-- Incomplete Profiles -->
                                <?php if (($dashboardStats['incomplete_profiles'] ?? 0) > 0): ?>
                                <a href="<?php echo APP_URL; ?>modules/students/list.php?filter=incomplete" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-user-settings-line text-info me-2"></i>
                                            <strong>Incomplete Student Profiles</strong>
                                            <p class="mb-0 text-muted"><?php echo number_format($dashboardStats['incomplete_profiles']); ?> students missing photos</p>
                                        </div>
                                        <span class="badge bg-info rounded-pill"><?php echo number_format($dashboardStats['incomplete_profiles']); ?></span>
                                    </div>
                                </a>
                                <?php endif; ?>

                                <!-- Open Support Tickets -->
                                <?php if (($dashboardStats['open_tickets'] ?? 0) > 0): ?>
                                <a href="<?php echo APP_URL; ?>modules/support/tickets.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-customer-service-2-line text-primary me-2"></i>
                                            <strong>Open Support Tickets</strong>
                                            <p class="mb-0 text-muted"><?php echo number_format($dashboardStats['open_tickets']); ?> tickets need attention</p>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo number_format($dashboardStats['open_tickets']); ?></span>
                                    </div>
                                </a>
                                <?php endif; ?>

                                <!-- Pending Payroll -->
                                <?php if (($dashboardStats['payroll']['pending'] ?? 0) > 0): ?>
                                <a href="<?php echo APP_URL; ?>modules/hr/payroll.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-money-dollar-circle-line text-success me-2"></i>
                                            <strong>Pending Payroll</strong>
                                            <p class="mb-0 text-muted"><?php echo number_format($dashboardStats['payroll']['pending']); ?> staff pending payment</p>
                                        </div>
                                        <span class="badge bg-success rounded-pill"><?php echo number_format($dashboardStats['payroll']['pending']); ?></span>
                                    </div>
                                </a>
                                <?php endif; ?>

                                <!-- No Alerts -->
                                <?php if (($dashboardStats['pending_admissions'] ?? 0) == 0 && 
                                          ($dashboardStats['overdue_invoices'] ?? 0) == 0 && 
                                          ($dashboardStats['incomplete_profiles'] ?? 0) == 0 && 
                                          ($dashboardStats['open_tickets'] ?? 0) == 0 && 
                                          ($dashboardStats['payroll']['pending'] ?? 0) == 0): ?>
                                <div class="list-group-item">
                                    <div class="text-center text-muted py-3">
                                        <i class="ri-checkbox-circle-line text-success font-24"></i>
                                        <p class="mb-0 mt-2">All systems operational. No pending actions.</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-time-line"></i> Recent Activity
                            </h4>
                            <div class="activity-feed" id="recentActivity">
                                <?php if (!empty($recentActivities)): ?>
                                    <?php foreach (array_slice($recentActivities, 0, 8) as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-<?php echo strtolower($activity['module'] ?? 'primary'); ?>-lighten text-<?php echo strtolower($activity['module'] ?? 'primary'); ?>">
                                            <i class="ri-<?php echo function_exists('getActivityIcon') ? getActivityIcon($activity['action']) : 'file-line'; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($activity['description'] ?? ''); ?></p>
                                            <small class="text-muted">
                                                <i class="ri-time-line"></i> <?php echo timeAgo($activity['created_at']); ?>
                                                <?php if ($activity['username']): ?>
                                                | <i class="ri-user-line"></i> <?php echo htmlspecialchars($activity['username']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">No recent activity</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Classes -->
            <?php if (!empty($dashboardStats['top_classes'])): ?>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-trophy-line text-warning"></i> Top Performing Classes
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Class</th>
                                            <th>Students</th>
                                            <th>Average Score</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dashboardStats['top_classes'] as $index => $class): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?php echo $index < 3 ? 'warning' : 'secondary'; ?> rounded-pill">
                                                    #<?php echo $index + 1; ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                            <td><?php echo number_format($class['students_count']); ?></td>
                                            <td><strong><?php echo number_format($class['avg_percentage'], 1); ?>%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?php echo $class['avg_percentage'] >= 80 ? 'success' : ($class['avg_percentage'] >= 60 ? 'warning' : 'danger'); ?>" 
                                                         style="width: <?php echo $class['avg_percentage']; ?>%">
                                                        <?php echo number_format($class['avg_percentage'], 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- Non-Admin Dashboard -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-dashboard-3-line font-48 text-muted"></i>
                            <h4 class="mt-3">Welcome to TacliinHub ERP System</h4>
                            <p class="text-muted">Your personalized dashboard is being prepared.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div> <!-- container -->
    </div> <!-- content -->
</div>

<?php include 'includes/footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
.widget-stat-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.widget-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-lighten { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-success-lighten { background-color: rgba(40, 167, 69, 0.1) !important; }
.bg-info-lighten { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-warning-lighten { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-danger-lighten { background-color: rgba(220, 53, 69, 0.1) !important; }

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.activity-feed {
    max-height: 500px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 12px;
}

.activity-content {
    flex: 1;
}

.activity-content h6 {
    font-size: 14px;
    margin-bottom: 4px;
}

.activity-content p {
    font-size: 12px;
    margin-bottom: 4px;
}

.list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.list-group-item:hover {
    border-left-color: #0d6efd;
    background-color: #f8f9fa;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    overflow: hidden;
}

.chart-container canvas {
    max-height: 100% !important;
    max-width: 100% !important;
}

.chart-container .no-data-message {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    z-index: 1;
}
</style>

<script>
// Dashboard data
let dashboardData = <?php echo json_encode($dashboardStats); ?>;
let branchId = <?php echo json_encode($branchId); ?>;

// Initialize charts
let feeTrendChart = null;
let attendanceTrendChart = null;
let studentStatusChart = null;
let feeStatusChart = null;
let staffDistributionChart = null;
let revenueVsOutstandingChart = null;
let performanceRadarChart = null;
let classWiseDistributionChart = null;
let examPerformanceChart = null;

// Initialize dashboard
$(document).ready(function() {
    initializeCharts();
    startAutoRefresh();
});

// Initialize charts
function initializeCharts() {
    // Fee Collection Trend Chart
    const feeCtx = document.getElementById('feeTrendChart');
    if (feeCtx) {
        const feeData = dashboardData.fee_trend || [];
        
        // Generate last 6 months labels even if no data
        const last6Months = [];
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            last6Months.push(date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
        }
        
        // Map data to months
        const feeDataMap = {};
        feeData.forEach(item => {
            const date = new Date(item.month + '-01');
            const key = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            feeDataMap[key] = parseFloat(item.total) || 0;
        });
        
        const feeValues = last6Months.map(month => feeDataMap[month] || 0);
        
        feeTrendChart = new Chart(feeCtx, {
            type: 'line',
            data: {
                labels: last6Months,
                datasets: [{
                    label: 'Fee Collection',
                    data: feeValues,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '<?php echo CURRENCY_SYMBOL; ?>' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '<?php echo CURRENCY_SYMBOL; ?>' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Show message if no data
        if (feeData.length === 0 && !feeCtx.parentElement.querySelector('.no-data-message')) {
            const noDataMsg = document.createElement('div');
            noDataMsg.className = 'no-data-message text-center text-muted py-4';
            noDataMsg.innerHTML = '<i class="ri-information-line"></i> No fee collection data available for the last 6 months';
            feeCtx.parentElement.appendChild(noDataMsg);
        }
    }

    // Attendance Trend Chart
    const attCtx = document.getElementById('attendanceTrendChart');
    if (attCtx) {
        const attData = dashboardData.attendance_trend || [];
        
        // Generate last 30 days labels even if no data
        const last30Days = [];
        const last30DaysData = {};
        
        for (let i = 29; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            const dateStr = date.toISOString().split('T')[0];
            const label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            last30Days.push(label);
            last30DaysData[dateStr] = { present: 0, total: 0 };
        }
        
        // Map actual data
        attData.forEach(item => {
            const dateStr = item.date;
            if (last30DaysData[dateStr]) {
                last30DaysData[dateStr] = {
                    present: parseInt(item.present) || 0,
                    total: parseInt(item.total) || 0
                };
            }
        });
        
        const presentValues = Object.values(last30DaysData).map(d => d.present);
        const totalValues = Object.values(last30DaysData).map(d => d.total);
        
        attendanceTrendChart = new Chart(attCtx, {
            type: 'bar',
            data: {
                labels: last30Days,
                datasets: [{
                    label: 'Present',
                    data: presentValues,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }, {
                    label: 'Total',
                    data: totalValues,
                    backgroundColor: 'rgba(13, 202, 240, 0.3)',
                    borderColor: 'rgb(13, 202, 240)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Show message if no data
        if (attData.length === 0 && !attCtx.parentElement.querySelector('.no-data-message')) {
            const noDataMsg = document.createElement('div');
            noDataMsg.className = 'no-data-message text-center text-muted py-4';
            noDataMsg.innerHTML = '<i class="ri-information-line"></i> No attendance data available for the last 30 days';
            attCtx.parentElement.appendChild(noDataMsg);
        }
    }

    // Student Status Distribution Chart (Doughnut)
    const studentStatusCtx = document.getElementById('studentStatusChart');
    if (studentStatusCtx) {
        const statusData = dashboardData.student_status_dist || [];
        const labels = statusData.map(item => item.status);
        const values = statusData.map(item => parseInt(item.count));
        
        studentStatusChart = new Chart(studentStatusCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(13, 202, 240, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderColor: [
                        'rgb(40, 167, 69)',
                        'rgb(13, 202, 240)',
                        'rgb(255, 193, 7)',
                        'rgb(220, 53, 69)',
                        'rgb(108, 117, 125)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Fee Status Distribution Chart (Pie)
    const feeStatusCtx = document.getElementById('feeStatusChart');
    if (feeStatusCtx) {
        const feeStatusData = dashboardData.fee_status_dist || [];
        const labels = feeStatusData.map(item => item.status);
        const values = feeStatusData.map(item => parseInt(item.count));
        
        feeStatusChart = new Chart(feeStatusCtx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(13, 202, 240, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderColor: [
                        'rgb(40, 167, 69)',
                        'rgb(255, 193, 7)',
                        'rgb(220, 53, 69)',
                        'rgb(13, 202, 240)',
                        'rgb(108, 117, 125)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Staff Distribution Chart (Doughnut)
    const staffDistCtx = document.getElementById('staffDistributionChart');
    if (staffDistCtx) {
        const staffData = dashboardData.staff_dist || [];
        const labels = staffData.map(item => item.employment_type || 'Other');
        const values = staffData.map(item => parseInt(item.count));
        
        staffDistributionChart = new Chart(staffDistCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(13, 202, 240, 0.8)'
                    ],
                    borderColor: [
                        'rgb(13, 110, 253)',
                        'rgb(40, 167, 69)',
                        'rgb(255, 193, 7)',
                        'rgb(220, 53, 69)',
                        'rgb(13, 202, 240)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Revenue vs Outstanding Fees Chart (Area)
    const revenueVsOutstandingCtx = document.getElementById('revenueVsOutstandingChart');
    if (revenueVsOutstandingCtx) {
        const revenueData = dashboardData.revenue_vs_outstanding || {};
        const revenueTrend = revenueData.revenue || [];
        const outstandingTrend = revenueData.outstanding || [];
        
        // Generate last 6 months labels
        const last6Months = [];
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            last6Months.push(date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
        }
        
        // Map data to months
        const revenueMap = {};
        revenueTrend.forEach(item => {
            const date = new Date(item.month + '-01');
            const key = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            revenueMap[key] = parseFloat(item.revenue) || 0;
        });
        
        const outstandingMap = {};
        outstandingTrend.forEach(item => {
            const date = new Date(item.month + '-01');
            const key = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            outstandingMap[key] = parseFloat(item.outstanding) || 0;
        });
        
        const revenueValues = last6Months.map(month => revenueMap[month] || 0);
        const outstandingValues = last6Months.map(month => outstandingMap[month] || 0);
        
        revenueVsOutstandingChart = new Chart(revenueVsOutstandingCtx, {
            type: 'line',
            data: {
                labels: last6Months,
                datasets: [{
                    label: 'Revenue',
                    data: revenueValues,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Outstanding',
                    data: outstandingValues,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': <?php echo CURRENCY_SYMBOL; ?>' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '<?php echo CURRENCY_SYMBOL; ?>' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Overall Performance Metrics (Radar)
    const performanceRadarCtx = document.getElementById('performanceRadarChart');
    if (performanceRadarCtx) {
        const metrics = dashboardData.performance_metrics || {};
        
        performanceRadarChart = new Chart(performanceRadarCtx, {
            type: 'radar',
            data: {
                labels: [
                    'Attendance',
                    'Fee Collection',
                    'Academic',
                    'Staff Satisfaction',
                    'Student Retention',
                    'Operational Efficiency'
                ],
                datasets: [{
                    label: 'Performance Score',
                    data: [
                        metrics.attendance || 0,
                        metrics.fee_collection || 0,
                        metrics.academic || 0,
                        metrics.staff_satisfaction || 0,
                        metrics.student_retention || 0,
                        metrics.operational_efficiency || 0
                    ],
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgb(13, 110, 253)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgb(13, 110, 253)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(13, 110, 253)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.r.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Class-wise Distribution Chart (Horizontal Bar)
    const classWiseDistCtx = document.getElementById('classWiseDistributionChart');
    if (classWiseDistCtx) {
        const classData = dashboardData.class_wise_dist || [];
        const labels = classData.map(item => item.class_name);
        const values = classData.map(item => parseInt(item.student_count));
        
        classWiseDistributionChart = new Chart(classWiseDistCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Students',
                    data: values,
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: 'rgb(13, 110, 253)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Exam Performance by Class Chart (Bar)
    const examPerformanceCtx = document.getElementById('examPerformanceChart');
    if (examPerformanceCtx) {
        const examData = dashboardData.exam_performance || [];
        const labels = examData.map(item => item.class_name);
        const avgScores = examData.map(item => parseFloat(item.avg_percentage) || 0);
        const maxScores = examData.map(item => parseFloat(item.max_percentage) || 0);
        const minScores = examData.map(item => parseFloat(item.min_percentage) || 0);
        
        examPerformanceChart = new Chart(examPerformanceCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Score',
                    data: avgScores,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }, {
                    label: 'Maximum Score',
                    data: maxScores,
                    backgroundColor: 'rgba(13, 202, 240, 0.6)',
                    borderColor: 'rgb(13, 202, 240)',
                    borderWidth: 1
                }, {
                    label: 'Minimum Score',
                    data: minScores,
                    backgroundColor: 'rgba(255, 193, 7, 0.6)',
                    borderColor: 'rgb(255, 193, 7)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Refresh dashboard data
function refreshDashboard() {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Refreshing...';
    btn.disabled = true;
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/dashboard/get-stats.php',
        type: 'GET',
        data: {
            branch_id: branchId || '',
            cache: false // Force refresh
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                dashboardData = response.data;
                updateDashboardUI();
                Swal.fire('Success', 'Dashboard refreshed successfully', 'success');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to refresh dashboard', 'error');
        },
        complete: function() {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    });
}

// Update dashboard UI with new data
function updateDashboardUI() {
    // Update KPI cards
    $('#stat-total-students').text(dashboardData.students?.total?.toLocaleString() || '0');
    $('#stat-total-staff').text(dashboardData.staff?.total?.toLocaleString() || '0');
    $('#stat-active-classes').text(dashboardData.classes?.active?.toLocaleString() || '0');
    $('#stat-attendance-today').text((dashboardData.attendance_today?.percentage || 0) + '%');
    $('#stat-monthly-revenue').text(formatCurrency(dashboardData.revenue_month || 0));
    $('#stat-outstanding-fees').text(formatCurrency(dashboardData.outstanding_fees || 0));
    
    // Update charts
    if (feeTrendChart) {
        const feeData = dashboardData.fee_trend || [];
        const last6Months = [];
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            last6Months.push(date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
        }
        
        const feeDataMap = {};
        feeData.forEach(item => {
            const date = new Date(item.month + '-01');
            const key = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            feeDataMap[key] = parseFloat(item.total) || 0;
        });
        
        const feeValues = last6Months.map(month => feeDataMap[month] || 0);
        
        feeTrendChart.data.labels = last6Months;
        feeTrendChart.data.datasets[0].data = feeValues;
        feeTrendChart.update();
    }
    
    if (attendanceTrendChart) {
        const attData = dashboardData.attendance_trend || [];
        const last30Days = [];
        const last30DaysData = {};
        
        for (let i = 29; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            const dateStr = date.toISOString().split('T')[0];
            const label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            last30Days.push(label);
            last30DaysData[dateStr] = { present: 0, total: 0 };
        }
        
        attData.forEach(item => {
            const dateStr = item.date;
            if (last30DaysData[dateStr]) {
                last30DaysData[dateStr] = {
                    present: parseInt(item.present) || 0,
                    total: parseInt(item.total) || 0
                };
            }
        });
        
        const presentValues = Object.values(last30DaysData).map(d => d.present);
        const totalValues = Object.values(last30DaysData).map(d => d.total);
        
        attendanceTrendChart.data.labels = last30Days;
        attendanceTrendChart.data.datasets[0].data = presentValues;
        attendanceTrendChart.data.datasets[1].data = totalValues;
        attendanceTrendChart.update();
    }
    
    // Update Student Status Chart
    if (studentStatusChart) {
        const statusData = dashboardData.student_status_dist || [];
        studentStatusChart.data.labels = statusData.map(item => item.status);
        studentStatusChart.data.datasets[0].data = statusData.map(item => parseInt(item.count));
        studentStatusChart.update();
    }
    
    // Update Fee Status Chart
    if (feeStatusChart) {
        const feeStatusData = dashboardData.fee_status_dist || [];
        feeStatusChart.data.labels = feeStatusData.map(item => item.status);
        feeStatusChart.data.datasets[0].data = feeStatusData.map(item => parseInt(item.count));
        feeStatusChart.update();
    }
    
    // Update Staff Distribution Chart
    if (staffDistributionChart) {
        const staffData = dashboardData.staff_dist || [];
        staffDistributionChart.data.labels = staffData.map(item => item.employment_type || 'Other');
        staffDistributionChart.data.datasets[0].data = staffData.map(item => parseInt(item.count));
        staffDistributionChart.update();
    }
    
    // Update Revenue vs Outstanding Chart
    if (revenueVsOutstandingChart) {
        const revenueData = dashboardData.revenue_vs_outstanding || {};
        const revenueTrend = revenueData.revenue || [];
        const outstandingTrend = revenueData.outstanding || [];
        
        const last6Months = [];
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            last6Months.push(date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
        }
        
        const revenueMap = {};
        revenueTrend.forEach(item => {
            const date = new Date(item.month + '-01');
            const key = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            revenueMap[key] = parseFloat(item.revenue) || 0;
        });
        
        const outstandingMap = {};
        outstandingTrend.forEach(item => {
            const date = new Date(item.month + '-01');
            const key = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            outstandingMap[key] = parseFloat(item.outstanding) || 0;
        });
        
        const revenueValues = last6Months.map(month => revenueMap[month] || 0);
        const outstandingValues = last6Months.map(month => outstandingMap[month] || 0);
        
        revenueVsOutstandingChart.data.labels = last6Months;
        revenueVsOutstandingChart.data.datasets[0].data = revenueValues;
        revenueVsOutstandingChart.data.datasets[1].data = outstandingValues;
        revenueVsOutstandingChart.update();
    }
    
    // Update Performance Radar Chart
    if (performanceRadarChart) {
        const metrics = dashboardData.performance_metrics || {};
        performanceRadarChart.data.datasets[0].data = [
            metrics.attendance || 0,
            metrics.fee_collection || 0,
            metrics.academic || 0,
            metrics.staff_satisfaction || 0,
            metrics.student_retention || 0,
            metrics.operational_efficiency || 0
        ];
        performanceRadarChart.update();
    }
    
    // Update Class-wise Distribution Chart
    if (classWiseDistributionChart) {
        const classData = dashboardData.class_wise_dist || [];
        classWiseDistributionChart.data.labels = classData.map(item => item.class_name);
        classWiseDistributionChart.data.datasets[0].data = classData.map(item => parseInt(item.student_count));
        classWiseDistributionChart.update();
    }
    
    // Update Exam Performance Chart
    if (examPerformanceChart) {
        const examData = dashboardData.exam_performance || [];
        examPerformanceChart.data.labels = examData.map(item => item.class_name);
        examPerformanceChart.data.datasets[0].data = examData.map(item => parseFloat(item.avg_percentage) || 0);
        examPerformanceChart.data.datasets[1].data = examData.map(item => parseFloat(item.max_percentage) || 0);
        examPerformanceChart.data.datasets[2].data = examData.map(item => parseFloat(item.min_percentage) || 0);
        examPerformanceChart.update();
    }
}

// Filter by branch
function filterByBranch(branchId) {
    window.location.href = 'dashboard.php?branch_id=' + branchId;
}

// Auto-refresh every 5 minutes
function startAutoRefresh() {
    setInterval(function() {
        refreshDashboard();
    }, 300000); // 5 minutes
}

// Export dashboard
function exportDashboard() {
    window.print();
}

// Format currency
function formatCurrency(amount) {
    return '<?php echo CURRENCY_SYMBOL; ?>' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Time ago helper
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    return date.toLocaleDateString();
}

// Get activity icon
function getActivityIcon(action) {
    const icons = {
        'Create': 'add-line',
        'Update': 'edit-line',
        'Delete': 'delete-bin-line',
        'Login': 'login-box-line',
        'Logout': 'logout-box-line',
        'View': 'eye-line',
        'Download': 'download-line',
        'Print': 'printer-line'
    };
    return icons[action] || 'file-line';
}

// Add spin animation
$('<style>').text(`
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin {
        animation: spin 1s linear infinite;
    }
`).appendTo('head');
</script>
