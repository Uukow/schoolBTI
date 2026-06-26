<?php
/**
 * Fee Payments Page
 * 
 * View and manage fee payments
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Fee Payments';

// Get current user and filters
$currentUser = getCurrentUser();
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$paymentMethod = $_GET['payment_method'] ?? '';

// Build query
$sql = "SELECT p.*, i.invoice_no, s.student_id, s.first_name, s.last_name, c.class_name,
        u.username as received_by_name
        FROM fee_payments p
        INNER JOIN fee_invoices i ON p.invoice_id = i.id
        INNER JOIN students s ON p.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        LEFT JOIN users u ON p.received_by = u.id
        WHERE p.payment_date BETWEEN ? AND ?";

$params = [$startDate, $endDate];
$types = 'ss';

if (!empty($paymentMethod)) {
    $sql .= " AND p.payment_method = ?";
    $params[] = $paymentMethod;
    $types .= 's';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY p.payment_date DESC, p.id DESC";

$payments = fetchAll(executeQuery($sql, $types, $params));

// Calculate statistics
$statsSql = "SELECT 
    COUNT(*) as total_payments,
    SUM(p.amount) as total_amount,
    COUNT(DISTINCT p.student_id) as students_count
    FROM fee_payments p
    INNER JOIN students s ON p.student_id = s.id
    WHERE p.payment_date BETWEEN ? AND ?";

$statsParams = [$startDate, $endDate];
$statsTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $statsSql .= " AND s.branch_id = ?";
    $statsParams[] = $currentUser['branch_id'];
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

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
                            <button onclick="exportTableToExcel('paymentsTable', 'fee_payments')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export
                            </button>
                        </div>
                        <h4 class="page-title">Fee Payments</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-file-list-3-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Payments</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_payments'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Amount</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_amount'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-user-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Students</h5>
                                    <h2 class="mb-0"><?php echo $stats['students_count'] ?? 0; ?></h2>
                                </div>
                            </div>
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
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" name="payment_method">
                                        <option value="">All Methods</option>
                                        <option value="Cash" <?php echo ($paymentMethod == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                                        <option value="Bank Transfer" <?php echo ($paymentMethod == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="Online" <?php echo ($paymentMethod == 'Online') ? 'selected' : ''; ?>>Online</option>
                                        <option value="EVC" <?php echo ($paymentMethod == 'EVC') ? 'selected' : ''; ?>>EVC</option>
                                        <option value="Zaad" <?php echo ($paymentMethod == 'Zaad') ? 'selected' : ''; ?>>Zaad</option>
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

            <!-- Payments List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Payment Records</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export" id="paymentsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Receipt No</th>
                                            <th>Invoice No</th>
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Payment Date</th>
                                            <th>Received By</th>
                                            <th>Transaction ID</th>
                                            <th class="no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($payment['receipt_no']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($payment['invoice_no']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($payment['student_id']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment['class_name']); ?></td>
                                            <td><strong><?php echo formatCurrency($payment['amount']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($payment['payment_method']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['received_by_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></td>
                                            <td class="no-print">
                                                <button onclick="viewReceipt(<?php echo $payment['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="View Receipt">
                                                    <i class="ri-file-text-line"></i>
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

<?php include '../../includes/footer.php'; ?>

<script>
function viewReceipt(paymentId) {
    window.open('<?php echo APP_URL; ?>modules/fees/view-receipt.php?id=' + paymentId, '_blank');
}
</script>

