<?php
/**
 * Admissions List Page
 * 
 * Display all admission applications
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'Admission Applications';

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$branchFilter = $_GET['branch_id'] ?? '';

// Build query
$sql = "SELECT a.*, b.branch_name, c.class_name 
        FROM admission_applications a 
        LEFT JOIN branches b ON a.branch_id = b.id 
        LEFT JOIN classes c ON a.class_id = c.id 
        WHERE 1=1";

$params = [];
$types = '';

// Apply filters
if (!empty($statusFilter)) {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($branchFilter)) {
    $sql .= " AND a.branch_id = ?";
    $params[] = $branchFilter;
    $types .= 'i';
}

// Branch filter for non-super admins
$currentUser = getCurrentUser();
if (!hasRole(['Super Admin'])) {
    $sql .= " AND a.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$applications = fetchAll($stmt);

// Get branches for filter
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Under Review' THEN 1 ELSE 0 END) as under_review,
    SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM admission_applications WHERE 1=1";

if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
    $statsSql .= " AND branch_id = " . $currentUser['branch_id'];
}

$statsResult = executeQuery($statsSql);
$stats = fetchOne($statsResult);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/admissions/apply.php" class="btn btn-primary">
                                <i class="ri-file-add-line"></i> New Application
                            </a>
                        </div>
                        <h4 class="page-title">Admission Applications</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-file-list-3-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total</h5>
                                    <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-time-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Pending</h5>
                                    <h2 class="mb-0"><?php echo $stats['pending']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Accepted</h5>
                                    <h2 class="mb-0"><?php echo $stats['accepted']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-close-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Rejected</h5>
                                    <h2 class="mb-0"><?php echo $stats['rejected']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <?php if (hasRole(['Super Admin'])): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Branch</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchFilter == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Pending" <?php echo ($statusFilter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Under Review" <?php echo ($statusFilter == 'Under Review') ? 'selected' : ''; ?>>Under Review</option>
                                        <option value="Interview Scheduled" <?php echo ($statusFilter == 'Interview Scheduled') ? 'selected' : ''; ?>>Interview Scheduled</option>
                                        <option value="Accepted" <?php echo ($statusFilter == 'Accepted') ? 'selected' : ''; ?>>Accepted</option>
                                        <option value="Rejected" <?php echo ($statusFilter == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="list.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Applications (<?php echo count($applications); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr>
                                            <th>Application No</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Branch</th>
                                            <th>Parent Phone</th>
                                            <th>Applied Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($app['application_no']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['class_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($app['branch_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($app['parent_phone']); ?></td>
                                            <td><?php echo formatDate($app['applied_at']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($app['status']) {
                                                    case 'Pending': $statusClass = 'warning'; break;
                                                    case 'Under Review': $statusClass = 'info'; break;
                                                    case 'Interview Scheduled': $statusClass = 'primary'; break;
                                                    case 'Accepted': $statusClass = 'success'; break;
                                                    case 'Rejected': $statusClass = 'danger'; break;
                                                    case 'Enrolled': $statusClass = 'success'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($app['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view.php?id=<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <?php if ($app['status'] == 'Pending' || $app['status'] == 'Under Review'): ?>
                                                    <a href="review.php?id=<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Review">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if ($app['status'] == 'Accepted'): ?>
                                                    <button onclick="enrollStudent(<?php echo $app['id']; ?>)" 
                                                            class="btn btn-sm btn-success" title="Enroll">
                                                        <i class="ri-user-add-line"></i>
                                                    </button>
                                                    <?php endif; ?>
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

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Enroll student function
function enrollStudent(applicationId) {
    confirmAction('Are you sure you want to enroll this student? This will create a student record.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/admissions/enroll.php',
            type: 'POST',
            data: { application_id: applicationId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Failed to enroll student', 'error');
            }
        });
    });
}
</script>

