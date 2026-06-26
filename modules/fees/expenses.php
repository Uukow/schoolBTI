<?php
/**
 * Expenses Management
 * 
 * Record and manage expense transactions
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Expenses Management';

// Get current user and filters
$currentUser = getCurrentUser();
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$categoryFilter = $_GET['category'] ?? '';

// Build query
$sql = "SELECT e.*, b.branch_name, u1.username as recorded_by_name, u2.username as approved_by_name
        FROM expenses e
        LEFT JOIN branches b ON e.branch_id = b.id
        LEFT JOIN users u1 ON e.recorded_by = u1.id
        LEFT JOIN users u2 ON e.approved_by = u2.id
        WHERE e.expense_date BETWEEN ? AND ?";

$params = [$startDate, $endDate];
$types = 'ss';

if (!empty($categoryFilter)) {
    $sql .= " AND e.expense_category = ?";
    $params[] = $categoryFilter;
    $types .= 's';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND e.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY e.expense_date DESC, e.id DESC";

$expenses = fetchAll(executeQuery($sql, $types, $params));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_records,
    SUM(amount) as total_amount
    FROM expenses
    WHERE expense_date BETWEEN ? AND ?";

$statsParams = [$startDate, $endDate];
$statsTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $statsSql .= " AND branch_id = ?";
    $statsParams[] = $currentUser['branch_id'];
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

// Get unique categories
$categoriesSql = "SELECT DISTINCT expense_category FROM expenses ORDER BY expense_category";
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="ri-add-line"></i> Add Expense
                            </button>
                        </div>
                        <h4 class="page-title">Expenses Management</h4>
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
                                    <div class="stat-icon bg-danger-lighten text-danger">
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
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Expenses</h5>
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
                                            <option value="<?php echo htmlspecialchars($cat['expense_category']); ?>" <?php echo ($categoryFilter == $cat['expense_category']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['expense_category']); ?>
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

            <!-- Expenses List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Expense Records</h4>
                            
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
                                            <th>Approved By</th>
                                            <th>Branch</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expenses as $expense): ?>
                                        <tr>
                                            <td><?php echo formatDate($expense['expense_date']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($expense['expense_category']); ?></strong></td>
                                            <td><strong class="text-danger"><?php echo formatCurrency($expense['amount']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($expense['payment_method'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($expense['reference_no'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($expense['description'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($expense['approved_by_name']): ?>
                                                    <span class="badge bg-success"><?php echo htmlspecialchars($expense['approved_by_name']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($expense['branch_name'] ?? 'All'); ?></td>
                                            <td>
                                                <?php if (!$expense['approved_by'] && hasRole(['Super Admin', 'Admin'])): ?>
                                                <button onclick="approveExpense(<?php echo $expense['id']; ?>)" 
                                                        class="btn btn-sm btn-success" title="Approve">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button onclick="editExpense(<?php echo $expense['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button onclick="deleteExpense(<?php echo $expense['id']; ?>)" 
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

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm">
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
                        <label class="form-label required">Expense Category</label>
                        <input type="text" class="form-control" name="expense_category" required placeholder="e.g., Salaries, Utilities, Supplies">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Expense Date</label>
                        <input type="date" class="form-control" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Online">Online</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No</label>
                        <input type="text" class="form-control" name="reference_no" placeholder="Invoice/Receipt number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add expense
$('#addExpenseForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/add-expense.php',
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

// Approve expense
function approveExpense(id) {
    Swal.fire({
        title: 'Approve Expense?',
        text: 'Approve this expense record?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Approve!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/approve-expense.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
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

// Delete expense
function deleteExpense(id) {
    Swal.fire({
        title: 'Delete Expense Record?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/delete-expense.php',
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

// Edit expense (placeholder)
function editExpense(id) {
    Swal.fire({
        icon: 'info',
        title: 'Edit Feature',
        text: 'Edit functionality will be implemented in the next update.'
    });
}
</script>

