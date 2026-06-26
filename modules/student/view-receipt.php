<?php
/**
 * View Receipt - Student Portal
 * 
 * Display payment receipt for printing
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'Payment Receipt';

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

// Get receipt number from URL
$receiptNo = $_GET['receipt_no'] ?? '';

if (empty($receiptNo)) {
    $_SESSION['error'] = 'Invalid receipt number';
    redirect(APP_URL . 'modules/student/my-receipts.php');
}

// Fetch receipt details
$sql = "SELECT p.*, 
        i.invoice_no, i.status as invoice_status,
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
        WHERE p.receipt_no = ?";

$params = [$receiptNo];
$types = 's';

// Security check: Only allow students to view their own receipts
if (!$isPortalViewer && $studentId) {
    $sql .= " AND p.student_id = ?";
    $params[] = $studentId;
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$receipt = fetchOne($stmt);

if (!$receipt) {
    $_SESSION['error'] = 'Receipt not found or access denied';
    redirect(APP_URL . 'modules/student/my-receipts.php');
}

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
    }
    body {
        background: white;
    }
}
.receipt-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.receipt-header {
    border-bottom: 3px solid #333;
    padding-bottom: 20px;
    margin-bottom: 30px;
    text-align: center;
}
.receipt-title {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}
.receipt-body {
    margin-bottom: 30px;
}
.receipt-section {
    margin-bottom: 25px;
}
.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e0e0e0;
}
.info-row {
    display: flex;
    margin-bottom: 8px;
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
.receipt-footer {
    border-top: 2px solid #333;
    padding-top: 20px;
    margin-top: 30px;
    text-align: center;
}
.amount-box {
    background: #f5f5f5;
    padding: 20px;
    border: 2px solid #333;
    border-radius: 5px;
    margin: 20px 0;
}
.amount-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}
.amount-value {
    font-size: 32px;
    font-weight: bold;
    color: #333;
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
                                <i class="ri-printer-line"></i> Print Receipt
                            </button>
                            <a href="<?php echo APP_URL; ?>modules/student/my-receipts.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Receipts
                            </a>
                        </div>
                        <h4 class="page-title">Payment Receipt</h4>
                    </div>
                </div>
            </div>

            <!-- Receipt Container -->
            <div class="row">
                <div class="col-12">
                    <div class="receipt-container">
                        
                        <!-- Receipt Header -->
                        <div class="receipt-header">
                            <div class="receipt-title">PAYMENT RECEIPT</div>
                            <div style="color: #666; font-size: 14px;">
                                Receipt No: <strong><?php echo htmlspecialchars($receipt['receipt_no']); ?></strong><br>
                                Date: <?php echo formatDate($receipt['payment_date']); ?>
                            </div>
                        </div>

                        <!-- School Information -->
                        <div class="receipt-section">
                            <h5 style="text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($receipt['branch_name'] ?? 'School'); ?></h5>
                            <?php if (!empty($receipt['branch_address'])): ?>
                            <p style="text-align: center; color: #666; margin-bottom: 5px;"><?php echo htmlspecialchars($receipt['branch_address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($receipt['branch_phone'])): ?>
                            <p style="text-align: center; color: #666; margin-bottom: 5px;">Phone: <?php echo htmlspecialchars($receipt['branch_phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($receipt['branch_email'])): ?>
                            <p style="text-align: center; color: #666;">Email: <?php echo htmlspecialchars($receipt['branch_email']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Receipt Body -->
                        <div class="receipt-body">
                            
                            <!-- Payment Information -->
                            <div class="receipt-section">
                                <div class="section-title">Payment Details</div>
                                <div class="info-row">
                                    <div class="info-label">Receipt Number:</div>
                                    <div class="info-value"><strong><?php echo htmlspecialchars($receipt['receipt_no']); ?></strong></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Payment Date:</div>
                                    <div class="info-value"><?php echo formatDate($receipt['payment_date']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Invoice Number:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['invoice_no']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Academic Session:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['session_name'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Payment Method:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['payment_method']); ?></div>
                                </div>
                                <?php if (!empty($receipt['transaction_id'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Transaction ID:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['transaction_id']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Student Information -->
                            <div class="receipt-section">
                                <div class="section-title">Student Information</div>
                                <div class="info-row">
                                    <div class="info-label">Student Name:</div>
                                    <div class="info-value">
                                        <strong><?php echo htmlspecialchars($receipt['first_name'] . ' ' . 
                                            ($receipt['middle_name'] ? $receipt['middle_name'] . ' ' : '') . 
                                            $receipt['last_name']); ?></strong>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Student ID:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['student_id']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Class:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['class_name'] ?? 'N/A'); ?></div>
                                </div>
                                <?php if (!empty($receipt['section_name'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Section:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($receipt['section_name']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Amount Box -->
                            <div class="amount-box" style="text-align: center;">
                                <div class="amount-label">Amount Received</div>
                                <div class="amount-value"><?php echo formatCurrency($receipt['amount']); ?></div>
                            </div>

                            <?php if (!empty($receipt['remarks'])): ?>
                            <div class="receipt-section">
                                <div class="section-title">Remarks</div>
                                <p style="color: #666;"><?php echo nl2br(htmlspecialchars($receipt['remarks'])); ?></p>
                            </div>
                            <?php endif; ?>

                        </div>

                        <!-- Receipt Footer -->
                        <div class="receipt-footer">
                            <p style="color: #666; margin-bottom: 10px;">
                                Received By: <strong><?php echo htmlspecialchars($receipt['received_by_name'] ?? 'N/A'); ?></strong>
                            </p>
                            <p style="color: #666; font-size: 12px; margin-bottom: 0;">
                                This is a computer-generated receipt. No signature required.<br>
                                Generated on: <?php echo date('d M Y, h:i A'); ?>
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

