<?php
/**
 * View Student Profile
 * 
 * Display complete student information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Student Profile';

$studentId = $_GET['id'] ?? 0;

if (empty($studentId)) {
    $_SESSION['error'] = 'Invalid student ID';
    redirect(APP_URL . 'modules/students/list.php');
}

// Get student details with all related information
$sql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name, u.username as user_account
        FROM students s
        LEFT JOIN classes c ON s.current_class_id = c.id
        LEFT JOIN sections sec ON s.current_section_id = sec.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = ?";

$stmt = executeQuery($sql, 'i', [$studentId]);
$student = fetchOne($stmt);

if (!$student) {
    $_SESSION['error'] = 'Student not found';
    redirect(APP_URL . 'modules/students/list.php');
}

// Get parent information
$parentSql = "SELECT p.*, sp.relationship, sp.is_primary
              FROM parents p
              INNER JOIN student_parents sp ON p.id = sp.parent_id
              WHERE sp.student_id = ?
              ORDER BY sp.is_primary DESC";
$parents = fetchAll(executeQuery($parentSql, 'i', [$studentId]));

// Get student documents
$docsSql = "SELECT * FROM student_documents WHERE student_id = ? ORDER BY uploaded_at DESC";
$documents = fetchAll(executeQuery($docsSql, 'i', [$studentId]));

// Get attendance summary
$attendanceSql = "SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_days
    FROM student_attendance 
    WHERE student_id = ?";
$attendanceStats = fetchOne(executeQuery($attendanceSql, 'i', [$studentId]));

// Calculate attendance percentage
$attendancePercentage = 0;
if ($attendanceStats['total_days'] > 0) {
    $attendancePercentage = round(($attendanceStats['present_days'] / $attendanceStats['total_days']) * 100, 2);
}

// Get fee summary
$feeSql = "SELECT 
    COUNT(*) as total_invoices,
    COALESCE(SUM(net_amount), 0) as total_amount,
    COALESCE(SUM(paid_amount), 0) as total_paid,
    COALESCE(SUM(due_amount), 0) as total_due
    FROM fee_invoices 
    WHERE student_id = ?";
$feeStats = fetchOne(executeQuery($feeSql, 'i', [$studentId]));

// Get recent invoices
$recentInvoicesSql = "SELECT * FROM fee_invoices WHERE student_id = ? ORDER BY created_at DESC LIMIT 5";
$recentInvoices = fetchAll(executeQuery($recentInvoicesSql, 'i', [$studentId]));

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
                            <a href="<?php echo APP_URL; ?>modules/students/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to List
                            </a>
                            <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                            <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning ms-2">
                                <i class="ri-edit-line"></i> Edit Profile
                            </a>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Student Profile</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Profile Card -->
                <div class="col-xl-4">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($student['photo'])): ?>
                                <img src="<?php echo APP_URL . $student['photo']; ?>" 
                                     alt="<?php echo htmlspecialchars($student['first_name']); ?>" 
                                     class="rounded-circle img-thumbnail mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px; font-size: 60px;">
                                    <?php echo strtoupper(substr($student['first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($student['student_id']); ?></p>
                            
                            <div class="mb-3">
                                <?php
                                $statusClass = 'secondary';
                                switch($student['status']) {
                                    case 'Active': $statusClass = 'success'; break;
                                    case 'Inactive': $statusClass = 'warning'; break;
                                    case 'Graduated': $statusClass = 'info'; break;
                                }
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?> badge-lg">
                                    <?php echo htmlspecialchars($student['status']); ?>
                                </span>
                            </div>
                            
                            <div class="text-start mt-3">
                                <p class="mb-2"><i class="ri-building-line me-2"></i><strong>Branch:</strong> <?php echo htmlspecialchars($student['branch_name']); ?></p>
                                <p class="mb-2"><i class="ri-book-open-line me-2"></i><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></p>
                                <?php if ($student['section_name']): ?>
                                <p class="mb-2"><i class="ri-organization-chart me-2"></i><strong>Section:</strong> <?php echo htmlspecialchars($student['section_name']); ?></p>
                                <?php endif; ?>
                                <p class="mb-2"><i class="ri-calendar-line me-2"></i><strong>Admission Date:</strong> <?php echo formatDate($student['admission_date']); ?></p>
                                <p class="mb-0"><i class="ri-calendar-check-line me-2"></i><strong>Age:</strong> <?php echo calculateAge($student['date_of_birth']); ?> years</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Quick Statistics</h5>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Attendance</span>
                                    <span class="text-success"><strong><?php echo $attendancePercentage; ?>%</strong></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $attendancePercentage; ?>%"></div>
                                </div>
                                <small class="text-muted"><?php echo $attendanceStats['present_days']; ?>/<?php echo $attendanceStats['total_days']; ?> days present</small>
                            </div>
                            
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Fees:</span>
                                    <strong><?php echo formatCurrency($feeStats['total_amount']); ?></strong>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Paid:</span>
                                    <span class="text-success"><strong><?php echo formatCurrency($feeStats['total_paid']); ?></strong></span>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Due:</span>
                                    <span class="text-danger"><strong><?php echo formatCurrency($feeStats['total_due']); ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Details -->
                <div class="col-xl-8">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Basic Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Full Name</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></strong></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Admission Number</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($student['admission_no']); ?></strong></p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Gender</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['gender']); ?></p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Date of Birth</label>
                                    <p class="mb-0"><?php echo formatDate($student['date_of_birth']); ?></p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Blood Group</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['blood_group'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Religion</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['religion'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Nationality</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['nationality'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Contact Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Email</label>
                                    <p class="mb-0">
                                        <?php if ($student['email']): ?>
                                            <i class="ri-mail-line me-1"></i><?php echo htmlspecialchars($student['email']); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Phone</label>
                                    <p class="mb-0">
                                        <?php if ($student['phone']): ?>
                                            <i class="ri-phone-line me-1"></i><?php echo htmlspecialchars($student['phone']); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($student['address']): ?>
                            <div class="mb-3">
                                <label class="text-muted mb-1">Address</label>
                                <p class="mb-0"><i class="ri-map-pin-line me-1"></i><?php echo htmlspecialchars($student['address']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <?php if ($student['city']): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">City</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['city']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($student['state']): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">State/Region</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['state']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($student['postal_code']): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Postal Code</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($student['postal_code']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <?php if (!empty($parents)): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Parent/Guardian Information</h4>
                            
                            <?php foreach ($parents as $parent): ?>
                            <div class="border-start border-primary border-3 ps-3 mb-3">
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?>
                                    <?php if ($parent['is_primary']): ?>
                                        <span class="badge bg-primary ms-2">Primary</span>
                                    <?php endif; ?>
                                </h5>
                                <p class="mb-1"><i class="ri-user-line me-2"></i><strong>Relationship:</strong> <?php echo htmlspecialchars($parent['relationship']); ?></p>
                                <p class="mb-1"><i class="ri-phone-line me-2"></i><strong>Phone:</strong> <?php echo htmlspecialchars($parent['phone']); ?></p>
                                <?php if ($parent['email']): ?>
                                <p class="mb-1"><i class="ri-mail-line me-2"></i><strong>Email:</strong> <?php echo htmlspecialchars($parent['email']); ?></p>
                                <?php endif; ?>
                                <?php if ($parent['occupation']): ?>
                                <p class="mb-0"><i class="ri-briefcase-line me-2"></i><strong>Occupation:</strong> <?php echo htmlspecialchars($parent['occupation']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Academic Performance -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Academic Performance</h4>
                            
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h3 class="text-success mb-1"><?php echo $attendancePercentage; ?>%</h3>
                                    <p class="text-muted mb-0">Attendance</p>
                                </div>
                                <div class="col-md-3">
                                    <h3 class="text-primary mb-1">-</h3>
                                    <p class="text-muted mb-0">GPA</p>
                                </div>
                                <div class="col-md-3">
                                    <h3 class="text-info mb-1">-</h3>
                                    <p class="text-muted mb-0">Rank</p>
                                </div>
                                <div class="col-md-3">
                                    <h3 class="text-warning mb-1">-</h3>
                                    <p class="text-muted mb-0">Grade</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Summary -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Fee Summary</h4>
                            
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <p class="text-muted mb-1">Total Invoices</p>
                                    <h4 class="mb-0"><?php echo $feeStats['total_invoices']; ?></h4>
                                </div>
                                <div class="col-6 mb-2">
                                    <p class="text-muted mb-1">Total Amount</p>
                                    <h4 class="mb-0"><?php echo formatCurrency($feeStats['total_amount']); ?></h4>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted mb-1">Total Paid</p>
                                    <h4 class="mb-0 text-success"><?php echo formatCurrency($feeStats['total_paid']); ?></h4>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted mb-1">Total Due</p>
                                    <h4 class="mb-0 text-danger"><?php echo formatCurrency($feeStats['total_due']); ?></h4>
                                </div>
                            </div>
                            
                            <?php if (!empty($recentInvoices)): ?>
                            <hr>
                            <h6 class="mb-2">Recent Invoices</h6>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentInvoices as $invoice): ?>
                                <a href="<?php echo APP_URL; ?>modules/fees/view-invoice.php?id=<?php echo $invoice['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span>
                                        <small class="text-muted"><?php echo htmlspecialchars($invoice['invoice_no']); ?></small><br>
                                        <small><?php echo formatCurrency($invoice['net_amount']); ?></small>
                                    </span>
                                    <span class="badge bg-<?php echo ($invoice['status'] == 'Paid') ? 'success' : 'warning'; ?>">
                                        <?php echo $invoice['status']; ?>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Documents -->
                    <?php if (!empty($documents)): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Documents</h4>
                            
                            <div class="list-group list-group-flush">
                                <?php foreach ($documents as $doc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="ri-file-line me-2"></i>
                                        <?php echo htmlspecialchars($doc['document_name']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($doc['document_type']); ?></small>
                                    </div>
                                    <a href="<?php echo APP_URL . $doc['file_path']; ?>" 
                                       class="btn btn-sm btn-primary" download target="_blank">
                                        <i class="ri-download-line"></i>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>
