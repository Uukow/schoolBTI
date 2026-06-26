<?php
/**
 * View Leave Details
 * 
 * Display complete leave application details
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

hrRequireAccess('hr_leave', 'view');

$pageTitle = 'Leave Details';

// Get leave ID from URL
$leaveId = $_GET['id'] ?? 0;

if (empty($leaveId)) {
    $_SESSION['error'] = 'Invalid leave ID';
    redirect(APP_URL . 'modules/hr/leaves.php');
}

// Get current user for access control
$currentUser = getCurrentUser();

// Fetch leave details with all related information
$sql = "SELECT la.*, 
        s.first_name, s.last_name, s.staff_id, s.designation, s.photo,
        s.email, s.phone, s.address,
        b.branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email,
        lt.leave_name, lt.leave_code, lt.days_allowed,
        u.username as approved_by_name
        FROM leave_applications la
        INNER JOIN staff s ON la.staff_id = s.id
        INNER JOIN leave_types lt ON la.leave_type_id = lt.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON la.approved_by = u.id
        WHERE la.id = ?";

$params = [$leaveId];
$types = 'i';

// Access control - staff can only view their own leaves
if (hasRole(['Teacher', 'Staff'])) {
    $sql .= " AND la.staff_id = ?";
    $params[] = $currentUser['staff_id'] ?? 0;
    $types .= 'i';
} elseif (!hasRole(['Super Admin'])) {
    // Branch filter for admins
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$leave = fetchOne($stmt);

if (!$leave) {
    $_SESSION['error'] = 'Leave application not found or access denied';
    redirect(APP_URL . 'modules/hr/leaves.php');
}

include '../../includes/header.php';
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .leave-container {
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
.leave-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.leave-header {
    border-bottom: 3px solid #4a90e2;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.leave-title {
    font-size: 36px;
    font-weight: bold;
    color: #4a90e2;
    margin-bottom: 10px;
}
.leave-section {
    margin-bottom: 25px;
}
.leave-section h5 {
    color: #4a90e2;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 8px;
    margin-bottom: 15px;
}
.info-row {
    display: flex;
    margin-bottom: 12px;
}
.info-label {
    font-weight: 600;
    width: 180px;
    color: #555;
}
.info-value {
    flex: 1;
    color: #333;
}
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
}
.status-pending {
    background-color: #fff3cd;
    color: #856404;
}
.status-approved {
    background-color: #d4edda;
    color: #155724;
}
.status-rejected {
    background-color: #f8d7da;
    color: #721c24;
}
.status-cancelled {
    background-color: #e2e3e5;
    color: #383d41;
}
.reason-box {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #4a90e2;
    margin-top: 10px;
}
.leave-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
    text-align: center;
    color: #666;
    font-size: 14px;
}
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Print Actions -->
            <div class="row no-print mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?php echo APP_URL; ?>modules/hr/leaves.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Back to Leaves
                        </a>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="downloadLeave()" class="btn btn-success ms-2">
                                <i class="ri-download-line"></i> Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Details Container -->
            <div class="leave-container">
                
                <!-- Header -->
                <div class="leave-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="leave-title">LEAVE APPLICATION</h2>
                            <p class="mb-0"><strong>Application ID:</strong> #<?php echo $leave['id']; ?></p>
                            <p class="mb-0"><strong>Applied On:</strong> <?php echo formatDateTime($leave['applied_at']); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if (!empty($leave['branch_name'])): ?>
                            <h5><strong><?php echo htmlspecialchars($leave['branch_name']); ?></strong></h5>
                            <?php if (!empty($leave['branch_address'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($leave['branch_address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($leave['branch_phone'])): ?>
                            <p class="mb-1">Phone: <?php echo htmlspecialchars($leave['branch_phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($leave['branch_email'])): ?>
                            <p class="mb-0">Email: <?php echo htmlspecialchars($leave['branch_email']); ?></p>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="leave-section">
                    <div class="text-center mb-4">
                        <span class="status-badge status-<?php echo strtolower($leave['status']); ?>">
                            <?php echo htmlspecialchars($leave['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Staff Information -->
                <div class="leave-section">
                    <h5>Staff Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Name:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Staff ID:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['staff_id']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Designation:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['designation'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($leave['email'])): ?>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['email']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($leave['phone'])): ?>
                            <div class="info-row">
                                <span class="info-label">Phone:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['phone']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($leave['address'])): ?>
                            <div class="info-row">
                                <span class="info-label">Address:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['address']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Leave Details -->
                <div class="leave-section">
                    <h5>Leave Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Leave Type:</span>
                                <span class="info-value">
                                    <strong><?php echo htmlspecialchars($leave['leave_name']); ?></strong>
                                    <small class="text-muted">(<?php echo htmlspecialchars($leave['leave_code']); ?>)</small>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Days Allowed:</span>
                                <span class="info-value"><?php echo $leave['days_allowed'] ? $leave['days_allowed'] . ' days' : 'Unlimited'; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Start Date:</span>
                                <span class="info-value"><strong><?php echo formatDate($leave['start_date']); ?></strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">End Date:</span>
                                <span class="info-value"><strong><?php echo formatDate($leave['end_date']); ?></strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total Days:</span>
                                <span class="info-value"><strong><?php echo $leave['total_days']; ?> days</strong></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Application Date:</span>
                                <span class="info-value"><?php echo formatDateTime($leave['applied_at']); ?></span>
                            </div>
                            <?php if ($leave['approval_date']): ?>
                            <div class="info-row">
                                <span class="info-label">Approval Date:</span>
                                <span class="info-value"><?php echo formatDateTime($leave['approval_date']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($leave['approved_by_name']): ?>
                            <div class="info-row">
                                <span class="info-label">Approved By:</span>
                                <span class="info-value"><?php echo htmlspecialchars($leave['approved_by_name']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div class="leave-section">
                    <h5>Reason for Leave</h5>
                    <div class="reason-box">
                        <?php echo nl2br(htmlspecialchars($leave['reason'])); ?>
                    </div>
                </div>

                <!-- Rejection Reason (if rejected) -->
                <?php if ($leave['status'] == 'Rejected' && !empty($leave['rejection_reason'])): ?>
                <div class="leave-section">
                    <h5>Rejection Reason</h5>
                    <div class="reason-box" style="border-left-color: #dc3545;">
                        <?php echo nl2br(htmlspecialchars($leave['rejection_reason'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="leave-footer">
                    <p class="mb-2"><strong>This is a computer-generated leave application document.</strong></p>
                    <p class="mb-0">Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                </div>

            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function downloadLeave() {
    // Open print dialog which can be saved as PDF
    window.print();
}
</script>

