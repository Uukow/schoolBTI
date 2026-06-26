<?php
/**
 * My Profile - Teacher Portal
 * 
 * View and edit teacher's own profile
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher']);

$pageTitle = 'My Profile';

// Get current user and teacher record
$currentUser = getCurrentUser();
$teacher = getTeacherByUserId($currentUser['id']);

if (!$teacher) {
    $_SESSION['error'] = 'Teacher profile not found. Please contact administrator.';
    redirect(APP_URL . 'dashboard.php');
}

// Get branch info
$branchSql = "SELECT * FROM branches WHERE id = ?";
$branch = fetchOne(executeQuery($branchSql, 'i', [$teacher['branch_id']]));

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
                        <h4 class="page-title">My Profile</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Profile Info -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($teacher['photo'])): ?>
                                <img src="<?php echo APP_URL . $teacher['photo']; ?>" alt="Photo" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                            <?php else: ?>
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title bg-primary rounded-circle text-white display-4">
                                        <?php echo strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($teacher['designation']); ?></p>
                            
                            <div class="text-start mt-4">
                                <p class="text-muted mb-2"><i class="ri-user-line me-2"></i> <strong>Staff ID:</strong> <?php echo htmlspecialchars($teacher['staff_id']); ?></p>
                                <p class="text-muted mb-2"><i class="ri-building-line me-2"></i> <strong>Branch:</strong> <?php echo htmlspecialchars($branch['branch_name'] ?? 'N/A'); ?></p>
                                <p class="text-muted mb-2"><i class="ri-mail-line me-2"></i> <strong>Email:</strong> <?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></p>
                                <p class="text-muted mb-2"><i class="ri-phone-line me-2"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></p>
                                <p class="text-muted mb-2"><i class="ri-calendar-line me-2"></i> <strong>Joining Date:</strong> <?php echo formatDate($teacher['joining_date']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Personal Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['gender']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" class="form-control" value="<?php echo formatDate($teacher['date_of_birth']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>" readonly>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($teacher['address'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <hr>

                            <h4 class="header-title mb-3">Professional Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['designation']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['department'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Qualification</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['qualification'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Experience (Years)</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['experience_years'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employment Type</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['employment_type']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['status']); ?>" readonly>
                                </div>
                            </div>

                            <hr>

                            <h4 class="header-title mb-3">Emergency Contact</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Emergency Contact Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['emergency_contact'] ?? 'N/A'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Emergency Phone</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['emergency_phone'] ?? 'N/A'); ?>" readonly>
                                </div>
                            </div>

                            <div class="mt-3">
                                <p class="text-muted">
                                    <small><i class="ri-information-line"></i> To update your profile information, please contact the administrator.</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<?php include '../../includes/footer-scripts.php'; ?>








