<?php
/**
 * General Settings
 * 
 * Configure system-wide settings
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'General Settings';

// Get current settings
$sql = "SELECT * FROM system_settings LIMIT 1";
$stmt = executeQuery($sql);
$settings = fetchOne($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $schoolName = sanitize($_POST['school_name'] ?? '');
    $schoolEmail = sanitize($_POST['school_email'] ?? '');
    $schoolPhone = sanitize($_POST['school_phone'] ?? '');
    $schoolAddress = sanitize($_POST['school_address'] ?? '');
    $currency = $_POST['currency'] ?? 'USD';
    $currencySymbol = $_POST['currency_symbol'] ?? '$';
    $timezone = $_POST['timezone'] ?? 'Africa/Mogadishu';
    $language = $_POST['language'] ?? 'en';
    $dateFormat = $_POST['date_format'] ?? 'd-m-Y';
    
    // Update settings
    if ($settings) {
        $sql = "UPDATE system_settings SET 
                school_name = ?, school_email = ?, school_phone = ?, school_address = ?,
                currency = ?, currency_symbol = ?, timezone = ?, language = ?, date_format = ?
                WHERE id = ?";
        
        $stmt = executeQuery($sql, 'sssssssssi', [
            $schoolName, $schoolEmail, $schoolPhone, $schoolAddress,
            $currency, $currencySymbol, $timezone, $language, $dateFormat,
            $settings['id']
        ]);
    } else {
        $sql = "INSERT INTO system_settings 
                (school_name, school_email, school_phone, school_address, currency, currency_symbol, timezone, language, date_format)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = executeQuery($sql, 'sssssssss', [
            $schoolName, $schoolEmail, $schoolPhone, $schoolAddress,
            $currency, $currencySymbol, $timezone, $language, $dateFormat
        ]);
    }
    
    if ($stmt) {
        logActivity(getCurrentUser()['id'], 'Update Settings', 'Settings', 'Updated general settings');
        $_SESSION['success'] = 'Settings updated successfully!';
        redirect(APP_URL . 'modules/settings/general.php');
    } else {
        $error = 'Failed to update settings';
    }
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
                        <h4 class="page-title">General Settings</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Settings Form -->
            <form method="POST" action="">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- School Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">School Information</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label required">School Name</label>
                                    <input type="text" class="form-control" name="school_name" 
                                           value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">School Email</label>
                                        <input type="email" class="form-control" name="school_email" 
                                               value="<?php echo htmlspecialchars($settings['school_email'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">School Phone</label>
                                        <input type="text" class="form-control" name="school_phone" 
                                               value="<?php echo htmlspecialchars($settings['school_phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">School Address</label>
                                    <textarea class="form-control" name="school_address" rows="3"><?php echo htmlspecialchars($settings['school_address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- System Configuration -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">System Configuration</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Currency</label>
                                        <select class="form-select" name="currency">
                                            <option value="USD" <?php echo ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                            <option value="SOS" <?php echo ($settings['currency'] ?? '') == 'SOS' ? 'selected' : ''; ?>>SOS - Somali Shilling</option>
                                            <option value="EUR" <?php echo ($settings['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                            <option value="GBP" <?php echo ($settings['currency'] ?? '') == 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Currency Symbol</label>
                                        <input type="text" class="form-control" name="currency_symbol" 
                                               value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Timezone</label>
                                        <select class="form-select" name="timezone">
                                            <option value="Africa/Mogadishu" <?php echo ($settings['timezone'] ?? '') == 'Africa/Mogadishu' ? 'selected' : ''; ?>>Africa/Mogadishu (EAT)</option>
                                            <option value="UTC" <?php echo ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : ''; ?>>America/New_York (EST)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Language</label>
                                        <select class="form-select" name="language">
                                            <option value="en" <?php echo ($settings['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                            <option value="so" <?php echo ($settings['language'] ?? '') == 'so' ? 'selected' : ''; ?>>Somali</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Date Format</label>
                                    <select class="form-select" name="date_format">
                                        <option value="d-m-Y" <?php echo ($settings['date_format'] ?? 'd-m-Y') == 'd-m-Y' ? 'selected' : ''; ?>>DD-MM-YYYY</option>
                                        <option value="m-d-Y" <?php echo ($settings['date_format'] ?? '') == 'm-d-Y' ? 'selected' : ''; ?>>MM-DD-YYYY</option>
                                        <option value="Y-m-d" <?php echo ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Submit Button -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="ri-save-line"></i> Save Settings
                                </button>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Quick Info</h5>
                                <p class="text-muted mb-2">
                                    <i class="ri-information-line"></i> Changes will take effect immediately
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="ri-shield-check-line"></i> All settings are logged for audit
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

