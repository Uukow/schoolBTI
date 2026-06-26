<?php
/**
 * My Payments - Student Portal
 * 
 * View payment history and receipts
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Student', 'Super Admin'], APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Payment History';

// Get current user and student record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$student = null;
$studentId = null;

if ($isSuperAdmin) {
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
$startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-6 months'));
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$paymentMethod = $_GET['payment_method'] ?? '';
$sessionFilter = $_GET['session_id'] ?? '';

$currentSession = getCurrentSession();

// Get payment history for student
$payments = [];
$stats = [
    'total_payments' => 0,
    'total_amount' => 0
];

if (!$isSuperAdmin && $studentId) {
    // Build query
    $sql = "SELECT p.*, i.invoice_no, i.status as invoice_status, i.session_id,
            sess.session_name,
            u.username as received_by_name
            FROM fee_payments p
            INNER JOIN fee_invoices i ON p.invoice_id = i.id
            LEFT JOIN academic_sessions sess ON i.session_id = sess.id
            LEFT JOIN users u ON p.received_by = u.id
            WHERE p.student_id = ? AND p.payment_date BETWEEN ? AND ?";
    
    $params = [$studentId, $startDate, $endDate];
    $types = 'iss';
    
    if (!empty($paymentMethod)) {
        $sql .= " AND p.payment_method = ?";
        $params[] = $paymentMethod;
        $types .= 's';
    }
    
    if (!empty($sessionFilter)) {
        $sql .= " AND i.session_id = ?";
        $params[] = $sessionFilter;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY p.payment_date DESC, p.id DESC";
    
    $payments = fetchAll(executeQuery($sql, $types, $params));
    
    // Calculate statistics
    $statsSql = "SELECT 
        COUNT(*) as total_payments,
        SUM(p.amount) as total_amount
        FROM fee_payments p
        INNER JOIN fee_invoices i ON p.invoice_id = i.id
        WHERE p.student_id = ? AND p.payment_date BETWEEN ? AND ?";
    
    $statsParams = [$studentId, $startDate, $endDate];
    $statsTypes = 'iss';
    
    if (!empty($paymentMethod)) {
        $statsSql .= " AND p.payment_method = ?";
        $statsParams[] = $paymentMethod;
        $statsTypes .= 's';
    }
    
    if (!empty($sessionFilter)) {
        $statsSql .= " AND i.session_id = ?";
        $statsParams[] = $sessionFilter;
        $statsTypes .= 'i';
    }
    
    $stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));
}

// Get academic sessions for filter
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

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
                            <?php if (!$isSuperAdmin && $student): ?>
                                <a href="<?php echo APP_URL; ?>modules/student/my-receipts.php" class="btn btn-success btn-sm me-2">
                                    <i class="ri-file-list-line"></i> View Receipts
                                </a>
                                <a href="<?php echo APP_URL; ?>modules/student/financial-statement.php" class="btn btn-info btn-sm me-2">
                                    <i class="ri-file-chart-line"></i> Financial Statement
                                </a>
                                <span class="text-muted"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">My Payment History</h4>
                    </div>
                </div>
            </div>

            <?php if (!$isSuperAdmin && !$student): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h5><i class="ri-error-warning-line"></i> Student Profile Not Found</h5>
                        <p>Your user account is not linked to a student record. Please contact your administrator.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$isSuperAdmin && $student): ?>
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-6 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-money-dollar-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Payments</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_payments'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-info me-2">Payments Recorded</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-checkbox-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Amount Paid</h5>
                            <h3 class="mt-3 mb-3"><?php echo formatCurrency($stats['total_amount'] ?? 0); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">Amount Paid</span>
                            </p>
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
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                                </div>
                                <div class="col-md-2">
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
                                <div class="col-md-2">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">All Methods</option>
                                        <option value="Cash" <?php echo ($paymentMethod == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                                        <option value="Bank Transfer" <?php echo ($paymentMethod == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="Credit Card" <?php echo ($paymentMethod == 'Credit Card') ? 'selected' : ''; ?>>Credit Card</option>
                                        <option value="Debit Card" <?php echo ($paymentMethod == 'Debit Card') ? 'selected' : ''; ?>>Debit Card</option>
                                        <option value="Online" <?php echo ($paymentMethod == 'Online') ? 'selected' : ''; ?>>Online</option>
                                        <option value="EVC" <?php echo ($paymentMethod == 'EVC') ? 'selected' : ''; ?>>EVC</option>
                                        <option value="Zaad" <?php echo ($paymentMethod == 'Zaad') ? 'selected' : ''; ?>>Zaad</option>
                                        <option value="Mobile Money" <?php echo ($paymentMethod == 'Mobile Money') ? 'selected' : ''; ?>>Mobile Money</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="my-payments.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Payment History (<?php echo count($payments); ?>)</h4>
                            
                            <?php if (empty($payments)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No payments found for the selected period.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="payments-table">
                                        <thead>
                                            <tr>
                                                <th>Receipt No</th>
                                                <th>Invoice No</th>
                                                <th>Session</th>
                                                <th>Payment Date</th>
                                                <th>Amount</th>
                                                <th>Payment Method</th>
                                                <th>Transaction ID</th>
                                                <th>Received By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($payment['receipt_no']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($payment['invoice_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['session_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo formatDate($payment['payment_date']); ?></td>
                                                    <td>
                                                        <span class="text-success fw-bold"><?php echo formatCurrency($payment['amount']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($payment['payment_method']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo !empty($payment['transaction_id']) ? htmlspecialchars($payment['transaction_id']) : '<span class="text-muted">-</span>'; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($payment['received_by_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/student/view-invoice.php?id=<?php echo $payment['invoice_id']; ?>" 
                                                           class="btn btn-sm btn-info" title="View Invoice">
                                                            <i class="ri-eye-line"></i> View Invoice
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<script>
$(document).ready(function() {
    $('#payments-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[3, 'desc']]
    });
});
</script>

