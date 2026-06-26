<?php
/**
 * User Profile Page
 * 
 * View and edit user profile
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once 'config/config.php';

requireLogin();

$pageTitle = 'My Profile';

$currentUser = getCurrentUser();

// Get additional info based on role
$additionalInfo = null;

if ($currentUser['role_name'] == 'Student') {
    $sql = "SELECT * FROM students WHERE user_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentUser['id']]);
    $additionalInfo = fetchOne($stmt);
} elseif ($currentUser['role_name'] == 'Teacher' || in_array($currentUser['role_name'], ['Admin', 'Accountant', 'Librarian'])) {
    $sql = "SELECT * FROM staff WHERE user_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentUser['id']]);
    $additionalInfo = fetchOne($stmt);
} elseif ($currentUser['role_name'] == 'Parent') {
    $sql = "SELECT * FROM parents WHERE user_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentUser['id']]);
    $additionalInfo = fetchOne($stmt);
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>change-password.php" class="btn btn-warning">
                                <i class="ri-lock-password-line"></i> Change Password
                            </a>
                        </div>
                        <h4 class="page-title">My Profile</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($additionalInfo['photo'])): ?>
                                <img src="<?php echo APP_URL . $additionalInfo['photo']; ?>" 
                                     alt="Profile Photo" class="rounded-circle img-thumbnail mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px; font-size: 60px;">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($currentUser['username']); ?></h4>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                            
                            <span class="badge bg-primary badge-lg">
                                <?php echo htmlspecialchars($currentUser['role_name']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Account Information</h5>
                            
                            <p class="mb-2">
                                <i class="ri-building-line me-2"></i>
                                <strong>Branch:</strong> <?php echo htmlspecialchars($currentUser['branch_name'] ?? 'All Branches'); ?>
                            </p>
                            <p class="mb-2">
                                <i class="ri-calendar-line me-2"></i>
                                <strong>Member Since:</strong> <?php echo formatDate($currentUser['created_at']); ?>
                            </p>
                            <p class="mb-2">
                                <i class="ri-time-line me-2"></i>
                                <strong>Last Login:</strong> <?php echo $currentUser['last_login'] ? formatDate($currentUser['last_login'], 'd M Y H:i') : 'Never'; ?>
                            </p>
                            <p class="mb-0">
                                <i class="ri-shield-check-line me-2"></i>
                                <strong>Status:</strong> 
                                <?php if ($currentUser['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                                <?php if ($currentUser['is_verified']): ?>
                                    <span class="badge bg-info ms-1">Verified</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <?php if ($additionalInfo): ?>
                    <!-- Additional Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Personal Information</h4>
                            
                            <div class="row">
                                <?php if (isset($additionalInfo['first_name'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Full Name</label>
                                    <p class="mb-0">
                                        <strong>
                                            <?php echo htmlspecialchars($additionalInfo['first_name'] . ' ' . $additionalInfo['last_name']); ?>
                                        </strong>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($additionalInfo['gender'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Gender</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($additionalInfo['gender']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($additionalInfo['date_of_birth'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Date of Birth</label>
                                    <p class="mb-0"><?php echo formatDate($additionalInfo['date_of_birth']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($additionalInfo['phone'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted mb-1">Phone</label>
                                    <p class="mb-0">
                                        <i class="ri-phone-line me-1"></i>
                                        <?php echo htmlspecialchars($additionalInfo['phone']); ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($additionalInfo['address'])): ?>
                                <div class="col-12 mb-3">
                                    <label class="text-muted mb-1">Address</label>
                                    <p class="mb-0">
                                        <i class="ri-map-pin-line me-1"></i>
                                        <?php echo htmlspecialchars($additionalInfo['address']); ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Recent Activity</h4>
                            
                            <?php
                            $activitySql = "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
                            $activities = fetchAll(executeQuery($activitySql, 'i', [$currentUser['id']]));
                            ?>
                            
                            <?php if (!empty($activities)): ?>
                            <div class="timeline">
                                <?php foreach ($activities as $activity): ?>
                                <div class="timeline-item">
                                    <small class="text-muted"><?php echo formatDate($activity['created_at'], 'd M Y H:i'); ?></small>
                                    <p class="mb-0">
                                        <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                                        <span class="text-muted">in <?php echo htmlspecialchars($activity['module']); ?></span>
                                        <?php if (!empty($activity['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($activity['description']); ?></small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <p class="text-muted text-center">No recent activity</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include 'includes/footer.php'; ?>

