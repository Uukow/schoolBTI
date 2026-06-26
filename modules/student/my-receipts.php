<?php
/**
 * My Receipts - Student Portal
 * 
 * View all payment receipts organized by month
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Receipts';

// Get current user and student record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$student = null;
$studentId = null;

if ($isPortalViewer) {
    $studentId = null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found. Please contact administrator to link your user account to a student record.';
        $studentId = null;
    } else {
        $studentId = $student['id'];
    }
}

// Get filters
$monthFilter = $_GET['month'] ?? date('Y-m');
$yearFilter = $_GET['year'] ?? date('Y');
$sessionFilter = $_GET['session_id'] ?? '';

$currentSession = getCurrentSession();

// Get academic sessions for filter
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

// Get receipts organized by month
$receiptsByMonth = [];
$allReceipts = [];

if (!$isPortalViewer && $studentId) {
    // Build query for all receipts
    $receiptsSql = "SELECT p.*, i.invoice_no, i.status as invoice_status, i.session_id,
                    sess.session_name,
                    u.username as received_by_name,
                    DATE_FORMAT(p.payment_date, '%Y-%m') as payment_month,
                    DATE_FORMAT(p.payment_date, '%M %Y') as month_name
                    FROM fee_payments p
                    INNER JOIN fee_invoices i ON p.invoice_id = i.id
                    LEFT JOIN academic_sessions sess ON i.session_id = sess.id
                    LEFT JOIN users u ON p.received_by = u.id
                    WHERE p.student_id = ?";
    
    $params = [$studentId];
    $types = 'i';
    
    if (!empty($monthFilter)) {
        $receiptsSql .= " AND DATE_FORMAT(p.payment_date, '%Y-%m') = ?";
        $params[] = $monthFilter;
        $types .= 's';
    } elseif (!empty($yearFilter)) {
        $receiptsSql .= " AND YEAR(p.payment_date) = ?";
        $params[] = $yearFilter;
        $types .= 'i';
    }
    
    if (!empty($sessionFilter)) {
        $receiptsSql .= " AND i.session_id = ?";
        $params[] = $sessionFilter;
        $types .= 'i';
    }
    
    $receiptsSql .= " ORDER BY p.payment_date DESC, p.id DESC";
    
    $allReceipts = fetchAll(executeQuery($receiptsSql, $types, $params));
    
    // Group receipts by month
    foreach ($allReceipts as $receipt) {
        $month = $receipt['payment_month'];
        if (!isset($receiptsByMonth[$month])) {
            $receiptsByMonth[$month] = [
                'month_name' => $receipt['month_name'],
                'receipts' => [],
                'total_amount' => 0
            ];
        }
        $receiptsByMonth[$month]['receipts'][] = $receipt;
        $receiptsByMonth[$month]['total_amount'] += $receipt['amount'];
    }
    
    // Sort months descending
    krsort($receiptsByMonth);
}

// Get statistics
$stats = [
    'total_receipts' => count($allReceipts),
    'total_amount' => array_sum(array_column($allReceipts, 'amount'))
];

// Generate month options for filter
$monthOptions = [];
$currentYear = date('Y');
for ($y = $currentYear; $y >= $currentYear - 3; $y--) {
    for ($m = 12; $m >= 1; $m--) {
        $monthValue = sprintf('%04d-%02d', $y, $m);
        $monthLabel = date('F Y', strtotime($monthValue . '-01'));
        $monthOptions[$monthValue] = $monthLabel;
    }
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
                            <button onclick="window.print()" class="btn btn-primary no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                        </div>
                        <h4 class="page-title">My Receipts</h4>
                        <div class="page-title-right">
                            <?php if (!$isPortalViewer && $student): ?>
                                <span class="text-muted"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$isPortalViewer && !$student): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h5><i class="ri-error-warning-line"></i> Student Profile Not Found</h5>
                        <p>Your user account is not linked to a student record. Please contact your administrator.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$isPortalViewer && $student): ?>
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-6 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-list-3-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Receipts</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_receipts']; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-info me-2">All Payments</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-money-dollar-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Amount</h5>
                            <h3 class="mt-3 mb-3"><?php echo formatCurrency($stats['total_amount']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">Total Paid</span>
                            </p>
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
                                <div class="col-md-4">
                                    <label class="form-label">Month</label>
                                    <select name="month" class="form-select">
                                        <option value="">All Months</option>
                                        <?php foreach ($monthOptions as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" 
                                                    <?php echo ($monthFilter == $value) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Academic Session</label>
                                    <select name="session_id" class="form-select">
                                        <option value="">All Sessions</option>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo $session['id']; ?>" 
                                                    <?php echo ($sessionFilter == $session['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="my-receipts.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipts by Month -->
            <?php if (empty($receiptsByMonth)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="ri-information-line"></i> No receipts found for the selected period.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($receiptsByMonth as $month => $monthData): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="header-title mb-0">
                                        <i class="ri-calendar-line"></i> <?php echo htmlspecialchars($monthData['month_name']); ?>
                                    </h4>
                                    <div>
                                        <span class="badge bg-success fs-6">
                                            <?php echo count($monthData['receipts']); ?> Receipt(s) - 
                                            <?php echo formatCurrency($monthData['total_amount']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Receipt No</th>
                                                <th>Date</th>
                                                <th>Invoice No</th>
                                                <th>Session</th>
                                                <th>Amount</th>
                                                <th>Payment Method</th>
                                                <th>Transaction ID</th>
                                                <th>Received By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($monthData['receipts'] as $receipt): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($receipt['receipt_no']); ?></strong></td>
                                                    <td><?php echo formatDate($receipt['payment_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($receipt['invoice_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($receipt['session_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="text-success fw-bold"><?php echo formatCurrency($receipt['amount']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($receipt['payment_method']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo !empty($receipt['transaction_id']) ? htmlspecialchars($receipt['transaction_id']) : '<span class="text-muted">-</span>'; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($receipt['received_by_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?php echo APP_URL; ?>modules/student/view-invoice.php?id=<?php echo $receipt['invoice_id']; ?>" 
                                                               class="btn btn-sm btn-info" title="View Invoice">
                                                                <i class="ri-eye-line"></i> Invoice
                                                            </a>
                                                            <button onclick="printReceipt('<?php echo htmlspecialchars($receipt['receipt_no']); ?>')" 
                                                                    class="btn btn-sm btn-secondary" title="Print Receipt">
                                                                <i class="ri-printer-line"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <th colspan="4" class="text-end">Monthly Total:</th>
                                                <th class="text-success"><?php echo formatCurrency($monthData['total_amount']); ?></th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd;
        page-break-inside: avoid;
        margin-bottom: 20px;
    }
}
</style>

<script>
function printReceipt(receiptNo) {
    // You can implement a receipt print view here
    window.open('<?php echo APP_URL; ?>modules/student/view-receipt.php?receipt_no=' + receiptNo, '_blank');
}

$(document).ready(function() {
    // Auto-expand filtered month section
    <?php if (!empty($monthFilter)): ?>
        $('.card').first().focus();
    <?php endif; ?>
});
</script>

