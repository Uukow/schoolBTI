<?php
/**
 * My Fees - Student Portal
 * 
 * View fee invoices, payments, and financial information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Fees & Payments';

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

$currentSession = getCurrentSession();

// Get filter
$statusFilter = $_GET['status'] ?? '';
$sessionFilter = $_GET['session_id'] ?? $currentSession['id'] ?? '';

// Get financial statistics for student
$stats = [
    'total_invoices' => 0,
    'total_amount' => 0,
    'total_paid' => 0,
    'total_due' => 0,
    'unpaid_count' => 0,
    'overdue_count' => 0
];

if (!$isPortalViewer && $studentId) {
    // Calculate statistics
    $statsSql = "SELECT 
        COUNT(*) as total_invoices,
        COALESCE(SUM(net_amount), 0) as total_amount,
        COALESCE(SUM(paid_amount), 0) as total_paid,
        COALESCE(SUM(due_amount), 0) as total_due,
        SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) as unpaid_count,
        SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue_count
        FROM fee_invoices
        WHERE student_id = ?";
    
    if (!empty($sessionFilter)) {
        $statsSql .= " AND session_id = ?";
        $statsStmt = executeQuery($statsSql, 'ii', [$studentId, $sessionFilter]);
    } else {
        $statsStmt = executeQuery($statsSql, 'i', [$studentId]);
    }
    $stats = fetchOne($statsStmt);
}

// Get invoices for student
$invoices = [];
if (!$isPortalViewer && $studentId) {
    $invoiceSql = "SELECT i.*, sess.session_name
                   FROM fee_invoices i
                   LEFT JOIN academic_sessions sess ON i.session_id = sess.id
                   WHERE i.student_id = ?";
    
    $params = [$studentId];
    $types = 'i';
    
    if (!empty($sessionFilter)) {
        $invoiceSql .= " AND i.session_id = ?";
        $params[] = $sessionFilter;
        $types .= 'i';
    }
    
    if (!empty($statusFilter)) {
        $invoiceSql .= " AND i.status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    $invoiceSql .= " ORDER BY i.created_at DESC";
    
    $invoiceStmt = executeQuery($invoiceSql, $types, $params);
    $invoices = fetchAll($invoiceStmt);
}

// Get outstanding fees (monthly assignments + invoices)
$outstandingFees = [];
$totalOutstanding = 0;
if (!$isPortalViewer && $studentId) {
    // Get outstanding monthly fee assignments
    $feesSql = "SELECT mfa.*, ft.fee_name, ft.fee_code, c.class_name, sess.session_name
                FROM monthly_fee_assignments mfa
                INNER JOIN fee_types ft ON mfa.fee_type_id = ft.id
                LEFT JOIN classes c ON mfa.class_id = c.id
                LEFT JOIN academic_sessions sess ON mfa.session_id = sess.id
                WHERE mfa.student_id = ? AND mfa.due_amount > 0";
    
    $feesParams = [$studentId];
    $feesTypes = 'i';
    
    if (!empty($sessionFilter)) {
        $feesSql .= " AND mfa.session_id = ?";
        $feesParams[] = $sessionFilter;
        $feesTypes .= 'i';
    }
    
    $feesSql .= " ORDER BY mfa.due_date ASC, mfa.month ASC";
    $feesStmt = executeQuery($feesSql, $feesTypes, $feesParams);
    $monthlyFees = fetchAll($feesStmt);
    
    // Get outstanding invoices
    $invoiceOutstandingSql = "SELECT i.*, sess.session_name
                              FROM fee_invoices i
                              LEFT JOIN academic_sessions sess ON i.session_id = sess.id
                              WHERE i.student_id = ? AND i.due_amount > 0";
    
    $invOutParams = [$studentId];
    $invOutTypes = 'i';
    
    if (!empty($sessionFilter)) {
        $invoiceOutstandingSql .= " AND i.session_id = ?";
        $invOutParams[] = $sessionFilter;
        $invOutTypes .= 'i';
    }
    
    $invoiceOutstandingSql .= " ORDER BY i.due_date ASC";
    $invOutStmt = executeQuery($invoiceOutstandingSql, $invOutTypes, $invOutParams);
    $outstandingInvoices = fetchAll($invOutStmt);
    
    // Combine and format outstanding fees
    foreach ($monthlyFees as $fee) {
        $outstandingFees[] = [
            'type' => 'monthly',
            'fee_name' => $fee['fee_name'],
            'fee_code' => $fee['fee_code'],
            'month' => $fee['month'],
            'amount' => (float)$fee['due_amount'],
            'due_date' => $fee['due_date'],
            'status' => $fee['status'],
            'id' => $fee['id'],
            'session_name' => $fee['session_name'] ?? 'N/A'
        ];
        $totalOutstanding += (float)$fee['due_amount'];
    }
    
    foreach ($outstandingInvoices as $invoice) {
        $outstandingFees[] = [
            'type' => 'invoice',
            'fee_name' => 'Invoice Fees',
            'fee_code' => 'INV',
            'month' => date('Y-m', strtotime($invoice['created_at'])),
            'amount' => (float)$invoice['due_amount'],
            'due_date' => $invoice['due_date'],
            'status' => $invoice['status'],
            'id' => $invoice['id'],
            'invoice_no' => $invoice['invoice_no'],
            'session_name' => $invoice['session_name'] ?? 'N/A'
        ];
        $totalOutstanding += (float)$invoice['due_amount'];
    }
    
    // Sort by due date
    usort($outstandingFees, function($a, $b) {
        if ($a['due_date'] == $b['due_date']) return 0;
        if (empty($a['due_date'])) return 1;
        if (empty($b['due_date'])) return -1;
        return strtotime($a['due_date']) - strtotime($b['due_date']);
    });
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
                            <?php if (!$isPortalViewer && $student): ?>
                                <a href="<?php echo APP_URL; ?>modules/student/my-receipts.php" class="btn btn-success btn-sm me-2">
                                    <i class="ri-file-list-line"></i> View Receipts
                                </a>
                                <a href="<?php echo APP_URL; ?>modules/student/financial-statement.php" class="btn btn-info btn-sm me-2">
                                    <i class="ri-file-chart-line"></i> Financial Statement
                                </a>
                                <span class="text-muted"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">My Fees & Payments</h4>
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
            <!-- Financial Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-list-3-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Invoices</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_invoices'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-info me-2">All Sessions</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-money-dollar-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Amount</h5>
                            <h3 class="mt-3 mb-3"><?php echo formatCurrency($stats['total_amount'] ?? 0); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-primary me-2">Invoice Total</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-checkbox-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Paid</h5>
                            <h3 class="mt-3 mb-3"><?php echo formatCurrency($stats['total_paid'] ?? 0); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">Amount Paid</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-alert-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Outstanding</h5>
                            <h3 class="mt-3 mb-3"><?php echo formatCurrency($stats['total_due'] ?? 0); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-danger me-2">Amount Due</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outstanding Fees Section -->
            <?php if (!empty($outstandingFees)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">
                                    <i class="ri-alert-line text-warning"></i> Outstanding Fees
                                    <span class="badge bg-warning ms-2"><?php echo count($outstandingFees); ?> item(s)</span>
                                </h4>
                                <div>
                                    <strong class="text-danger me-3">Total Outstanding: <?php echo formatCurrency($totalOutstanding); ?></strong>
                                    <a href="<?php echo APP_URL; ?>modules/fees/flexible-payment.php?student_id=<?php echo $studentId; ?>" 
                                       class="btn btn-primary">
                                        <i class="ri-money-dollar-circle-line"></i> Make Payment
                                    </a>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Month/Period</th>
                                            <th>Session</th>
                                            <th>Due Date</th>
                                            <th>Amount Due</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($outstandingFees as $fee): ?>
                                        <tr class="<?php echo (!empty($fee['due_date']) && strtotime($fee['due_date']) < strtotime('today')) ? 'table-danger' : ''; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($fee['fee_name']); ?></strong>
                                                <?php if (!empty($fee['fee_code'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($fee['fee_code']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fee['type'] == 'monthly'): ?>
                                                    <?php echo date('F Y', strtotime($fee['month'] . '-01')); ?>
                                                <?php else: ?>
                                                    <?php echo date('F Y', strtotime($fee['month'] . '-01')); ?>
                                                    <?php if (!empty($fee['invoice_no'])): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($fee['invoice_no']); ?></small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($fee['session_name']); ?></td>
                                            <td>
                                                <?php if (!empty($fee['due_date'])): ?>
                                                    <?php echo formatDate($fee['due_date']); ?>
                                                    <?php if (strtotime($fee['due_date']) < strtotime('today')): ?>
                                                        <br><span class="badge bg-danger">Overdue</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-danger"><?php echo formatCurrency($fee['amount']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'warning';
                                                if ($fee['status'] == 'Overdue') $statusClass = 'danger';
                                                elseif ($fee['status'] == 'Partially Paid') $statusClass = 'info';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($fee['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($fee['type'] == 'invoice'): ?>
                                                    <a href="<?php echo APP_URL; ?>modules/student/view-invoice.php?id=<?php echo $fee['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View Invoice">
                                                        <i class="ri-eye-line"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo APP_URL; ?>modules/fees/flexible-payment.php?student_id=<?php echo $studentId; ?>" 
                                                       class="btn btn-sm btn-primary" title="Pay Fee">
                                                        <i class="ri-money-dollar-circle-line"></i> Pay
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-warning">
                                        <tr>
                                            <th colspan="4" class="text-end">Total Outstanding:</th>
                                            <th class="text-danger"><?php echo formatCurrency($totalOutstanding); ?></th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
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
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Unpaid" <?php echo ($statusFilter == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="Partially Paid" <?php echo ($statusFilter == 'Partially Paid') ? 'selected' : ''; ?>>Partially Paid</option>
                                        <option value="Paid" <?php echo ($statusFilter == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Overdue" <?php echo ($statusFilter == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="my-fees.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Fee Invoices (<?php echo count($invoices); ?>)</h4>
                            
                            <?php if (empty($invoices)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No invoices found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="invoices-table">
                                        <thead>
                                            <tr>
                                                <th>Invoice No</th>
                                                <th>Session</th>
                                                <th>Total Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Due Amount</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($invoices as $invoice): ?>
                                                <?php
                                                $isOverdue = $invoice['due_date'] && 
                                                           strtotime($invoice['due_date']) < time() && 
                                                           $invoice['status'] != 'Paid';
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($invoice['invoice_no']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($invoice['session_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo formatCurrency($invoice['net_amount']); ?></td>
                                                    <td>
                                                        <span class="text-success"><?php echo formatCurrency($invoice['paid_amount']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($invoice['due_amount'] > 0): ?>
                                                            <span class="text-danger"><strong><?php echo formatCurrency($invoice['due_amount']); ?></strong></span>
                                                        <?php else: ?>
                                                            <span class="text-success"><?php echo formatCurrency(0); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $invoice['due_date'] ? formatDate($invoice['due_date']) : 'N/A'; ?>
                                                        <?php if ($isOverdue): ?>
                                                            <br><small class="text-danger">Overdue</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusClass = 'secondary';
                                                        switch($invoice['status']) {
                                                            case 'Paid': $statusClass = 'success'; break;
                                                            case 'Partially Paid': $statusClass = 'info'; break;
                                                            case 'Unpaid': $statusClass = 'warning'; break;
                                                            case 'Overdue': $statusClass = 'danger'; break;
                                                            case 'Waived': $statusClass = 'dark'; break;
                                                        }
                                                        ?>
                                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                                            <?php echo htmlspecialchars($invoice['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/student/view-invoice.php?id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="View Invoice">
                                                            <i class="ri-eye-line"></i> View
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
    $('#invoices-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']]
    });
});
</script>

