<?php
/**
 * View Payslip
 * 
 * Display payslip for printing
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Payslip';
$currentUser = getCurrentUser();
$isAdmin = hasRole(['Super Admin', 'Admin', 'Accountant']);
$isStaffSelf = hasRole(['Teacher', 'Staff']) && !$isAdmin;

if (!$isAdmin && !$isStaffSelf) {
    $_SESSION['error'] = 'Access denied';
    redirect(APP_URL . 'index.php');
}

// Get payment ID from URL
$paymentId = $_GET['id'] ?? 0;

if (empty($paymentId)) {
    $_SESSION['error'] = 'Invalid payslip ID';
    redirect($isStaffSelf ? APP_URL . 'modules/hr/my-payslips.php' : APP_URL . 'modules/hr/payroll.php');
}

// Fetch payroll details with all related information
$sql = "SELECT sp.*, 
        s.first_name, s.last_name, s.staff_id, s.designation, s.photo,
        s.address, s.city, s.state, s.phone, s.email,
        b.branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email,
        u.username as processed_by_name
        FROM salary_payments sp
        INNER JOIN staff s ON sp.staff_id = s.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON sp.processed_by = u.id
        WHERE sp.id = ?";

$params = [$paymentId];
$types = 'i';

// Branch filter for non-super admins
if (!hasRole(['Super Admin']) && $isAdmin) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$payslip = fetchOne($stmt);

if ($payslip && $isStaffSelf) {
    $ownStaff = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    if (!$ownStaff || $ownStaff['id'] != $payslip['staff_id']) {
        $payslip = null;
    }
}

if (!$payslip) {
    $_SESSION['error'] = 'Payslip not found or access denied';
    redirect($isStaffSelf ? APP_URL . 'modules/hr/my-payslips.php' : APP_URL . 'modules/hr/payroll.php');
}

// Get system settings for school information
$settingsSql = "SELECT * FROM system_settings LIMIT 1";
$settings = fetchOne(executeQuery($settingsSql));

include '../../includes/header.php';
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .payslip-container {
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
.payslip-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.payslip-header {
    border-bottom: 3px solid #4a90e2;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.payslip-title {
    font-size: 36px;
    font-weight: bold;
    color: #4a90e2;
    margin-bottom: 10px;
}
.payslip-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}
.payslip-section {
    margin-bottom: 25px;
}
.payslip-section h5 {
    color: #4a90e2;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 8px;
    margin-bottom: 15px;
}
.payslip-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.payslip-table th,
.payslip-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}
.payslip-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}
.payslip-table tr:last-child td {
    border-bottom: none;
}
.payslip-total {
    background-color: #f8f9fa;
    font-weight: bold;
    font-size: 18px;
}
.payslip-footer {
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
                        <a href="<?php echo APP_URL; ?>modules/hr/<?php echo $isStaffSelf ? 'my-payslips.php' : 'payroll.php'; ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Back
                        </a>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="ri-printer-line"></i> Print Payslip
                            </button>
                            <a href="<?php echo APP_URL; ?>modules/hr/download-payslip-pdf.php?id=<?php echo (int)$paymentId; ?>" class="btn btn-success ms-2">
                                <i class="ri-download-line"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payslip Container -->
            <div class="payslip-container">
                
                <!-- Header -->
                <div class="payslip-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="payslip-title">PAYSLIP</h2>
                            <p class="mb-0"><strong>Payment Month:</strong> <?php echo date('F Y', strtotime($payslip['payment_month'])); ?></p>
                            <p class="mb-0"><strong>Generated Date:</strong> <?php echo formatDateTime($payslip['created_at']); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if (!empty($payslip['branch_name'])): ?>
                            <h5><strong><?php echo htmlspecialchars($payslip['branch_name']); ?></strong></h5>
                            <?php elseif (!empty($settings['school_name'])): ?>
                            <h5><strong><?php echo htmlspecialchars($settings['school_name']); ?></strong></h5>
                            <?php endif; ?>
                            <?php if (!empty($payslip['branch_address'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($payslip['branch_address']); ?></p>
                            <?php elseif (!empty($settings['school_address'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($settings['school_address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payslip['branch_phone'])): ?>
                            <p class="mb-1">Phone: <?php echo htmlspecialchars($payslip['branch_phone']); ?></p>
                            <?php elseif (!empty($settings['school_phone'])): ?>
                            <p class="mb-1">Phone: <?php echo htmlspecialchars($settings['school_phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payslip['branch_email'])): ?>
                            <p class="mb-0">Email: <?php echo htmlspecialchars($payslip['branch_email']); ?></p>
                            <?php elseif (!empty($settings['school_email'])): ?>
                            <p class="mb-0">Email: <?php echo htmlspecialchars($settings['school_email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Staff Information -->
                <div class="payslip-section">
                    <h5>Staff Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($payslip['first_name'] . ' ' . $payslip['last_name']); ?></p>
                            <p class="mb-1"><strong>Staff ID:</strong> <?php echo htmlspecialchars($payslip['staff_id']); ?></p>
                            <p class="mb-1"><strong>Designation:</strong> <?php echo htmlspecialchars($payslip['designation'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($payslip['address'])): ?>
                            <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($payslip['address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payslip['phone'])): ?>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($payslip['phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($payslip['email'])): ?>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($payslip['email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Salary Details -->
                <div class="payslip-section">
                    <h5>Salary Details</h5>
                    <table class="payslip-table">
                        <thead>
                            <tr>
                                <th width="60%">Description</th>
                                <th width="20%" class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Basic Salary</strong></td>
                                <td class="text-end"><?php echo formatCurrency($payslip['basic_salary']); ?></td>
                            </tr>
                            <?php if ($payslip['allowances'] > 0): ?>
                            <tr>
                                <td><strong>Allowances</strong></td>
                                <td class="text-end text-success">+ <?php echo formatCurrency($payslip['allowances']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($payslip['deductions'] > 0): ?>
                            <tr>
                                <td><strong>Deductions</strong></td>
                                <td class="text-end text-danger">- <?php echo formatCurrency($payslip['deductions']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="payslip-total">
                                <td><strong>Net Salary</strong></td>
                                <td class="text-end"><strong><?php echo formatCurrency($payslip['net_salary']); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Information -->
                <div class="payslip-section">
                    <h5>Payment Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Payment Month:</strong> <?php echo date('F Y', strtotime($payslip['payment_month'])); ?></p>
                            <?php if ($payslip['payment_date']): ?>
                            <p class="mb-1"><strong>Payment Date:</strong> <?php echo formatDate($payslip['payment_date']); ?></p>
                            <?php else: ?>
                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
                            <?php endif; ?>
                            <?php if (!empty($payslip['payment_method'])): ?>
                            <p class="mb-1"><strong>Payment Method:</strong> 
                                <span class="badge bg-info"><?php echo htmlspecialchars($payslip['payment_method']); ?></span>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Processed By:</strong> <?php echo htmlspecialchars($payslip['processed_by_name'] ?? 'N/A'); ?></p>
                            <p class="mb-1"><strong>Processed Date:</strong> <?php echo formatDateTime($payslip['created_at']); ?></p>
                            <?php if (!empty($payslip['remarks'])): ?>
                            <p class="mb-1"><strong>Remarks:</strong> <?php echo htmlspecialchars($payslip['remarks']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="payslip-footer">
                    <p class="mb-2"><strong>This is a computer-generated payslip. No signature required.</strong></p>
                    <p class="mb-0">Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                </div>

            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

