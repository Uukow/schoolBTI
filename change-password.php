<?php
/**
 * Change Password Page
 * 
 * Allow users to change their password
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once 'config/config.php';

requireLogin();

$pageTitle = 'Change Password';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } else {
        $result = changePassword(getCurrentUser()['id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
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
                            <a href="<?php echo APP_URL; ?>profile.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Profile
                            </a>
                        </div>
                        <h4 class="page-title">Change Password</h4>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="avatar-lg mx-auto">
                                    <div class="avatar-title bg-primary-lighten text-primary rounded-circle">
                                        <i class="ri-lock-password-line font-32"></i>
                                    </div>
                                </div>
                                <h4 class="mt-3">Change Your Password</h4>
                                <p class="text-muted">Choose a strong password for your account security</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="ri-error-warning-line me-2"></i><?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="ri-check-line me-2"></i><?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label required">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-lock-line"></i></span>
                                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                        <span class="input-group-text cursor-pointer toggle-password" toggle="#currentPassword">
                                            <i class="ri-eye-off-line"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-lock-line"></i></span>
                                        <input type="password" class="form-control" id="newPassword" name="new_password" 
                                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                                        <span class="input-group-text cursor-pointer toggle-password" toggle="#newPassword">
                                            <i class="ri-eye-off-line"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-lock-line"></i></span>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                                        <span class="input-group-text cursor-pointer toggle-password" toggle="#confirmPassword">
                                            <i class="ri-eye-off-line"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    <strong>Password Tips:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Use at least <?php echo PASSWORD_MIN_LENGTH; ?> characters</li>
                                        <li>Include uppercase and lowercase letters</li>
                                        <li>Include numbers</li>
                                        <li>Include special characters (!@#$%)</li>
                                        <li>Don't use common words or personal info</li>
                                    </ul>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-save-line"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include 'includes/footer.php'; ?>

