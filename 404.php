<?php
/**
 * 404 Error Page
 * 
 * Page not found error
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>404 - Page Not Found - <?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>template_extracted/assets/images/favicon.ico">
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/vendor.min.css" rel="stylesheet" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/app.min.css" rel="stylesheet" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/remixicon/remixicon.css" rel="stylesheet" />
</head>
<body class="authentication-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h1 class="text-error mb-4" style="font-size: 100px; color: #fa5c7c;">404</h1>
                                <h4 class="text-uppercase text-danger mt-3">Page Not Found</h4>
                                <p class="text-muted mt-3">
                                    The page you are looking for might have been removed, had its name changed, 
                                    or is temporarily unavailable.
                                </p>
                                <a class="btn btn-primary mt-3" href="<?php echo APP_URL; ?>">
                                    <i class="ri-home-4-line me-1"></i> Return Home
                                </a>
                                <?php if (isLoggedIn()): ?>
                                <a class="btn btn-info mt-3 ms-2" href="<?php echo APP_URL; ?>dashboard.php">
                                    <i class="ri-dashboard-line me-1"></i> Go to Dashboard
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/vendor.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/app.js"></script>
</body>
</html>

