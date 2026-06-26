<?php
/**
 * View Payment Receipt
 * 
 * Display payment receipt for printing
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Payment Receipt';

// Get payment ID from URL
$paymentId = $_GET['id'] ?? 0;

if (empty($paymentId)) {
    $_SESSION['error'] = 'Invalid payment ID';
    redirect(APP_URL . 'modules/fees/payments.php');
}

// Get current user for branch filtering
$currentUser = getCurrentUser();

// Fetch payment details with all related information
$sql = "SELECT p.*, 
        i.invoice_no, i.status as invoice_status, i.session_id,
        s.student_id, s.first_name, s.last_name, s.middle_name, s.photo,
        s.address, s.city, s.state, s.phone, s.email,
        c.class_name, sec.section_name,
        b.branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email,
        sess.session_name,
        u.username as received_by_name
        FROM fee_payments p
        INNER JOIN fee_invoices i ON p.invoice_id = i.id
        INNER JOIN students s ON p.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        LEFT JOIN sections sec ON s.current_section_id = sec.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN academic_sessions sess ON i.session_id = sess.id
        LEFT JOIN users u ON p.received_by = u.id
        WHERE p.id = ?";

$params = [$paymentId];
$types = 'i';

// Branch filter for non-super admins
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$payment = fetchOne($stmt);

if (!$payment) {
    $_SESSION['error'] = 'Payment receipt not found or access denied';
    redirect(APP_URL . 'modules/fees/payments.php');
}

// Get payment allocations (for flexible payments)
$allocationsSql = "SELECT pa.*, mfa.month, ft.fee_name
                   FROM payment_allocations pa
                   LEFT JOIN monthly_fee_assignments mfa ON pa.reference_id = mfa.id AND pa.allocation_type = 'Monthly Assignment'
                   LEFT JOIN fee_types ft ON mfa.fee_type_id = ft.id
                   WHERE pa.payment_id = ?
                   ORDER BY pa.id";
$allocations = fetchAll(executeQuery($allocationsSql, 'i', [$paymentId]));

// Get invoice items
$itemsSql = "SELECT ii.*, ft.fee_name, ft.fee_code
             FROM fee_invoice_items ii
             INNER JOIN fee_types ft ON ii.fee_type_id = ft.id
             WHERE ii.invoice_id = ?
             ORDER BY ii.id";
$items = fetchAll(executeQuery($itemsSql, 'i', [$payment['invoice_id']]));

include '../../includes/header.php';
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .receipt-container {
        box-shadow: none;
        border: none;
        padding: 0;
    }
    body {
        background: white;
        padding: 0;
    }
    @page {
        margin: 1cm;
    }
}
.receipt-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.receipt-header {
    border-bottom: 3px solid #4a90e2;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.receipt-title {
    font-size: 36px;
    font-weight: bold;
    color: #4a90e2;
    margin-bottom: 10px;
}
.receipt-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}
.receipt-section {
    margin-bottom: 25px;
}
.receipt-section h5 {
    color: #4a90e2;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 8px;
    margin-bottom: 15px;
}
.receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.receipt-table th,
.receipt-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}
.receipt-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}
.receipt-table tr:last-child td {
    border-bottom: none;
}
.receipt-total {
    background-color: #f8f9fa;
    font-weight: bold;
    font-size: 18px;
}
.receipt-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
    text-align: center;
    color: #666;
    font-size: 14px;
}
.signature-section {
    margin-top: 50px;
    display: flex;
    justify-content: space-between;
}
.signature-box {
    text-align: center;
    width: 200px;
}
.signature-line {
    border-top: 1px solid #333;
    margin-top: 50px;
    padding-top: 5px;
}
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Print Actions -->
            <div class="row no-print mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?php echo APP_URL; ?>modules/fees/payments.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Back to Payments
                        </a>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="ri-printer-line"></i> Print Receipt
                            </button>
                            <button onclick="downloadReceipt()" class="btn btn-success ms-2">
                                <i class="ri-download-line"></i> Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Container -->
            <div class="receipt-container">
                
                <!-- Header -->
                <div class="receipt-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="receipt-title">PAYMENT RECEIPT</h2>
                            <p class="mb-0"><strong>Receipt No:</strong> <?php echo htmlspecialchars($payment['receipt_no']); ?></p>
                            <p class="mb-0"><strong>Date:</strong> <?php echo formatDateTime($payment['payment_date']); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if (!empty($payment['branch_name'])): ?>
                            <h5><strong><?php echo htmlspecialchars($payment['branch_name']); ?></strong></h5>
                            <?php if (!empty($payment['branch_address'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($payment['branch_address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payment['branch_phone'])): ?>
                            <p class="mb-1">Phone: <?php echo htmlspecialchars($payment['branch_phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payment['branch_email'])): ?>
                            <p class="mb-0">Email: <?php echo htmlspecialchars($payment['branch_email']); ?></p>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Student Information -->
                <div class="receipt-section">
                    <h5>Student Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($payment['first_name'] . ' ' . ($payment['middle_name'] ? $payment['middle_name'] . ' ' : '') . $payment['last_name']); ?></p>
                            <p class="mb-1"><strong>Student ID:</strong> <?php echo htmlspecialchars($payment['student_id']); ?></p>
                            <p class="mb-1"><strong>Class:</strong> <?php echo htmlspecialchars($payment['class_name'] ?? 'N/A'); ?></p>
                            <?php if (!empty($payment['section_name'])): ?>
                            <p class="mb-1"><strong>Section:</strong> <?php echo htmlspecialchars($payment['section_name']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($payment['address'])): ?>
                            <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($payment['address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payment['phone'])): ?>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($payment['phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payment['email'])): ?>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($payment['email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="receipt-section">
                    <h5>Payment Details</h5>
                    <table class="receipt-table">
                        <tr>
                            <th width="40%">Description</th>
                            <th width="20%" class="text-end">Amount</th>
                        </tr>
                        <?php if (!empty($allocations)): ?>
                            <?php foreach ($allocations as $allocation): ?>
                            <tr>
                                <td>
                                    <?php if ($allocation['allocation_type'] == 'Monthly Assignment'): ?>
                                        <?php echo htmlspecialchars($allocation['fee_name'] ?? 'Fee Payment'); ?>
                                        <?php if (!empty($allocation['month'])): ?>
                                            - <?php echo date('F Y', strtotime($allocation['month'] . '-01')); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Payment Allocation
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo formatCurrency($allocation['amount']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php elseif (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['fee_name']); ?> (<?php echo htmlspecialchars($item['fee_code']); ?>)</td>
                                <td class="text-end"><?php echo formatCurrency($item['amount']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td>Fee Payment</td>
                                <td class="text-end"><?php echo formatCurrency($payment['amount']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="receipt-total">
                            <td><strong>Total Amount Paid</strong></td>
                            <td class="text-end"><strong><?php echo formatCurrency($payment['amount']); ?></strong></td>
                        </tr>
                    </table>
                </div>

                <!-- Payment Method -->
                <div class="receipt-section">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Payment Method:</strong> 
                                <span class="badge bg-info"><?php echo htmlspecialchars($payment['payment_method']); ?></span>
                            </p>
                            <?php if (!empty($payment['transaction_id'])): ?>
                            <p class="mb-1"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                            <?php endif; ?>
                            <p class="mb-1"><strong>Invoice No:</strong> <?php echo htmlspecialchars($payment['invoice_no']); ?></p>
                            <p class="mb-1"><strong>Academic Session:</strong> <?php echo htmlspecialchars($payment['session_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Received By:</strong> <?php echo htmlspecialchars($payment['received_by_name'] ?? 'N/A'); ?></p>
                            <p class="mb-1"><strong>Payment Date:</strong> <?php echo formatDateTime($payment['payment_date']); ?></p>
                            <?php if (!empty($payment['remarks'])): ?>
                            <p class="mb-1"><strong>Remarks:</strong> <?php echo htmlspecialchars($payment['remarks']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="receipt-footer">
                    <p class="mb-2"><strong>Thank you for your payment!</strong></p>
                    <p class="mb-0">This is a computer-generated receipt. No signature required.</p>
                    <p class="mb-0">Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                </div>

            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function downloadReceipt() {
    // Open print dialog which can be saved as PDF
    window.print();
}
</script>

