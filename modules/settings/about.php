<?php
/**
 * About & License Information
 * 
 * Display system information, license details, and contact information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'About & License';

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
                        <h4 class="page-title">About & License Information</h4>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-information-line"></i> System Information
                            </h4>
                            
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td width="200"><strong>System Name:</strong></td>
                                            <td><strong class="text-primary"><?php echo APP_NAME; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Version:</strong></td>
                                            <td><?php echo APP_VERSION; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Developer:</strong></td>
                                            <td><strong>Uukow Technology Solutions (UTech)</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Copyright:</strong></td>
                                            <td>&copy; <?php echo date('Y'); ?> Uukow Technology Solutions (UTech). All rights reserved.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-shield-check-line"></i> License Information
                            </h4>
                            
                            <div class="alert alert-warning">
                                <h5 class="alert-heading">
                                    <i class="ri-alert-line"></i> Proprietary Software - Unauthorized Use Prohibited
                                </h5>
                                <p class="mb-0">
                                    <strong><?php echo APP_NAME; ?></strong> is proprietary software developed and owned by 
                                    <strong>Uukow Technology Solutions (UTech)</strong>. 
                                    The use of this software without official authorization is strictly prohibited.
                                </p>
                            </div>

                            <div class="mb-4">
                                <h5>License Terms:</h5>
                                <ul>
                                    <li>This software is protected by copyright laws and international copyright treaties</li>
                                    <li>Unauthorized use, reproduction, distribution, or modification may result in severe civil and criminal penalties</li>
                                    <li>All licensing inquiries must be made through official channels</li>
                                    <li>Permission to use this software is granted ONLY upon official authorization from Uukow Technology Solutions (UTech)</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <h5>Restrictions:</h5>
                                <ul>
                                    <li>You may NOT use this Software without official authorization</li>
                                    <li>You may NOT redistribute this Software in any form</li>
                                    <li>You may NOT sell or sublicense this Software</li>
                                    <li>You may NOT remove or modify copyright notices</li>
                                    <li>You may NOT claim authorship of this Software</li>
                                    <li>You may NOT reverse engineer, decompile, or disassemble this Software</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-customer-service-line"></i> Licensing & Contact Information
                            </h4>
                            
                            <p class="mb-4">
                                For licensing, authorization, or permission to use this Software, please contact:
                            </p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="ri-building-line text-primary"></i> Uukow Technology Solutions (UTech)
                                            </h5>
                                            <p class="card-text">
                                                <strong>Website:</strong><br>
                                                <a href="https://uukowtech.com" target="_blank" class="text-primary">
                                                    <i class="ri-global-line"></i> https://uukowtech.com
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card border-success mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="ri-mail-line text-success"></i> Email Contact
                                            </h5>
                                            <p class="card-text">
                                                <strong>Email:</strong><br>
                                                <a href="mailto:info@uukowtech.com" class="text-success">
                                                    <i class="ri-mail-send-line"></i> info@uukowtech.com
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="ri-phone-line text-info"></i> Phone & WhatsApp
                                            </h5>
                                            <p class="card-text">
                                                <strong>WhatsApp / Call:</strong><br>
                                                <a href="https://wa.me/252613888976" target="_blank" class="btn btn-success btn-sm">
                                                    <i class="ri-whatsapp-line"></i> +252613888976
                                                </a>
                                                <a href="tel:+252613888976" class="btn btn-info btn-sm ms-2">
                                                    <i class="ri-phone-line"></i> Call Now
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h6 class="alert-heading">
                                    <i class="ri-information-line"></i> Important Notice
                                </h6>
                                <p class="mb-0">
                                    All licensing inquiries must be made through the official channels listed above. 
                                    Unauthorized use of this software is strictly prohibited and may result in legal action.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full License Text -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-file-text-line"></i> Full License Agreement
                            </h4>
                            
                            <div class="text-center mb-3">
                                <a href="<?php echo APP_URL; ?>LICENSE.txt" target="_blank" class="btn btn-primary">
                                    <i class="ri-file-download-line"></i> View Full License Agreement (LICENSE.txt)
                                </a>
                            </div>
                            
                            <div class="alert alert-secondary">
                                <p class="mb-0">
                                    <small>
                                        For the complete license agreement, terms, and conditions, please refer to the 
                                        <a href="<?php echo APP_URL; ?>LICENSE.txt" target="_blank" class="text-primary">LICENSE.txt</a> file 
                                        in the root directory of this application.
                                    </small>
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

