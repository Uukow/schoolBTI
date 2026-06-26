<?php
/**
 * Financial Reports
 * 
 * Generate financial reports and summaries
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Financial Reports';

// Get current user and filters
$currentUser = getCurrentUser();
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$reportType = $_GET['type'] ?? 'summary';

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Calculate summary
$summary = [];

// Fee collections
$feeSql = "SELECT SUM(amount) as total FROM fee_payments WHERE payment_date BETWEEN ? AND ?";
$feeParams = [$startDate, $endDate];
$feeTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $feeSql .= " AND student_id IN (SELECT id FROM students WHERE branch_id = ?)";
    $feeParams[] = $currentUser['branch_id'];
    $feeTypes .= 'i';
}

$feeResult = fetchOne(executeQuery($feeSql, $feeTypes, $feeParams));
$summary['fee_collections'] = $feeResult['total'] ?? 0;

// Income
$incomeSql = "SELECT SUM(amount) as total FROM income WHERE income_date BETWEEN ? AND ?";
$incomeParams = [$startDate, $endDate];
$incomeTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $incomeSql .= " AND branch_id = ?";
    $incomeParams[] = $currentUser['branch_id'];
    $incomeTypes .= 'i';
}

$incomeResult = fetchOne(executeQuery($incomeSql, $incomeTypes, $incomeParams));
$summary['income'] = $incomeResult['total'] ?? 0;

// Expenses
$expenseSql = "SELECT SUM(amount) as total FROM expenses WHERE expense_date BETWEEN ? AND ?";
$expenseParams = [$startDate, $endDate];
$expenseTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $expenseSql .= " AND branch_id = ?";
    $expenseParams[] = $currentUser['branch_id'];
    $expenseTypes .= 'i';
}

$expenseResult = fetchOne(executeQuery($expenseSql, $expenseTypes, $expenseParams));
$summary['expenses'] = $expenseResult['total'] ?? 0;

// Total income
$summary['total_income'] = $summary['fee_collections'] + $summary['income'];

// Net profit/loss
$summary['net'] = $summary['total_income'] - $summary['expenses'];

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
                            <button onclick="window.print()" class="btn btn-secondary no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportTableToExcel('reportsTable', 'financial_report')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export
                            </button>
                        </div>
                        <h4 class="page-title">Financial Reports</h4>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row no-print">
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
                                    <label class="form-label">Report Type</label>
                                    <select class="form-select" name="type">
                                        <option value="summary" <?php echo ($reportType == 'summary') ? 'selected' : ''; ?>>Summary</option>
                                        <option value="detailed" <?php echo ($reportType == 'detailed') ? 'selected' : ''; ?>>Detailed</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Generate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Fee Collections</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($summary['fee_collections']); ?></h2>
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
                                        <i class="ri-arrow-up-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Other Income</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($summary['income']); ?></h2>
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
                                        <i class="ri-arrow-down-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Expenses</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($summary['expenses']); ?></h2>
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
                                    <div class="stat-icon bg-<?php echo $summary['net'] >= 0 ? 'success' : 'danger'; ?>-lighten text-<?php echo $summary['net'] >= 0 ? 'success' : 'danger'; ?>">
                                        <i class="ri-line-chart-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Net <?php echo $summary['net'] >= 0 ? 'Profit' : 'Loss'; ?></h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($summary['net']); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Financial Summary</h4>
                            <p class="text-muted">Period: <?php echo formatDate($startDate); ?> to <?php echo formatDate($endDate); ?></p>
                            
                            <div class="table-responsive" id="reportsTable">
                                <table class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Category</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Fee Collections</strong></td>
                                            <td class="text-end"><strong class="text-primary"><?php echo formatCurrency($summary['fee_collections']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Other Income</strong></td>
                                            <td class="text-end"><strong class="text-success"><?php echo formatCurrency($summary['income']); ?></strong></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><strong>Total Income</strong></td>
                                            <td class="text-end"><strong><?php echo formatCurrency($summary['total_income']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Expenses</strong></td>
                                            <td class="text-end"><strong class="text-danger"><?php echo formatCurrency($summary['expenses']); ?></strong></td>
                                        </tr>
                                        <tr class="table-<?php echo $summary['net'] >= 0 ? 'success' : 'danger'; ?>">
                                            <td><strong>Net <?php echo $summary['net'] >= 0 ? 'Profit' : 'Loss'; ?></strong></td>
                                            <td class="text-end"><strong><?php echo formatCurrency($summary['net']); ?></strong></td>
                                        </tr>
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

