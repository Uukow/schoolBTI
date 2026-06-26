<?php
/**
 * View Staff Profile
 * 
 * Display complete staff information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

hrRequirePage('hr_payroll', 'view');

$pageTitle = 'Staff Profile';

$staffId = $_GET['id'] ?? 0;

if (empty($staffId)) {
    $_SESSION['error'] = 'Invalid staff ID';
    redirect(APP_URL . 'modules/hr/staff.php');
}

// Get current user
$currentUser = getCurrentUser();

// Get staff details with all related information
$sql = "SELECT s.*, b.branch_name, u.username as user_account
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = ?";

$params = [$staffId];
$types = 'i';

// Branch filter for non-super admins
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$staff = fetchOne($stmt);

if (!$staff) {
    $_SESSION['error'] = 'Staff not found or access denied';
    redirect(APP_URL . 'modules/hr/staff.php');
}

// Calculate age
$age = '';
if (!empty($staff['date_of_birth'])) {
    $age = calculateAge($staff['date_of_birth']);
}

// Calculate years of service
$yearsOfService = '';
if (!empty($staff['joining_date'])) {
    $joinDate = new DateTime($staff['joining_date']);
    $today = new DateTime();
    $yearsOfService = $joinDate->diff($today)->y;
}

// Get attendance summary (if available)
$attendanceSql = "SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_days
    FROM staff_attendance 
    WHERE staff_id = ?";
$attendanceStats = fetchOne(executeQuery($attendanceSql, 'i', [$staffId]));

// Calculate attendance percentage
$attendancePercentage = 0;
if ($attendanceStats && $attendanceStats['total_days'] > 0) {
    $attendancePercentage = round(($attendanceStats['present_days'] / $attendanceStats['total_days']) * 100, 2);
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
                            <a href="<?php echo APP_URL; ?>modules/hr/staff.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to List
                            </a>
                            <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                            <a href="edit-staff.php?id=<?php echo $staff['id']; ?>" class="btn btn-warning ms-2">
                                <i class="ri-edit-line"></i> Edit Profile
                            </a>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Staff Profile</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Profile Card -->
                <div class="col-xl-4">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($staff['photo'])): ?>
                                <img src="<?php echo APP_URL . $staff['photo']; ?>" 
                                     alt="<?php echo htmlspecialchars($staff['first_name']); ?>" 
                                     class="rounded-circle img-thumbnail mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px; font-size: 60px;">
                                    <?php echo strtoupper(substr($staff['first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h4>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($staff['staff_id']); ?></p>
                            
                            <div class="mb-3">
                                <?php
                                $statusClass = 'secondary';
                                switch($staff['status']) {
                                    case 'Active': $statusClass = 'success'; break;
                                    case 'Inactive': $statusClass = 'warning'; break;
                                    case 'Resigned': $statusClass = 'info'; break;
                                    case 'Terminated': $statusClass = 'danger'; break;
                                }
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?> badge-lg">
                                    <?php echo htmlspecialchars($staff['status']); ?>
                                </span>
                            </div>
                            
                            <div class="text-start mt-3">
                                <p class="mb-2"><i class="ri-building-line me-2"></i><strong>Branch:</strong> <?php echo htmlspecialchars($staff['branch_name']); ?></p>
                                <p class="mb-2"><i class="ri-briefcase-line me-2"></i><strong>Designation:</strong> <?php echo htmlspecialchars($staff['designation']); ?></p>
                                <?php if ($staff['department']): ?>
                                <p class="mb-2"><i class="ri-organization-chart me-2"></i><strong>Department:</strong> <?php echo htmlspecialchars($staff['department']); ?></p>
                                <?php endif; ?>
                                <p class="mb-2"><i class="ri-calendar-line me-2"></i><strong>Joining Date:</strong> <?php echo formatDate($staff['joining_date']); ?></p>
                                <?php if ($yearsOfService !== ''): ?>
                                <p class="mb-2"><i class="ri-time-line me-2"></i><strong>Years of Service:</strong> <?php echo $yearsOfService; ?> years</p>
                                <?php endif; ?>
                                <?php if ($age): ?>
                                <p class="mb-0"><i class="ri-calendar-check-line me-2"></i><strong>Age:</strong> <?php echo $age; ?> years</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Quick Statistics</h5>
                            
                            <?php if ($attendanceStats && $attendanceStats['total_days'] > 0): ?>
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
                            <?php endif; ?>
                            
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Employment Type:</span>
                                    <strong><?php echo htmlspecialchars($staff['employment_type']); ?></strong>
                                </div>
                            </div>
                            <?php if ($staff['experience_years']): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Experience:</span>
                                    <strong><?php echo $staff['experience_years']; ?> years</strong>
                                </div>
                            </div>
                            <?php endif; ?>
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
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></strong></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Staff ID</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($staff['staff_id']); ?></strong></p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Gender</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['gender']); ?></p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Date of Birth</label>
                                    <p class="mb-0"><?php echo formatDate($staff['date_of_birth']); ?></p>
                                </div>
                                <?php if ($age): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Age</label>
                                    <p class="mb-0"><?php echo $age; ?> years</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Designation</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($staff['designation']); ?></strong></p>
                                </div>
                                <?php if ($staff['department']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Department</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['department']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Employment Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Joining Date</label>
                                    <p class="mb-0"><strong><?php echo formatDate($staff['joining_date']); ?></strong></p>
                                </div>
                                <?php if ($staff['leaving_date']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Leaving Date</label>
                                    <p class="mb-0"><?php echo formatDate($staff['leaving_date']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Employment Type</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['employment_type']); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Status</label>
                                    <p class="mb-0">
                                        <?php
                                        $statusClass = 'secondary';
                                        switch($staff['status']) {
                                            case 'Active': $statusClass = 'success'; break;
                                            case 'Inactive': $statusClass = 'warning'; break;
                                            case 'Resigned': $statusClass = 'info'; break;
                                            case 'Terminated': $statusClass = 'danger'; break;
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($staff['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($staff['qualification']): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Qualification</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['qualification']); ?></p>
                                </div>
                                <?php if ($staff['experience_years']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Experience</label>
                                    <p class="mb-0"><?php echo $staff['experience_years']; ?> years</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($yearsOfService !== ''): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Years of Service</label>
                                    <p class="mb-0"><strong><?php echo $yearsOfService; ?> years</strong></p>
                                </div>
                            </div>
                            <?php endif; ?>
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
                                        <?php if ($staff['email']): ?>
                                            <i class="ri-mail-line me-1"></i><?php echo htmlspecialchars($staff['email']); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Phone</label>
                                    <p class="mb-0">
                                        <?php if ($staff['phone']): ?>
                                            <i class="ri-phone-line me-1"></i><?php echo htmlspecialchars($staff['phone']); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($staff['address']): ?>
                            <div class="mb-3">
                                <label class="text-muted mb-1">Address</label>
                                <p class="mb-0"><i class="ri-map-pin-line me-1"></i><?php echo htmlspecialchars($staff['address']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <?php if ($staff['city']): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">City</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['city']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($staff['state']): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">State/Region</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['state']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($staff['postal_code']): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted mb-1">Postal Code</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['postal_code']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <?php if ($staff['emergency_contact'] || $staff['emergency_phone']): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Emergency Contact</h4>
                            
                            <div class="row">
                                <?php if ($staff['emergency_contact']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Contact Name</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['emergency_contact']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($staff['emergency_phone']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Contact Phone</label>
                                    <p class="mb-0"><i class="ri-phone-line me-1"></i><?php echo htmlspecialchars($staff['emergency_phone']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Bank Information -->
                    <?php if ($staff['bank_account_no'] || $staff['bank_name']): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Bank Information</h4>
                            
                            <div class="row">
                                <?php if ($staff['bank_name']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Bank Name</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['bank_name']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($staff['bank_account_no']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Account Number</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['bank_account_no']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Account Information -->
                    <?php if ($staff['user_account']): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Account Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Username</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($staff['user_account']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>


