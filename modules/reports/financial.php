<?php
/**
 * Financial Reports
 * 
 * Generate financial reports
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Financial Reports';

// Get current user
$currentUser = getCurrentUser();

// Get filters
$reportType = $_GET['type'] ?? 'summary';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$branchFilter = $_GET['branch_id'] ?? '';

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Initialize report data
$reportData = [];
$reportTitle = 'Financial Report';

switch ($reportType) {
    case 'summary':
        // Financial summary
        $sql = "SELECT 
                (SELECT COALESCE(SUM(amount), 0) FROM fee_payments 
                 WHERE payment_date BETWEEN ? AND ?) as fee_collection,
                (SELECT COALESCE(SUM(amount), 0) FROM income 
                 WHERE income_date BETWEEN ? AND ?) as other_income,
                (SELECT COALESCE(SUM(amount), 0) FROM expenses 
                 WHERE expense_date BETWEEN ? AND ?) as total_expenses";
        
        $params = [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate];
        $types = 'ssssss';
        
        if (!hasRole(['Super Admin']) && !empty($currentUser['branch_id'])) {
            $sql = "SELECT 
                    (SELECT COALESCE(SUM(fp.amount), 0) FROM fee_payments fp
                     INNER JOIN fee_invoices fi ON fp.invoice_id = fi.id
                     INNER JOIN students s ON fi.student_id = s.id
                     WHERE fp.payment_date BETWEEN ? AND ? AND s.branch_id = ?) as fee_collection,
                    (SELECT COALESCE(SUM(amount), 0) FROM income 
                     WHERE income_date BETWEEN ? AND ? AND branch_id = ?) as other_income,
                    (SELECT COALESCE(SUM(amount), 0) FROM expenses 
                     WHERE expense_date BETWEEN ? AND ? AND branch_id = ?) as total_expenses";
            
            $params = [$startDate, $endDate, $currentUser['branch_id'], 
                      $startDate, $endDate, $currentUser['branch_id'],
                      $startDate, $endDate, $currentUser['branch_id']];
            $types = 'ssisssissi';
        }
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Financial Summary';
        break;
    
    case 'fees':
        // Fee collection report
        $sql = "SELECT DATE_FORMAT(fp.payment_date, '%Y-%m') as month,
                COUNT(DISTINCT fp.invoice_id) as invoices,
                COUNT(fp.id) as payments,
                SUM(fp.amount) as total_collected
                FROM fee_payments fp
                INNER JOIN fee_invoices fi ON fp.invoice_id = fi.id
                WHERE fp.payment_date BETWEEN ? AND ?";
        
        $params = [$startDate, $endDate];
        $types = 'ss';
        
        if (!hasRole(['Super Admin']) && !empty($currentUser['branch_id'])) {
            $sql .= " AND fi.student_id IN (SELECT id FROM students WHERE branch_id = ?)";
            $params[] = $currentUser['branch_id'];
            $types .= 'i';
        }
        
        $sql .= " GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m')
                  ORDER BY month DESC";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Fee Collection Report';
        break;
    
    case 'income':
        // Income report
        $sql = "SELECT income_category,
                COUNT(*) as transactions,
                SUM(amount) as total_amount
                FROM income
                WHERE income_date BETWEEN ? AND ?";
        
        $params = [$startDate, $endDate];
        $types = 'ss';
        
        if (!hasRole(['Super Admin']) && !empty($currentUser['branch_id'])) {
            $sql .= " AND branch_id = ?";
            $params[] = $currentUser['branch_id'];
            $types .= 'i';
        }
        
        $sql .= " GROUP BY income_category ORDER BY total_amount DESC";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Income Report';
        break;
    
    case 'expenses':
        // Expenses report
        $sql = "SELECT expense_category,
                COUNT(*) as transactions,
                SUM(amount) as total_amount
                FROM expenses
                WHERE expense_date BETWEEN ? AND ?";
        
        $params = [$startDate, $endDate];
        $types = 'ss';
        
        if (!hasRole(['Super Admin']) && !empty($currentUser['branch_id'])) {
            $sql .= " AND branch_id = ?";
            $params[] = $currentUser['branch_id'];
            $types .= 'i';
        }
        
        $sql .= " GROUP BY expense_category ORDER BY total_amount DESC";
        
        $reportData = fetchAll(executeQuery($sql, $types, $params));
        $reportTitle = 'Expenses Report';
        break;
}

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
                            <button onclick="exportTableToExcel('reportTable', 'financial_report')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export Excel
                            </button>
                        </div>
                        <h4 class="page-title">Financial Reports</h4>
                    </div>
                </div>
            </div>

            <!-- Report Type Tabs -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'summary' ? 'active' : ''; ?>" 
                                       href="?type=summary">
                                        <i class="ri-dashboard-line"></i> Summary
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'fees' ? 'active' : ''; ?>" 
                                       href="?type=fees">
                                        <i class="ri-money-dollar-circle-line"></i> Fee Collection
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'income' ? 'active' : ''; ?>" 
                                       href="?type=income">
                                        <i class="ri-arrow-up-circle-line"></i> Income
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $reportType == 'expenses' ? 'active' : ''; ?>" 
                                       href="?type=expenses">
                                        <i class="ri-arrow-down-circle-line"></i> Expenses
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <input type="hidden" name="type" value="<?php echo $reportType; ?>">
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <?php if (hasRole(['Super Admin'])): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Branch</label>
                                    <select class="form-select" name="branch_id">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchFilter == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Generate Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Data -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo $reportTitle; ?></h4>
                            
                            <?php if ($reportType == 'summary' && !empty($reportData)): ?>
                            <?php $summary = $reportData[0]; ?>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card widget-stat-card">
                                        <div class="card-body">
                                            <h5 class="text-muted">Fee Collection</h5>
                                            <h2 class="text-success"><?php echo formatCurrency($summary['fee_collection'] ?? 0); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card widget-stat-card">
                                        <div class="card-body">
                                            <h5 class="text-muted">Other Income</h5>
                                            <h2 class="text-info"><?php echo formatCurrency($summary['other_income'] ?? 0); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card widget-stat-card">
                                        <div class="card-body">
                                            <h5 class="text-muted">Total Expenses</h5>
                                            <h2 class="text-danger"><?php echo formatCurrency($summary['total_expenses'] ?? 0); ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h5 class="text-muted">Net Profit/Loss</h5>
                                            <?php 
                                            $totalIncome = ($summary['fee_collection'] ?? 0) + ($summary['other_income'] ?? 0);
                                            $netProfit = $totalIncome - ($summary['total_expenses'] ?? 0);
                                            ?>
                                            <h1 class="<?php echo $netProfit >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatCurrency($netProfit); ?>
                                            </h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php elseif (!empty($reportData)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="reportTable">
                                    <thead class="table-light">
                                        <?php if ($reportType == 'fees'): ?>
                                        <tr>
                                            <th>Month</th>
                                            <th>Invoices</th>
                                            <th>Payments</th>
                                            <th>Total Collected</th>
                                        </tr>
                                        <?php elseif ($reportType == 'income' || $reportType == 'expenses'): ?>
                                        <tr>
                                            <th>Category</th>
                                            <th>Transactions</th>
                                            <th>Total Amount</th>
                                        </tr>
                                        <?php endif; ?>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <?php if ($reportType == 'fees'): ?>
                                                <td><strong><?php echo date('F Y', strtotime($row['month'] . '-01')); ?></strong></td>
                                                <td><?php echo $row['invoices'] ?? 0; ?></td>
                                                <td><?php echo $row['payments'] ?? 0; ?></td>
                                                <td><strong><?php echo formatCurrency($row['total_collected'] ?? 0); ?></strong></td>
                                            <?php elseif ($reportType == 'income'): ?>
                                                <td><strong><?php echo htmlspecialchars($row['income_category'] ?? 'N/A'); ?></strong></td>
                                                <td><?php echo $row['transactions'] ?? 0; ?></td>
                                                <td><strong><?php echo formatCurrency($row['total_amount'] ?? 0); ?></strong></td>
                                            <?php elseif ($reportType == 'expenses'): ?>
                                                <td><strong><?php echo htmlspecialchars($row['expense_category'] ?? 'N/A'); ?></strong></td>
                                                <td><?php echo $row['transactions'] ?? 0; ?></td>
                                                <td><strong><?php echo formatCurrency($row['total_amount'] ?? 0); ?></strong></td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line font-24"></i>
                                <h5 class="mt-2">No Data Available</h5>
                                <p class="mb-0">No data found for the selected criteria.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

