<?php
/**
 * Financial Statement - Student Portal
 * 
 * View comprehensive financial statement with summary by month and session
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'Financial Statement';

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
$sessionFilter = $_GET['session_id'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$currentSession = getCurrentSession();

// Get academic sessions for filter
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

// Financial ledger data
$ledgerEntries = [];
$financialSummary = [
    'total_charges' => 0,
    'total_receipts' => 0,
    'opening_balance' => 0,
    'closing_balance' => 0
];

// Get financial ledger data
if (!$isPortalViewer && $studentId) {
    // Build ledger query - combine ledger entries with invoice numbers and receipt numbers
    $ledgerSql = "SELECT 
            l.id,
            l.created_at as transaction_date,
            l.transaction_type,
            l.debit_amount as charge,
            l.credit_amount as receipt,
            l.balance,
            l.description,
            l.month,
            l.reference_type,
            l.reference_id,
            sess.session_name,
            sess.id as session_id
            FROM student_fee_ledger l
            INNER JOIN academic_sessions sess ON l.session_id = sess.id
            WHERE l.student_id = ?";
    
    $ledgerParams = [$studentId];
    $ledgerTypes = 'i';
    
    if (!empty($sessionFilter)) {
        $ledgerSql .= " AND l.session_id = ?";
        $ledgerParams[] = $sessionFilter;
        $ledgerTypes .= 'i';
    }
    
    if (!empty($dateFrom)) {
        $ledgerSql .= " AND DATE(l.created_at) >= ?";
        $ledgerParams[] = $dateFrom;
        $ledgerTypes .= 's';
    }
    
    if (!empty($dateTo)) {
        $ledgerSql .= " AND DATE(l.created_at) <= ?";
        $ledgerParams[] = $dateTo;
        $ledgerTypes .= 's';
    }
    
    $ledgerSql .= " ORDER BY l.created_at ASC, l.id ASC";
    
    $ledgerEntries = fetchAll(executeQuery($ledgerSql, $ledgerTypes, $ledgerParams));
    
    // Enhance entries with invoice/receipt numbers
    foreach ($ledgerEntries as &$entry) {
        $entry['invoice_no'] = null;
        
        if ($entry['reference_type'] == 'payment' && !empty($entry['reference_id'])) {
            // Get receipt number for payments
            $receiptSql = "SELECT receipt_no FROM fee_payments WHERE id = ?";
            $receiptStmt = executeQuery($receiptSql, 'i', [$entry['reference_id']]);
            $receipt = fetchOne($receiptStmt);
            $entry['invoice_no'] = $receipt ? $receipt['receipt_no'] : null;
        } elseif ($entry['reference_type'] == 'monthly_assignment' && !empty($entry['reference_id'])) {
            // Get invoice number for assignments
            $invoiceSql = "SELECT fi.invoice_no 
                          FROM fee_invoices fi 
                          INNER JOIN monthly_fee_assignments mfa ON fi.id = mfa.invoice_id 
                          WHERE mfa.id = ? 
                          LIMIT 1";
            $invoiceStmt = executeQuery($invoiceSql, 'i', [$entry['reference_id']]);
            $invoice = fetchOne($invoiceStmt);
            $entry['invoice_no'] = $invoice ? $invoice['invoice_no'] : null;
        }
    }
    unset($entry);
    
    // Calculate summary
    if (!empty($ledgerEntries)) {
        $financialSummary['total_charges'] = array_sum(array_column($ledgerEntries, 'charge'));
        $financialSummary['total_receipts'] = array_sum(array_column($ledgerEntries, 'receipt'));
        
        // Opening balance (balance before first entry in date range)
        if (!empty($dateFrom)) {
            $openingSql = "SELECT balance FROM student_fee_ledger 
                          WHERE student_id = ? AND DATE(created_at) < ?";
            $openingParams = [$studentId, $dateFrom];
            $openingTypes = 'is';
            
            if (!empty($sessionFilter)) {
                $openingSql .= " AND session_id = ?";
                $openingParams[] = $sessionFilter;
                $openingTypes .= 'i';
            }
            
            $openingSql .= " ORDER BY created_at DESC, id DESC LIMIT 1";
            $openingStmt = executeQuery($openingSql, $openingTypes, $openingParams);
            $openingRow = fetchOne($openingStmt);
            $financialSummary['opening_balance'] = $openingRow ? (float)$openingRow['balance'] : 0;
        } else {
            // If no date filter, opening balance is 0
            $financialSummary['opening_balance'] = 0;
        }
        
        // Closing balance is the last entry's balance
        $lastEntry = end($ledgerEntries);
        $financialSummary['closing_balance'] = $lastEntry ? (float)$lastEntry['balance'] : 0;
    }
    
    // Get outstanding fees (monthly assignments + invoices)
    $outstandingFees = [];
    $totalOutstanding = 0;
    
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
} else {
    $allInvoices = [];
    $outstandingFees = [];
    $totalOutstanding = 0;
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
                                <i class="ri-printer-line"></i> Print Statement
                            </button>
                        </div>
                        <h4 class="page-title">Financial Statement</h4>
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
            <div class="row no-print">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <label class="form-label">Date From</label>
                                    <input type="date" name="date_from" class="form-control" 
                                           value="<?php echo htmlspecialchars($dateFrom); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date To</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="<?php echo htmlspecialchars($dateTo); ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="financial-statement.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-4">Financial Summary</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="text-muted">Opening Balance</label>
                                        <h3 class="<?php echo ($financialSummary['opening_balance'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo formatCurrency($financialSummary['opening_balance']); ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="text-muted">Total Charges</label>
                                        <h3 class="text-danger"><?php echo formatCurrency($financialSummary['total_charges']); ?></h3>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="text-muted">Total Receipts</label>
                                        <h3 class="text-success"><?php echo formatCurrency($financialSummary['total_receipts']); ?></h3>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="text-muted">Closing Balance</label>
                                        <h3 class="<?php echo ($financialSummary['closing_balance'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo formatCurrency($financialSummary['closing_balance']); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($dateFrom) || !empty($dateTo)): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <strong>Period:</strong> 
                                        <?php echo $dateFrom ? formatDate($dateFrom) : 'Beginning'; ?> 
                                        to 
                                        <?php echo $dateTo ? formatDate($dateTo) : 'Today'; ?>
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Ledger -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Financial Statement</h4>
                            
                            <?php if (empty($ledgerEntries)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No transactions found for the selected period.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="ledger-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Invoice No / Receipt No</th>
                                                <th>Session</th>
                                                <th>Charge</th>
                                                <th>Receipt</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ledgerEntries as $entry): ?>
                                                <tr>
                                                    <td><?php echo formatDate($entry['transaction_date']); ?></td>
                                                    <td>
                                                        <?php if (!empty($entry['invoice_no'])): ?>
                                                            <strong><?php echo htmlspecialchars($entry['invoice_no']); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($entry['session_name'] ?? 'N/A'); ?></td>
                                                    <td class="text-danger">
                                                        <?php if ($entry['charge'] > 0): ?>
                                                            <strong><?php echo formatCurrency($entry['charge']); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-success">
                                                        <?php if ($entry['receipt'] > 0): ?>
                                                            <strong><?php echo formatCurrency($entry['receipt']); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong class="<?php echo ($entry['balance'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo formatCurrency($entry['balance']); ?>
                                                        </strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-active">
                                            <tr>
                                                <th colspan="3" class="text-end">Totals:</th>
                                                <th class="text-danger"><?php echo formatCurrency($financialSummary['total_charges']); ?></th>
                                                <th class="text-success"><?php echo formatCurrency($financialSummary['total_receipts']); ?></th>
                                                <th class="<?php echo ($financialSummary['closing_balance'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo formatCurrency($financialSummary['closing_balance']); ?>
                                                </th>
                                            </tr>
                                        </tfoot>
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

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd;
        page-break-inside: avoid;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#ledger-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });
});
</script>

