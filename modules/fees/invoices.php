<?php
/**
 * Fee Invoices Management
 * 
 * Manage student fee invoices
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Fee Invoices';

// Get current user and filters
$currentUser = getCurrentUser();
$statusFilter = $_GET['status'] ?? '';
$classFilter = $_GET['class_id'] ?? '';

// Build query
$sql = "SELECT i.*, s.student_id, s.first_name, s.last_name, c.class_name, b.branch_name
        FROM fee_invoices i
        INNER JOIN students s ON i.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($statusFilter)) {
    $sql .= " AND i.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($classFilter)) {
    $sql .= " AND s.current_class_id = ?";
    $params[] = $classFilter;
    $types .= 'i';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY i.created_at DESC";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$invoices = fetchAll($stmt);

// Get classes for filter
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    COALESCE(SUM(i.net_amount), 0) as total_amount,
    COALESCE(SUM(i.paid_amount), 0) as total_paid,
    COALESCE(SUM(i.due_amount), 0) as total_due,
    COALESCE(SUM(CASE WHEN i.status = 'Unpaid' THEN 1 ELSE 0 END), 0) as unpaid_count,
    COALESCE(SUM(CASE WHEN i.status = 'Overdue' THEN 1 ELSE 0 END), 0) as overdue_count
    FROM fee_invoices i
    INNER JOIN students s ON i.student_id = s.id
    WHERE 1=1";

if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
    $statsSql .= " AND s.branch_id = " . $currentUser['branch_id'];
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateInvoiceModal">
                                <i class="ri-file-add-line"></i> Generate Invoice
                            </button>
                        </div>
                        <h4 class="page-title">Fee Invoices</h4>
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
                                    <h5 class="mt-0 mb-1 text-muted">Total Invoices</h5>
                                    <h2 class="mb-0"><?php echo number_format($stats['total']); ?></h2>
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
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Collected</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_paid']); ?></h2>
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
                                    <h5 class="mt-0 mb-1 text-muted">Total Due</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_due']); ?></h2>
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
                                        <i class="ri-error-warning-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Overdue</h5>
                                    <h2 class="mb-0"><?php echo $stats['overdue_count']; ?></h2>
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
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Unpaid" <?php echo ($statusFilter == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="Partially Paid" <?php echo ($statusFilter == 'Partially Paid') ? 'selected' : ''; ?>>Partially Paid</option>
                                        <option value="Paid" <?php echo ($statusFilter == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Overdue" <?php echo ($statusFilter == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Class</label>
                                    <select name="class_id" class="form-select">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="invoices.php" class="btn btn-secondary">
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
                            <h4 class="header-title mb-3">All Invoices (<?php echo count($invoices); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Total Amount</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($invoice['invoice_no']); ?></strong></td>
                                            <td>
                                                <?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($invoice['student_id']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($invoice['class_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo formatCurrency($invoice['net_amount']); ?></td>
                                            <td class="text-success"><strong><?php echo formatCurrency($invoice['paid_amount']); ?></strong></td>
                                            <td class="text-danger"><strong><?php echo formatCurrency($invoice['due_amount']); ?></strong></td>
                                            <td><?php echo $invoice['due_date'] ? formatDate($invoice['due_date']) : 'N/A'; ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($invoice['status']) {
                                                    case 'Paid': $statusClass = 'success'; break;
                                                    case 'Unpaid': $statusClass = 'warning'; break;
                                                    case 'Partially Paid': $statusClass = 'info'; break;
                                                    case 'Overdue': $statusClass = 'danger'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($invoice['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view-invoice.php?id=<?php echo $invoice['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View Invoice" target="_blank">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <?php if ($invoice['status'] != 'Paid'): ?>
                                                    <button onclick="recordPayment(<?php echo $invoice['id']; ?>, <?php echo $invoice['due_amount']; ?>)" 
                                                            class="btn btn-sm btn-success" title="Record Payment">
                                                        <i class="ri-money-dollar-circle-line"></i>
                                                    </button>
                                                    <button onclick="sendReminder(<?php echo $invoice['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Send Reminder">
                                                        <i class="ri-mail-send-line"></i>
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

<!-- Generate Invoice Modal -->
<div class="modal fade" id="generateInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Fee Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateInvoiceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select class="form-select" name="student_id" id="studentSelect" required>
                            <option value="">Select Student</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Fee Type</label>
                        <select class="form-select" name="fee_type_id" id="feeTypeSelect" required>
                            <option value="">Select Fee Type</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount</label>
                        <input type="number" class="form-control" name="discount" value="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Generate Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                <input type="hidden" name="invoice_id" id="paymentInvoiceId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Due Amount</label>
                        <input type="text" class="form-control" id="dueAmount" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Payment Amount</label>
                        <input type="number" class="form-control" name="amount" id="paymentAmount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Payment Method</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="EVC">EVC Plus</option>
                            <option value="Zaad">Zaad Service</option>
                            <option value="Mobile Money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" class="form-control" name="transaction_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Load students and fee types when modal opens
$('#generateInvoiceModal').on('shown.bs.modal', function() {
    // Load students
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/get-students.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Student</option>';
                response.data.forEach(function(student) {
                    options += `<option value="${student.id}">${student.student_id} - ${student.first_name} ${student.last_name}</option>`;
                });
                $('#studentSelect').html(options);
            }
        }
    });
    
    // Load fee types
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/get-fee-types.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Fee Type</option>';
                response.data.forEach(function(type) {
                    options += `<option value="${type.id}">${type.fee_name}</option>`;
                });
                $('#feeTypeSelect').html(options);
            }
        }
    });
});

// Generate invoice
$('#generateInvoiceForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/generate-invoice.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#generateInvoiceModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Record payment
function recordPayment(invoiceId, dueAmount) {
    $('#paymentInvoiceId').val(invoiceId);
    $('#dueAmount').val(formatCurrency(dueAmount));
    $('#paymentAmount').val(dueAmount);
    $('#paymentModal').modal('show');
}

$('#paymentForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/record-payment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#paymentModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Send reminder
function sendReminder(invoiceId) {
    confirmAction('Send payment reminder to parent via SMS/Email?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/fees/send-reminder.php',
            type: 'POST',
            data: { invoice_id: invoiceId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}
</script>

