<?php
/**
 * View Invoice - Student Portal
 * 
 * Display detailed invoice information with payment history for students
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'View Invoice';

// Get current user and student record
$currentUser = getCurrentUser();
$isPortalViewer = isPortalAdminViewer();

$student = null;
$studentId = null;

if (!$isPortalViewer) {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found';
        redirect(APP_URL . 'modules/student/dashboard.php');
    }
    $studentId = $student['id'];
}

// Get invoice ID from URL
$invoiceId = $_GET['id'] ?? 0;

if (empty($invoiceId)) {
    $_SESSION['error'] = 'Invalid invoice ID';
    redirect(APP_URL . 'modules/student/my-fees.php');
}

// Fetch invoice details with student and session information
$sql = "SELECT i.*, 
        s.student_id, s.first_name, s.last_name, s.middle_name, s.photo,
        s.address, s.city, s.state, s.phone, s.email,
        c.class_name, sec.section_name,
        b.branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email,
        sess.session_name,
        u.username as generated_by_name
        FROM fee_invoices i
        INNER JOIN students s ON i.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        LEFT JOIN sections sec ON s.current_section_id = sec.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN academic_sessions sess ON i.session_id = sess.id
        LEFT JOIN users u ON i.generated_by = u.id
        WHERE i.id = ?";

$params = [$invoiceId];
$types = 'i';

// Security check: Only allow students to view their own invoices
if (!$isPortalViewer && $studentId) {
    $sql .= " AND i.student_id = ?";
    $params[] = $studentId;
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$invoice = fetchOne($stmt);

if (!$invoice) {
    $_SESSION['error'] = 'Invoice not found or access denied';
    redirect(APP_URL . 'modules/student/my-fees.php');
}

// Fetch invoice items
$itemsSql = "SELECT ii.*, ft.fee_name, ft.fee_code
             FROM fee_invoice_items ii
             INNER JOIN fee_types ft ON ii.fee_type_id = ft.id
             WHERE ii.invoice_id = ?
             ORDER BY ii.id";
$items = fetchAll(executeQuery($itemsSql, 'i', [$invoiceId]));

// Fetch payment history
$paymentsSql = "SELECT p.*, u.username as received_by_name
                FROM fee_payments p
                LEFT JOIN users u ON p.received_by = u.id
                WHERE p.invoice_id = ?
                ORDER BY p.payment_date DESC, p.created_at DESC";
$payments = fetchAll(executeQuery($paymentsSql, 'i', [$invoiceId]));

// Get parent information for contact
$parentSql = "SELECT p.*, sp.relationship, sp.is_primary
              FROM parents p
              INNER JOIN student_parents sp ON p.id = sp.parent_id
              WHERE sp.student_id = ? AND sp.is_primary = 1
              LIMIT 1";
$parent = fetchOne(executeQuery($parentSql, 'i', [$invoice['student_id']]));

include '../../includes/header.php';
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .invoice-container {
        box-shadow: none;
        border: none;
    }
    body {
        background: white;
    }
}
.invoice-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.invoice-header {
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.invoice-title {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}
.invoice-meta {
    color: #666;
    font-size: 14px;
}
.invoice-body {
    margin-bottom: 30px;
}
.invoice-section {
    margin-bottom: 25px;
}
.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}
.info-row {
    display: flex;
    margin-bottom: 10px;
}
.info-label {
    width: 150px;
    font-weight: 600;
    color: #666;
}
.info-value {
    flex: 1;
    color: #333;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.items-table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
}
.items-table td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
}
.items-table tr:last-child td {
    border-bottom: none;
}
.total-section {
    margin-top: 20px;
    text-align: right;
}
.total-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 8px;
    padding: 8px 0;
}
.total-label {
    width: 200px;
    text-align: right;
    font-weight: 600;
    padding-right: 15px;
}
.total-value {
    width: 150px;
    text-align: right;
    font-weight: 600;
}
.grand-total {
    border-top: 2px solid #333;
    padding-top: 10px;
    margin-top: 10px;
    font-size: 18px;
}
.payment-history {
    margin-top: 30px;
}
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}
.status-paid {
    background: #d4edda;
    color: #155724;
}
.status-unpaid {
    background: #fff3cd;
    color: #856404;
}
.status-partial {
    background: #d1ecf1;
    color: #0c5460;
}
.status-overdue {
    background: #f8d7da;
    color: #721c24;
}
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Action Buttons -->
            <div class="row no-print">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="ri-printer-line"></i> Print Invoice
                            </button>
                            <a href="<?php echo APP_URL; ?>modules/student/my-fees.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to My Fees
                            </a>
                        </div>
                        <h4 class="page-title">Invoice Details</h4>
                    </div>
                </div>
            </div>

            <!-- Invoice Container -->
            <div class="row">
                <div class="col-12">
                    <div class="invoice-container">
                        
                        <!-- Invoice Header -->
                        <div class="invoice-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="invoice-title">INVOICE</div>
                                    <div class="invoice-meta">
                                        Invoice No: <strong><?php echo htmlspecialchars($invoice['invoice_no']); ?></strong><br>
                                        Date: <?php echo formatDate($invoice['created_at']); ?><br>
                                        <?php if ($invoice['due_date']): ?>
                                        Due Date: <strong><?php echo formatDate($invoice['due_date']); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h5><?php echo htmlspecialchars($invoice['branch_name'] ?? 'School'); ?></h5>
                                    <?php if (!empty($invoice['branch_address'])): ?>
                                    <p class="mb-1"><?php echo htmlspecialchars($invoice['branch_address']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($invoice['branch_phone'])): ?>
                                    <p class="mb-1">Phone: <?php echo htmlspecialchars($invoice['branch_phone']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($invoice['branch_email'])): ?>
                                    <p class="mb-0">Email: <?php echo htmlspecialchars($invoice['branch_email']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Body -->
                        <div class="invoice-body">
                            
                            <!-- Student Information -->
                            <div class="invoice-section">
                                <div class="section-title">Bill To</div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Student Name:</div>
                                            <div class="info-value">
                                                <strong><?php echo htmlspecialchars($invoice['first_name'] . ' ' . 
                                                    ($invoice['middle_name'] ? $invoice['middle_name'] . ' ' : '') . 
                                                    $invoice['last_name']); ?></strong>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Student ID:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['student_id']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Class:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['class_name'] ?? 'N/A'); ?></div>
                                        </div>
                                        <?php if (!empty($invoice['section_name'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Section:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['section_name']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($invoice['session_name'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Session:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['session_name']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (!empty($invoice['address'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Address:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['address']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($invoice['phone'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Phone:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['phone']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($invoice['email'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Email:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($invoice['email']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($parent)): ?>
                                        <div class="info-row">
                                            <div class="info-label">Parent:</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?>
                                                <?php if (!empty($parent['phone'])): ?>
                                                    <br><small>Phone: <?php echo htmlspecialchars($parent['phone']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="invoice-section">
                                <div class="section-title">Invoice Items</div>
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fee Type</th>
                                            <th>Description</th>
                                            <th style="text-align: right;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['fee_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['fee_code']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['description'] ?? '-'); ?></td>
                                                <td style="text-align: right;"><?php echo formatCurrency($item['amount']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No items found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals -->
                            <div class="total-section">
                                <div class="total-row">
                                    <div class="total-label">Subtotal:</div>
                                    <div class="total-value"><?php echo formatCurrency($invoice['total_amount']); ?></div>
                                </div>
                                <?php if ($invoice['discount'] > 0): ?>
                                <div class="total-row">
                                    <div class="total-label">Discount:</div>
                                    <div class="total-value" style="color: #28a745;">-<?php echo formatCurrency($invoice['discount']); ?></div>
                                </div>
                                <?php endif; ?>
                                <div class="total-row grand-total">
                                    <div class="total-label">Total Amount:</div>
                                    <div class="total-value"><?php echo formatCurrency($invoice['net_amount']); ?></div>
                                </div>
                                <div class="total-row">
                                    <div class="total-label">Paid Amount:</div>
                                    <div class="total-value" style="color: #28a745;"><?php echo formatCurrency($invoice['paid_amount']); ?></div>
                                </div>
                                <div class="total-row grand-total">
                                    <div class="total-label">Due Amount:</div>
                                    <div class="total-value" style="color: #dc3545; font-size: 20px;">
                                        <?php echo formatCurrency($invoice['due_amount']); ?>
                                    </div>
                                </div>
                                <div class="total-row mt-3">
                                    <div class="total-label">Status:</div>
                                    <div class="total-value">
                                        <?php
                                        $statusClass = 'status-unpaid';
                                        switch($invoice['status']) {
                                            case 'Paid': $statusClass = 'status-paid'; break;
                                            case 'Unpaid': $statusClass = 'status-unpaid'; break;
                                            case 'Partially Paid': $statusClass = 'status-partial'; break;
                                            case 'Overdue': $statusClass = 'status-overdue'; break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($invoice['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment History -->
                            <?php if (!empty($payments)): ?>
                            <div class="payment-history">
                                <div class="section-title">Payment History</div>
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th>Receipt No</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th>Received By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($payment['receipt_no']); ?></strong></td>
                                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                                            <td style="text-align: right; color: #28a745; font-weight: 600;">
                                                <?php echo formatCurrency($payment['amount']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($payment['received_by_name'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            <!-- Footer Notes -->
                            <div class="mt-4 pt-4 border-top">
                                <p class="text-muted small mb-0">
                                    <strong>Note:</strong> This is a computer-generated invoice. No signature required.
                                    <?php if ($invoice['generated_by_name']): ?>
                                    Generated by: <?php echo htmlspecialchars($invoice['generated_by_name']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

