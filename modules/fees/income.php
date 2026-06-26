<?php
/**
 * Income Management
 * 
 * Record and manage income transactions
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Income Management';

// Get current user and filters
$currentUser = getCurrentUser();
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$categoryFilter = $_GET['category'] ?? '';

// Build query
$sql = "SELECT i.*, b.branch_name, u.username as recorded_by_name
        FROM income i
        LEFT JOIN branches b ON i.branch_id = b.id
        LEFT JOIN users u ON i.recorded_by = u.id
        WHERE i.income_date BETWEEN ? AND ?";

$params = [$startDate, $endDate];
$types = 'ss';

if (!empty($categoryFilter)) {
    $sql .= " AND i.income_category = ?";
    $params[] = $categoryFilter;
    $types .= 's';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND i.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY i.income_date DESC, i.id DESC";

$incomes = fetchAll(executeQuery($sql, $types, $params));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_records,
    SUM(amount) as total_amount
    FROM income
    WHERE income_date BETWEEN ? AND ?";

$statsParams = [$startDate, $endDate];
$statsTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $statsSql .= " AND branch_id = ?";
    $statsParams[] = $currentUser['branch_id'];
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

// Get unique categories
$categoriesSql = "SELECT DISTINCT income_category FROM income ORDER BY income_category";
$categories = fetchAll(executeQuery($categoriesSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                                <i class="ri-add-line"></i> Add Income
                            </button>
                        </div>
                        <h4 class="page-title">Income Management</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-file-list-3-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Records</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_records'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Income</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_amount'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['income_category']); ?>" <?php echo ($categoryFilter == $cat['income_category']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['income_category']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Income List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Income Records</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Category</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Reference No</th>
                                            <th>Description</th>
                                            <th>Branch</th>
                                            <th>Recorded By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($incomes as $income): ?>
                                        <tr>
                                            <td><?php echo formatDate($income['income_date']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($income['income_category']); ?></strong></td>
                                            <td><strong class="text-success"><?php echo formatCurrency($income['amount']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($income['payment_method'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($income['reference_no'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($income['description'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($income['branch_name'] ?? 'All'); ?></td>
                                            <td><?php echo htmlspecialchars($income['recorded_by_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <button onclick="editIncome(<?php echo $income['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button onclick="deleteIncome(<?php echo $income['id']; ?>)" 
                                                        class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
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

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Income Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addIncomeForm">
                <div class="modal-body">
                    <?php if (hasRole(['Super Admin'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="branch_id">
                            <option value="">All Branches</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['id']; ?>">
                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label required">Income Category</label>
                        <input type="text" class="form-control" name="income_category" required placeholder="e.g., Tuition Fees, Donations">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Income Date</label>
                        <input type="date" class="form-control" name="income_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Online">Online</option>
                            <option value="EVC">EVC</option>
                            <option value="Zaad">Zaad</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No</label>
                        <input type="text" class="form-control" name="reference_no" placeholder="Transaction/Receipt number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add income
$('#addIncomeForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/add-income.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        }
    });
});

// Delete income
function deleteIncome(id) {
    Swal.fire({
        title: 'Delete Income Record?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/delete-income.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                }
            });
        }
    });
}

// Edit income (placeholder)
function editIncome(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

