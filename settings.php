<?php
/**
 * Centralized Settings Configuration Module
 * 
 * Single source of truth for all system-wide configurations
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once 'config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'System Settings';

// Initialize Settings Manager
$settingsManager = SettingsManager::getInstance();
$settings = $settingsManager->getAll();

// Get audit log for recent changes
$auditLog = $settingsManager->getAuditLog(10);

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
                            <button type="button" class="btn btn-info" onclick="reloadSettings()">
                                <i class="ri-refresh-line"></i> Reload Settings
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="viewAuditLog()">
                                <i class="ri-file-list-line"></i> View Audit Log
                            </button>
                        </div>
                        <h4 class="page-title">System Settings</h4>
                        <p class="text-muted mb-0">Centralized configuration management for all system settings</p>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>

            <!-- Settings Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#system-identity" role="tab">
                                        <i class="ri-building-line me-1"></i> System Identity
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#academic" role="tab">
                                        <i class="ri-graduation-cap-line me-1"></i> Academic
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#financial" role="tab">
                                        <i class="ri-money-dollar-circle-line me-1"></i> Financial
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#security" role="tab">
                                        <i class="ri-shield-check-line me-1"></i> Security
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#communication" role="tab">
                                        <i class="ri-mail-send-line me-1"></i> Communication
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#uiux" role="tab">
                                        <i class="ri-palette-line me-1"></i> UI/UX
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#integration" role="tab">
                                        <i class="ri-plug-line me-1"></i> Integration
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#features" role="tab">
                                        <i class="ri-toggle-line me-1"></i> Features
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content">
                                
                                <!-- System Identity Tab -->
                                <div class="tab-pane fade show active" id="system-identity" role="tabpanel">
                                    <form id="systemIdentityForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">System Name</label>
                                                    <input type="text" class="form-control" name="school_name" 
                                                           value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">System Short Name</label>
                                                    <input type="text" class="form-control" name="system_short_name" 
                                                           value="<?php echo htmlspecialchars($settings['system_short_name'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">System Logo</label>
                                                    <div class="mb-2">
                                                        <?php if (!empty($settings['system_logo'])): ?>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <img src="<?php echo APP_URL . htmlspecialchars($settings['system_logo']); ?>" 
                                                                 alt="Current Logo" 
                                                                 style="max-height: 60px; max-width: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;"
                                                                 class="me-2">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeLogo('logo')">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                        <small class="text-muted d-block">Current: <?php echo htmlspecialchars($settings['system_logo']); ?></small>
                                                        <?php else: ?>
                                                        <div class="alert alert-info py-2 mb-2">
                                                            <small><i class="ri-information-line"></i> No logo uploaded</small>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           id="logoUpload" 
                                                           name="system_logo" 
                                                           accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml"
                                                           onchange="uploadLogo('logo', this)">
                                                    <small class="text-muted">Recommended: 200x60px, Max 2MB (JPG, PNG, GIF, SVG)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Favicon</label>
                                                    <div class="mb-2">
                                                        <?php if (!empty($settings['system_favicon'])): ?>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <img src="<?php echo APP_URL . htmlspecialchars($settings['system_favicon']); ?>" 
                                                                 alt="Current Favicon" 
                                                                 style="max-height: 32px; max-width: 32px; border: 1px solid #ddd; padding: 2px; border-radius: 4px;"
                                                                 class="me-2">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeLogo('favicon')">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                        <small class="text-muted d-block">Current: <?php echo htmlspecialchars($settings['system_favicon']); ?></small>
                                                        <?php else: ?>
                                                        <div class="alert alert-info py-2 mb-2">
                                                            <small><i class="ri-information-line"></i> No favicon uploaded</small>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           id="faviconUpload" 
                                                           name="system_favicon" 
                                                           accept="image/x-icon,image/png,image/svg+xml"
                                                           onchange="uploadLogo('favicon', this)">
                                                    <small class="text-muted">Recommended: 32x32px or 16x16px, Max 2MB (ICO, PNG, SVG)</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">School Email</label>
                                                    <input type="email" class="form-control" name="school_email" 
                                                           value="<?php echo htmlspecialchars($settings['school_email'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">School Phone</label>
                                                    <input type="text" class="form-control" name="school_phone" 
                                                           value="<?php echo htmlspecialchars($settings['school_phone'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">School Address</label>
                                            <textarea class="form-control" name="school_address" rows="3"><?php echo htmlspecialchars($settings['school_address'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Developer/Company Name</label>
                                            <input type="text" class="form-control" name="developer_name" 
                                                   value="<?php echo htmlspecialchars($settings['developer_name'] ?? 'Uukow Technology Solutions (UTech)'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">License Text</label>
                                            <textarea class="form-control" name="license_text" rows="4"><?php echo htmlspecialchars($settings['license_text'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save System Identity
                                        </button>
                                    </form>
                                </div>

                                <!-- Academic Settings Tab -->
                                <div class="tab-pane fade" id="academic" role="tabpanel">
                                    <form id="academicForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Grading System</label>
                                                    <select class="form-select" name="grading_system">
                                                        <option value="Percentage" <?php echo ($settings['grading_system'] ?? 'Percentage') == 'Percentage' ? 'selected' : ''; ?>>Percentage</option>
                                                        <option value="Letter" <?php echo ($settings['grading_system'] ?? '') == 'Letter' ? 'selected' : ''; ?>>Letter Grade</option>
                                                        <option value="GPA" <?php echo ($settings['grading_system'] ?? '') == 'GPA' ? 'selected' : ''; ?>>GPA</option>
                                                        <option value="Points" <?php echo ($settings['grading_system'] ?? '') == 'Points' ? 'selected' : ''; ?>>Points</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">GPA Scale</label>
                                                    <input type="number" class="form-control" name="gpa_scale" 
                                                           value="<?php echo htmlspecialchars($settings['gpa_scale'] ?? '4.00'); ?>" 
                                                           step="0.01" min="0" max="10">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Attendance Threshold (%)</label>
                                                    <input type="number" class="form-control" name="attendance_threshold" 
                                                           value="<?php echo htmlspecialchars($settings['attendance_threshold'] ?? '75'); ?>" 
                                                           min="0" max="100">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Class Graduation</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="class_graduation_enabled" 
                                                               <?php echo ($settings['class_graduation_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Class Graduation Feature</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save Academic Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Financial Settings Tab -->
                                <div class="tab-pane fade" id="financial" role="tabpanel">
                                    <form id="financialForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Currency</label>
                                                    <select class="form-select" name="currency">
                                                        <option value="USD" <?php echo ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                                        <option value="SOS" <?php echo ($settings['currency'] ?? '') == 'SOS' ? 'selected' : ''; ?>>SOS - Somali Shilling</option>
                                                        <option value="EUR" <?php echo ($settings['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                                        <option value="GBP" <?php echo ($settings['currency'] ?? '') == 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                                        <option value="ETB" <?php echo ($settings['currency'] ?? '') == 'ETB' ? 'selected' : ''; ?>>ETB - Ethiopian Birr</option>
                                                        <option value="KES" <?php echo ($settings['currency'] ?? '') == 'KES' ? 'selected' : ''; ?>>KES - Kenyan Shilling</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Currency Symbol</label>
                                                    <input type="text" class="form-control" name="currency_symbol" 
                                                           value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Tuition Fee Behavior</label>
                                            <select class="form-select" name="tuition_fee_behavior">
                                                <option value="Monthly" <?php echo ($settings['tuition_fee_behavior'] ?? 'Monthly') == 'Monthly' ? 'selected' : ''; ?>>Monthly</option>
                                                <option value="Termly" <?php echo ($settings['tuition_fee_behavior'] ?? '') == 'Termly' ? 'selected' : ''; ?>>Termly</option>
                                                <option value="Yearly" <?php echo ($settings['tuition_fee_behavior'] ?? '') == 'Yearly' ? 'selected' : ''; ?>>Yearly</option>
                                                <option value="Custom" <?php echo ($settings['tuition_fee_behavior'] ?? '') == 'Custom' ? 'selected' : ''; ?>>Custom</option>
                                            </select>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="discount_enabled" 
                                                               <?php echo ($settings['discount_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Discounts</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="penalty_enabled" 
                                                               <?php echo ($settings['penalty_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Penalties</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Penalty Rate (%)</label>
                                                    <input type="number" class="form-control" name="penalty_rate" 
                                                           value="<?php echo htmlspecialchars($settings['penalty_rate'] ?? '0.00'); ?>" 
                                                           step="0.01" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="payroll_enabled" 
                                                               <?php echo ($settings['payroll_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Payroll</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="tax_enabled" 
                                                               <?php echo ($settings['tax_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Tax</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Tax Rate (%)</label>
                                                    <input type="number" class="form-control" name="tax_rate" 
                                                           value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0.00'); ?>" 
                                                           step="0.01" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save Financial Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Security Settings Tab -->
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <form id="securityForm">
                                        <h5 class="mb-3">Session & Authentication</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Session Timeout (seconds)</label>
                                                    <input type="number" class="form-control" name="session_timeout" 
                                                           value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '3600'); ?>" 
                                                           min="300" max="86400">
                                                    <small class="text-muted">Minimum: 300 (5 min), Maximum: 86400 (24 hours)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="two_factor_enabled" 
                                                               <?php echo ($settings['two_factor_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Two-Factor Authentication</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">Password Policy</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Minimum Password Length</label>
                                                    <input type="number" class="form-control" name="password_min_length" 
                                                           value="<?php echo htmlspecialchars($settings['password_min_length'] ?? '8'); ?>" 
                                                           min="6" max="32">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="password_require_uppercase" 
                                                               <?php echo ($settings['password_require_uppercase'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Require Uppercase</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="password_require_lowercase" 
                                                               <?php echo ($settings['password_require_lowercase'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Require Lowercase</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="password_require_number" 
                                                               <?php echo ($settings['password_require_number'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Require Number</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="password_require_special" 
                                                               <?php echo ($settings['password_require_special'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Require Special Character</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">Login Restrictions</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Max Login Attempts</label>
                                                    <input type="number" class="form-control" name="max_login_attempts" 
                                                           value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '5'); ?>" 
                                                           min="3" max="10">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Account Lockout Time (seconds)</label>
                                                    <input type="number" class="form-control" name="account_lockout_time" 
                                                           value="<?php echo htmlspecialchars($settings['account_lockout_time'] ?? '1800'); ?>" 
                                                           min="300">
                                                    <small class="text-muted">Default: 1800 (30 minutes)</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="audit_logging_enabled" 
                                                       <?php echo ($settings['audit_logging_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable Audit Logging</label>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save Security Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Communication Settings Tab -->
                                <div class="tab-pane fade" id="communication" role="tabpanel">
                                    <form id="communicationForm">
                                        <h5 class="mb-3">Email Configuration (SMTP)</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="email_enabled" 
                                                       <?php echo ($settings['email_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable Email</label>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Host</label>
                                                    <input type="text" class="form-control" name="smtp_host" 
                                                           value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Port</label>
                                                    <input type="number" class="form-control" name="smtp_port" 
                                                           value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Username</label>
                                                    <input type="text" class="form-control" name="smtp_username" 
                                                           value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Password</label>
                                                    <input type="password" class="form-control" name="smtp_password" 
                                                           placeholder="Leave blank to keep current">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">SMTP Encryption</label>
                                            <select class="form-select" name="smtp_encryption">
                                                <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') == 'none' ? 'selected' : ''; ?>>None</option>
                                            </select>
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">SMS Configuration</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="sms_enabled" 
                                                       <?php echo ($settings['sms_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable SMS</label>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMS Gateway</label>
                                                    <select class="form-select" name="sms_gateway">
                                                        <option value="twilio" <?php echo ($settings['sms_gateway'] ?? '') == 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                                                        <option value="nexmo" <?php echo ($settings['sms_gateway'] ?? '') == 'nexmo' ? 'selected' : ''; ?>>Nexmo</option>
                                                        <option value="custom" <?php echo ($settings['sms_gateway'] ?? '') == 'custom' ? 'selected' : ''; ?>>Custom</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMS API Key</label>
                                                    <input type="password" class="form-control" name="sms_api_key" 
                                                           placeholder="Leave blank to keep current">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">WhatsApp Configuration</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="whatsapp_enabled" 
                                                       <?php echo ($settings['whatsapp_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable WhatsApp</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">WhatsApp API Key</label>
                                            <input type="password" class="form-control" name="whatsapp_api_key" 
                                                   placeholder="Leave blank to keep current">
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">Notification Preferences</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="notification_enabled" 
                                                       <?php echo ($settings['notification_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable Notifications</label>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="notification_email" 
                                                           <?php echo ($settings['notification_email'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">Email Notifications</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="notification_sms" 
                                                           <?php echo ($settings['notification_sms'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">SMS Notifications</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="notification_whatsapp" 
                                                           <?php echo ($settings['notification_whatsapp'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">WhatsApp Notifications</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save Communication Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- UI/UX Settings Tab -->
                                <div class="tab-pane fade" id="uiux" role="tabpanel">
                                    <form id="uiuxForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Timezone</label>
                                                    <select class="form-select" name="timezone">
                                                        <option value="Africa/Mogadishu" <?php echo ($settings['timezone'] ?? 'Africa/Mogadishu') == 'Africa/Mogadishu' ? 'selected' : ''; ?>>Africa/Mogadishu (EAT)</option>
                                                        <option value="UTC" <?php echo ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                                        <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : ''; ?>>America/New_York (EST)</option>
                                                        <option value="Europe/London" <?php echo ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : ''; ?>>Europe/London (GMT)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Language</label>
                                                    <select class="form-select" name="language">
                                                        <option value="en" <?php echo ($settings['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                                        <option value="so" <?php echo ($settings['language'] ?? '') == 'so' ? 'selected' : ''; ?>>Somali</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Date Format</label>
                                                    <select class="form-select" name="date_format">
                                                        <option value="d-m-Y" <?php echo ($settings['date_format'] ?? 'd-m-Y') == 'd-m-Y' ? 'selected' : ''; ?>>DD-MM-YYYY</option>
                                                        <option value="m-d-Y" <?php echo ($settings['date_format'] ?? '') == 'm-d-Y' ? 'selected' : ''; ?>>MM-DD-YYYY</option>
                                                        <option value="Y-m-d" <?php echo ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Time Format</label>
                                                    <select class="form-select" name="time_format">
                                                        <option value="H:i:s" <?php echo ($settings['time_format'] ?? 'H:i:s') == 'H:i:s' ? 'selected' : ''; ?>>24 Hour (HH:MM:SS)</option>
                                                        <option value="h:i:s A" <?php echo ($settings['time_format'] ?? '') == 'h:i:s A' ? 'selected' : ''; ?>>12 Hour (HH:MM:SS AM/PM)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Theme</label>
                                                    <select class="form-select" name="theme">
                                                        <option value="default" <?php echo ($settings['theme'] ?? 'default') == 'default' ? 'selected' : ''; ?>>Default</option>
                                                        <option value="dark" <?php echo ($settings['theme'] ?? '') == 'dark' ? 'selected' : ''; ?>>Dark</option>
                                                        <option value="light" <?php echo ($settings['theme'] ?? '') == 'light' ? 'selected' : ''; ?>>Light</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Pagination Limit</label>
                                                    <input type="number" class="form-control" name="pagination_limit" 
                                                           value="<?php echo htmlspecialchars($settings['pagination_limit'] ?? '25'); ?>" 
                                                           min="10" max="100">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Records Per Page</label>
                                                    <input type="number" class="form-control" name="records_per_page" 
                                                           value="<?php echo htmlspecialchars($settings['records_per_page'] ?? '25'); ?>" 
                                                           min="10" max="100">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save UI/UX Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Integration Settings Tab -->
                                <div class="tab-pane fade" id="integration" role="tabpanel">
                                    <form id="integrationForm">
                                        <h5 class="mb-3">API Configuration</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="api_enabled" 
                                                       <?php echo ($settings['api_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable API</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">API Key</label>
                                            <input type="password" class="form-control" name="api_key" 
                                                   placeholder="Leave blank to keep current">
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">Webhook Configuration</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="webhook_enabled" 
                                                       <?php echo ($settings['webhook_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable Webhooks</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Webhook URL</label>
                                            <input type="url" class="form-control" name="webhook_url" 
                                                   value="<?php echo htmlspecialchars($settings['webhook_url'] ?? ''); ?>">
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">Payment Gateway</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Gateway</label>
                                                    <select class="form-select" name="payment_gateway">
                                                        <option value="stripe" <?php echo ($settings['payment_gateway'] ?? '') == 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                                                        <option value="paypal" <?php echo ($settings['payment_gateway'] ?? '') == 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                                                        <option value="custom" <?php echo ($settings['payment_gateway'] ?? '') == 'custom' ? 'selected' : ''; ?>>Custom</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Payment API Key</label>
                                                    <input type="password" class="form-control" name="payment_api_key" 
                                                           placeholder="Leave blank to keep current">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="mb-3 mt-4">License Verification</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="license_verification_enabled" 
                                                       <?php echo ($settings['license_verification_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Enable License Verification</label>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">License Verification Endpoint</label>
                                                    <input type="url" class="form-control" name="license_verification_endpoint" 
                                                           value="<?php echo htmlspecialchars($settings['license_verification_endpoint'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">License Key</label>
                                                    <input type="password" class="form-control" name="license_key" 
                                                           placeholder="Leave blank to keep current">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save Integration Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Features Tab -->
                                <div class="tab-pane fade" id="features" role="tabpanel">
                                    <form id="featuresForm">
                                        <h5 class="mb-3">Feature Toggles</h5>
                                        <p class="text-muted">Enable or disable specific features/modules in the system.</p>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="feature_lms" 
                                                               <?php echo ($settings['feature_lms'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Learning Management System (LMS)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="feature_library" 
                                                               <?php echo ($settings['feature_library'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Library Management</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="feature_transport" 
                                                               <?php echo ($settings['feature_transport'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Transport Management</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="feature_hostel" 
                                                               <?php echo ($settings['feature_hostel'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Hostel Management</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="feature_certificates" 
                                                               <?php echo ($settings['feature_certificates'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Certificates</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="feature_events" 
                                                               <?php echo ($settings['feature_events'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Events Management</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Save Feature Settings
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Audit Log Modal -->
<div class="modal fade" id="auditLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Settings Audit Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Setting</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th>Changed By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLog as $log): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($log['setting_key']); ?></code></td>
                                <td><small><?php echo htmlspecialchars(substr($log['old_value'] ?? '', 0, 50)); ?></small></td>
                                <td><small><?php echo htmlspecialchars(substr($log['new_value'] ?? '', 0, 50)); ?></small></td>
                                <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer-scripts.php'; ?>

<script>
// APP_URL is already declared in footer.php, so we can use it directly

// Handle form submissions
$('#systemIdentityForm, #academicForm, #financialForm, #securityForm, #communicationForm, #uiuxForm, #integrationForm, #featuresForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const formId = form.attr('id');
    
    // Build data object from form
    const data = {};
    
    // Handle all input types except files
    form.find('input, select, textarea').each(function() {
        const $input = $(this);
        const name = $input.attr('name');
        const type = $input.attr('type') || $input.prop('tagName').toLowerCase();
        
        if (!name) return; // Skip inputs without names
        
        // Skip file inputs
        if (type === 'file') {
            return;
        }
        
        // Handle checkboxes
        if (type === 'checkbox') {
            data[name] = $input.is(':checked') ? 1 : 0;
            return;
        }
        
        // Handle radio buttons
        if (type === 'radio') {
            if ($input.is(':checked')) {
                data[name] = $input.val();
            }
            return;
        }
        
        // Handle other inputs (text, email, number, url, password, etc.)
        const value = $input.val();
        
        // Skip empty password fields (they should remain unchanged)
        if (type === 'password' && !value) {
            return;
        }
        
        // Add to data object (include all values, even empty strings)
        data[name] = value !== null && value !== undefined ? value : '';
    });
    
    // Validate that we have data
    if (Object.keys(data).length === 0) {
        console.error('No form data collected!');
        console.error('Form element:', form[0]);
        console.error('Total inputs found:', form.find('input, select, textarea').length);
        console.error('Inputs with names:', form.find('[name]').length);
        form.find('input, select, textarea').each(function() {
            console.log('Input:', $(this).attr('name'), 'Type:', $(this).attr('type'), 'Value:', $(this).val());
        });
        showAlert('danger', 'No data to save. Please check the form and try again.');
        return;
    }
    
    console.log('Saving settings:', data);
    console.log('Form ID:', formId);
    console.log('Data keys:', Object.keys(data));
    console.log('Data count:', Object.keys(data).length);
    
    $.ajax({
        url: APP_URL + 'ajax/settings/save-settings.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            if (response.success) {
                let message = response.message || 'Settings saved successfully!';
                
                // Show warnings if any
                if (response.data && response.data.warnings && response.data.warnings.length > 0) {
                    message += '\n\nWarnings:\n' + response.data.warnings.slice(0, 3).join('\n');
                    if (response.data.warnings.length > 3) {
                        message += '\n... and ' + (response.data.warnings.length - 3) + ' more';
                    }
                    showAlert('warning', message);
                } else {
                    showAlert('success', message);
                }
                
                // Reload settings after a delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                let errorMsg = response.message || 'Failed to save settings';
                
                // Show detailed errors
                if (response.data && response.data.errors && response.data.errors.length > 0) {
                    errorMsg += '\n\nErrors:\n' + response.data.errors.slice(0, 3).join('\n');
                }
                
                if (response.data && response.data.warnings && response.data.warnings.length > 0) {
                    errorMsg += '\n\nMissing Columns:\n' + response.data.warnings.slice(0, 5).join('\n');
                    errorMsg += '\n\nPlease run the database migration to add missing columns.';
                }
                
                showAlert('danger', errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            let errorMsg = 'An error occurred while saving settings: ' + error;
            
            // Try to parse error response
            try {
                const errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.message) {
                    errorMsg = errorResponse.message;
                }
            } catch (e) {
                // Not JSON, use default message
            }
            
            showAlert('danger', errorMsg);
        }
    });
});

function showAlert(type, message) {
    // Replace newlines with <br> for HTML display
    const htmlMessage = message.replace(/\n/g, '<br>');
    
    const iconClass = type === 'success' ? 'check' : (type === 'warning' ? 'alert' : 'error-warning');
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="ri-${iconClass}-line me-2"></i>
            <div style="display: inline-block;">${htmlMessage}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alertContainer').html(alertHtml);
    
    // Auto-hide after 8 seconds (longer for warnings/errors)
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 8000);
}

function reloadSettings() {
    $.ajax({
        url: APP_URL + 'ajax/settings/reload-settings.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Settings reloaded successfully!');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert('danger', response.message || 'Failed to reload settings');
            }
        }
    });
}

function viewAuditLog() {
    $('#auditLogModal').modal('show');
}

// Upload logo/favicon
function uploadLogo(fileType, input) {
    const file = input.files[0];
    if (!file) {
        return;
    }
    
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        showAlert('danger', 'File size exceeds 2MB limit');
        input.value = ''; // Clear input
        return;
    }
    
    // Show loading
    const originalText = input.nextElementSibling?.textContent || '';
    if (input.nextElementSibling) {
        input.nextElementSibling.innerHTML = '<i class="ri-loader-4-line spin"></i> Uploading...';
    }
    
    // Create FormData
    const formData = new FormData();
    formData.append('file', file);
    formData.append('file_type', fileType);
    
    $.ajax({
        url: APP_URL + 'ajax/settings/upload-logo.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message || ucfirst(fileType) + ' uploaded successfully!');
                // Reload page after a delay to show new logo/favicon
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('danger', response.message || 'Failed to upload ' + fileType);
                input.value = ''; // Clear input on error
            }
        },
        error: function(xhr, status, error) {
            console.error('Upload Error:', status, error, xhr.responseText);
            showAlert('danger', 'An error occurred while uploading: ' + error);
            input.value = ''; // Clear input on error
        },
        complete: function() {
            // Restore original text
            if (input.nextElementSibling && originalText) {
                input.nextElementSibling.textContent = originalText;
            }
        }
    });
}

// Remove logo/favicon
function removeLogo(fileType) {
    if (!confirm('Are you sure you want to remove the ' + fileType + '?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('file_type', fileType);
    
    $.ajax({
        url: APP_URL + 'ajax/settings/remove-logo.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message || ucfirst(fileType) + ' removed successfully!');
                // Reload page after a delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('danger', response.message || 'Failed to remove ' + fileType);
            }
        },
        error: function(xhr, status, error) {
            console.error('Remove Error:', status, error, xhr.responseText);
            showAlert('danger', 'An error occurred while removing: ' + error);
        }
    });
}

// Helper function
function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
