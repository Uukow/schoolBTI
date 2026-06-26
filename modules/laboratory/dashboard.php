<?php
/**
 * LAB Management System - Main Dashboard
 */

require_once '../../config/config.php';
requireLabRoles(labParticipantRoles());

$pageTitle = 'Laboratory Dashboard';
$currentUser = getCurrentUser();

$bf  = labBranchWhere();
$bfR = labBranchWhere('r');
$bfS = labBranchWhere('s');
$bfI = labBranchWhere('i');

// KPI Stats
$stats = [];

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_sections WHERE status='active'" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['total_sections'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt, COALESCE(SUM(total_cost),0) as inv_value FROM lab_inventory_items WHERE 1=1" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['total_items']  = $row['cnt'] ?? 0;
$stats['inv_value']    = $row['inv_value'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_inventory_items WHERE status='available'" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['available_items'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_inventory_items WHERE status='damaged'" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['damaged_items'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_material_requests WHERE status='pending'" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['pending_requests'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_experiment_sessions WHERE session_date >= CURDATE() AND status='scheduled'" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['upcoming_sessions'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_safety_incidents WHERE status IN('reported','under_investigation')" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['open_incidents'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_issues WHERE status IN('open','in_progress','escalated')" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['open_issues'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_bookings WHERE booking_date >= CURDATE() AND status='approved'" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['upcoming_bookings'] = $row['cnt'] ?? 0;

$row = fetchOne(executeQuery("SELECT COUNT(*) as cnt FROM lab_procurement WHERE status='received' AND purchase_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)" . $bf['sql'], $bf['types'] ?: null, $bf['params'] ?: null));
$stats['recent_purchases'] = $row['cnt'] ?? 0;

// Recent activity - latest requests
$recentSql = "SELECT r.request_number, r.status, r.created_at, u.username as requester
              FROM lab_material_requests r
              LEFT JOIN users u ON r.requester_id = u.id
              WHERE 1=1" . $bfR['sql'] . " ORDER BY r.created_at DESC LIMIT 8";
$recentRequests = fetchAll(executeQuery($recentSql, $bfR['types'] ?: null, $bfR['params'] ?: null));

// Upcoming sessions
$sessionSql = "SELECT s.session_date, s.start_time, e.experiment_title, ls.section_name, u.username as instructor
               FROM lab_experiment_sessions s
               LEFT JOIN lab_experiments e ON s.experiment_id = e.id
               LEFT JOIN lab_sections ls ON s.section_id = ls.id
               LEFT JOIN users u ON s.instructor_id = u.id
               WHERE s.session_date >= CURDATE() AND s.status='scheduled'" . $bfS['sql'] . "
               ORDER BY s.session_date, s.start_time LIMIT 6";
$upcomingSessions = fetchAll(executeQuery($sessionSql, $bfS['types'] ?: null, $bfS['params'] ?: null));

// Low stock items (available_qty < 3)
$lowStockSql = "SELECT i.item_title, i.item_code, i.available_qty, c.category_name
                FROM lab_inventory_items i
                LEFT JOIN lab_inventory_categories c ON i.category_id = c.id
                WHERE i.available_qty < 3 AND i.status='available'" . $bfI['sql'] . "
                ORDER BY i.available_qty LIMIT 5";
$lowStockItems = fetchAll(executeQuery($lowStockSql, $bfI['types'] ?: null, $bfI['params'] ?: null));

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
                            <span class="badge bg-success fs-6"><i class="ri-flask-line me-1"></i> LAB Management System</span>
                        </div>
                        <h4 class="page-title">Laboratory Dashboard</h4>
                    </div>
                </div>
            </div>

            <!-- Row 1: Primary KPIs -->
            <div class="row">
                <?php
                $cards = [
                    ['icon' => 'ri-building-2-line',       'color' => 'primary',  'label' => 'Total Labs',        'value' => $stats['total_sections']],
                    ['icon' => 'ri-tools-line',             'color' => 'info',     'label' => 'Total Equipment',   'value' => $stats['total_items']],
                    ['icon' => 'ri-checkbox-circle-line',   'color' => 'success',  'label' => 'Available Items',   'value' => $stats['available_items']],
                    ['icon' => 'ri-alert-line',             'color' => 'danger',   'label' => 'Damaged Items',     'value' => $stats['damaged_items']],
                    ['icon' => 'ri-file-list-3-line',       'color' => 'warning',  'label' => 'Pending Requests',  'value' => $stats['pending_requests']],
                    ['icon' => 'ri-calendar-event-line',    'color' => 'purple',   'label' => 'Upcoming Sessions', 'value' => $stats['upcoming_sessions']],
                    ['icon' => 'ri-shield-flash-line',      'color' => 'danger',   'label' => 'Open Incidents',    'value' => $stats['open_incidents']],
                    ['icon' => 'ri-error-warning-line',     'color' => 'warning',  'label' => 'Open Issues',       'value' => $stats['open_issues']],
                ];
                foreach ($cards as $c): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-<?php echo $c['color']; ?>-lighten text-<?php echo $c['color']; ?>">
                                        <i class="<?php echo $c['icon']; ?> font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted"><?php echo $c['label']; ?></h5>
                                    <h2 class="mb-0"><?php echo number_format($c['value']); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Row 2: Inventory Value + Purchases -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="text-white-50 mb-1">Total Inventory Value</h5>
                            <h2 class="text-white mb-0"><?php echo formatCurrency($stats['inv_value']); ?></h2>
                            <small class="text-white-50">Based on purchase cost</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="text-white-50 mb-1">Recent Purchases (30 days)</h5>
                            <h2 class="text-white mb-0"><?php echo $stats['recent_purchases']; ?></h2>
                            <small class="text-white-50">Received items</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="text-white-50 mb-1">Upcoming Bookings</h5>
                            <h2 class="text-white mb-0"><?php echo $stats['upcoming_bookings']; ?></h2>
                            <small class="text-white-50">Approved reservations</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Material Requests -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title">Recent Material Requests</h4>
                                <a href="<?php echo APP_URL; ?>modules/laboratory/requests.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead><tr><th>Request #</th><th>Requester</th><th>Date</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($recentRequests as $req): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($req['request_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($req['requester'] ?? 'N/A'); ?></td>
                                            <td><?php echo formatDate($req['created_at']); ?></td>
                                            <td>
                                                <?php
                                                $statusColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','issued'=>'info','returned'=>'secondary','closed'=>'dark'];
                                                $sc = $statusColors[$req['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $sc; ?>"><?php echo ucfirst($req['status']); ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentRequests)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No requests yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock + Upcoming Sessions -->
                <div class="col-md-5">
                    <!-- Low Stock Alert -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title text-danger"><i class="ri-alert-line me-1"></i> Low Stock Alert</h4>
                                <a href="<?php echo APP_URL; ?>modules/laboratory/inventory.php" class="btn btn-sm btn-outline-danger">View All</a>
                            </div>
                            <?php if (empty($lowStockItems)): ?>
                            <p class="text-muted text-center mb-0">No low stock items</p>
                            <?php else: ?>
                            <?php foreach ($lowStockItems as $item): ?>
                            <div class="d-flex align-items-center mb-2 p-2 bg-danger-lighten rounded">
                                <i class="ri-error-warning-line text-danger me-2"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block" style="font-size:13px"><?php echo htmlspecialchars($item['item_title']); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars($item['category_name'] ?? ''); ?> &bull; Qty: <span class="text-danger fw-bold"><?php echo $item['available_qty']; ?></span></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming Sessions -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title">Upcoming Sessions</h4>
                                <a href="<?php echo APP_URL; ?>modules/laboratory/experiments.php" class="btn btn-sm btn-outline-info">View All</a>
                            </div>
                            <?php if (empty($upcomingSessions)): ?>
                            <p class="text-muted text-center mb-0">No upcoming sessions</p>
                            <?php else: ?>
                            <?php foreach ($upcomingSessions as $sess): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2 text-center" style="min-width:42px">
                                    <span class="badge bg-primary p-2"><?php echo date('d M', strtotime($sess['session_date'])); ?></span>
                                </div>
                                <div>
                                    <strong style="font-size:13px"><?php echo htmlspecialchars($sess['experiment_title'] ?? 'N/A'); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($sess['section_name'] ?? ''); ?> &bull; <?php echo substr($sess['start_time'], 0, 5); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Quick Navigation</h4>
                            <div class="row g-2">
                                <?php
                                $quickLinks = [
                                    ['url' => 'sections.php',    'icon' => 'ri-building-2-line',    'label' => 'Lab Sections',      'color' => 'primary'],
                                    ['url' => 'inventory.php',   'icon' => 'ri-tools-line',         'label' => 'Inventory',         'color' => 'info'],
                                    ['url' => 'requests.php',    'icon' => 'ri-file-list-3-line',   'label' => 'Material Requests', 'color' => 'warning'],
                                    ['url' => 'experiments.php', 'icon' => 'ri-flask-line',         'label' => 'Experiments',       'color' => 'success'],
                                    ['url' => 'maintenance.php', 'icon' => 'ri-settings-3-line',   'label' => 'Maintenance',       'color' => 'secondary'],
                                    ['url' => 'issues.php',      'icon' => 'ri-error-warning-line', 'label' => 'Issues',            'color' => 'danger'],
                                    ['url' => 'visitors.php',    'icon' => 'ri-user-received-line', 'label' => 'Visitors',          'color' => 'dark'],
                                    ['url' => 'safety.php',      'icon' => 'ri-shield-check-line',  'label' => 'Safety',            'color' => 'danger'],
                                    ['url' => 'procurement.php', 'icon' => 'ri-shopping-cart-line', 'label' => 'Procurement',       'color' => 'success'],
                                    ['url' => 'bookings.php',    'icon' => 'ri-calendar-check-line','label' => 'Bookings',          'color' => 'info'],
                                    ['url' => 'issue-types.php', 'icon' => 'ri-list-settings-line', 'label' => 'Issue Types',       'color' => 'secondary'],
                                    ['url' => 'reports.php',     'icon' => 'ri-bar-chart-box-line', 'label' => 'Reports',           'color' => 'primary'],
                                ];
                                foreach ($quickLinks as $ql): ?>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="<?php echo APP_URL; ?>modules/laboratory/<?php echo $ql['url']; ?>" class="btn btn-outline-<?php echo $ql['color']; ?> w-100 py-3">
                                        <i class="<?php echo $ql['icon']; ?> d-block fs-4 mb-1"></i>
                                        <small><?php echo $ql['label']; ?></small>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
