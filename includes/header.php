<?php
/**
 * Header Component
 * 
 * Reusable header for all pages
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Get current user
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get unread notifications count
$notificationSql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$notificationStmt = executeQuery($notificationSql, 'i', [$currentUser['id']]);
$notificationCount = fetchOne($notificationStmt)['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="TacliinHub ERP System - Comprehensive School Management System" name="description" />
    <meta content="Uukow Technology Solutions (UTech)" name="author" />
    <meta content="Proprietary Software - Unauthorized use is prohibited" name="copyright" />

    <!-- App favicon -->
    <?php
    // Get system favicon from settings
    $systemFavicon = null;
    if (class_exists('SettingsManager')) {
        $settingsManager = SettingsManager::getInstance();
        $systemFavicon = $settingsManager->get('system_favicon');
    } else {
        // Fallback: direct database query
        try {
            $faviconSql = "SELECT system_favicon FROM system_settings LIMIT 1";
            $faviconStmt = executeQuery($faviconSql);
            $faviconSettings = fetchOne($faviconStmt);
            $systemFavicon = $faviconSettings['system_favicon'] ?? null;
        } catch (Exception $e) {
            $systemFavicon = null;
        }
    }
    
    // Determine favicon URL
    $faviconUrl = !empty($systemFavicon) && file_exists(ABSPATH . $systemFavicon) 
        ? APP_URL . $systemFavicon 
        : APP_URL . 'template_extracted/assets/images/favicon.ico';
    ?>
    <link rel="shortcut icon" href="<?php echo $faviconUrl; ?>">
    <link rel="icon" type="image/png" href="<?php echo $faviconUrl; ?>">

    <!-- Daterangepicker css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">

    <!-- Vector Map css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- Datatables css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config Js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/hyper-config.js"></script>

    <!-- Vendor css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>assets/css/custom.css" rel="stylesheet" type="text/css" />

    <?php if (isset($additionalCSS)) echo $additionalCSS; ?>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- ========== Topbar Start ========== -->
        <div class="navbar-custom">
            <div class="topbar container-fluid">
                <div class="d-flex align-items-center gap-lg-2 gap-1">

                    <!-- Topbar Brand Logo -->
                    <div class="logo-topbar">
                        <?php
                        // Get system logo from settings
                        if (!isset($systemLogo)) {
                            $systemLogo = null;
                            if (class_exists('SettingsManager')) {
                                $settingsManager = SettingsManager::getInstance();
                                $systemLogo = $settingsManager->get('system_logo');
                            } else {
                                try {
                                    $logoSql = "SELECT system_logo FROM system_settings LIMIT 1";
                                    $logoStmt = executeQuery($logoSql);
                                    $logoSettings = fetchOne($logoStmt);
                                    $systemLogo = $logoSettings['system_logo'] ?? null;
                                } catch (Exception $e) {
                                    $systemLogo = null;
                                }
                            }
                        }
                        
                        // Determine logo URLs for header
                        $headerLogoLight = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
                            ? APP_URL . $systemLogo 
                            : APP_URL . 'template_extracted/assets/images/logo.png';
                            
                        $headerLogoDark = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
                            ? APP_URL . $systemLogo 
                            : APP_URL . 'template_extracted/assets/images/logo-dark.png';
                            
                        $headerLogoSm = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
                            ? APP_URL . $systemLogo 
                            : APP_URL . 'template_extracted/assets/images/logo-sm.png';
                            
                        $headerLogoDarkSm = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
                            ? APP_URL . $systemLogo 
                            : APP_URL . 'template_extracted/assets/images/logo-dark-sm.png';
                        ?>
                        <!-- Logo light -->
                        <a href="<?php echo APP_URL; ?>dashboard.php" class="logo-light">
                            <span class="logo-lg">
                                <img src="<?php echo $headerLogoLight; ?>" alt="logo" style="max-height: 40px; width: auto;">
                            </span>
                            <span class="logo-sm">
                                <img src="<?php echo $headerLogoSm; ?>" alt="small logo" style="max-height: 32px; width: auto;">
                            </span>
                        </a>

                        <!-- Logo Dark -->
                        <a href="<?php echo APP_URL; ?>dashboard.php" class="logo-dark">
                            <span class="logo-lg">
                                <img src="<?php echo $headerLogoDark; ?>" alt="dark logo" style="max-height: 40px; width: auto;">
                            </span>
                            <span class="logo-sm">
                                <img src="<?php echo $headerLogoDarkSm; ?>" alt="small logo" style="max-height: 32px; width: auto;">
                            </span>
                        </a>
                    </div>

                    <!-- Sidebar Menu Toggle Button -->
                    <button class="button-toggle-menu">
                        <i class="ri-menu-5-line"></i>
                    </button>

                    <!-- Topbar Search Form -->
                    <div class="app-search dropdown d-none d-lg-block">
                        <form id="globalSearchForm">
                            <div class="input-group">
                                <input type="search" class="form-control" placeholder="Search students, teachers..." id="top-search">
                                <span class="ri-search-line search-icon"></span>
                            </div>
                        </form>
                    </div>

                </div>

                <ul class="topbar-menu d-flex align-items-center gap-3">
                    <li class="dropdown d-lg-none">
                        <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="ri-search-line font-22"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                            <form class="p-3">
                                <input type="search" class="form-control" placeholder="Search ..." aria-label="Search">
                            </form>
                        </div>
                    </li>

                    <!-- Notifications -->
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="ri-notification-3-line font-22"></i>
                            <?php if ($notificationCount > 0): ?>
                                <span class="noti-icon-badge"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0">
                            <div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 font-16 fw-semibold">Notifications</h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="javascript:void(0);" onclick="markAllRead()" class="text-dark text-decoration-underline">
                                            <small>Clear All</small>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="px-2" style="max-height: 300px;" data-simplebar id="notificationList">
                                <!-- Notifications will be loaded via AJAX -->
                                <div class="text-center p-3">
                                    <small class="text-muted">Loading notifications...</small>
                                </div>
                            </div>

                            <a href="<?php echo APP_URL; ?>notifications.php" class="dropdown-item text-center text-primary notify-item border-top py-2">
                                View All
                            </a>
                        </div>
                    </li>

                    <!-- Dark/Light Mode Toggle -->
                    <li class="d-none d-sm-inline-block">
                        <div class="nav-link" id="light-dark-mode">
                            <i class="ri-moon-line font-22"></i>
                        </div>
                    </li>

                    <!-- Fullscreen Toggle -->
                    <li class="d-none d-md-inline-block">
                        <a class="nav-link" href="#" data-toggle="fullscreen">
                            <i class="ri-fullscreen-line font-22"></i>
                        </a>
                    </li>

                    <!-- User Dropdown -->
                    <li class="dropdown">
                        <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="account-user-avatar">
                                <?php if (!empty($currentUser['photo'])): ?>
                                    <img src="<?php echo APP_URL . $currentUser['photo']; ?>" alt="user-image" width="32" class="rounded-circle">
                                <?php else: ?>
                                    <i class="ri-user-line font-22"></i>
                                <?php endif; ?>
                            </span>
                            <span class="d-lg-flex flex-column gap-1 d-none">
                                <h5 class="my-0"><?php echo htmlspecialchars($currentUser['username']); ?></h5>
                                <h6 class="my-0 fw-normal"><?php echo htmlspecialchars($currentUser['role_name']); ?></h6>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                            <!-- item-->
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow m-0">Welcome!</h6>
                            </div>

                            <!-- item-->
                            <a href="<?php echo APP_URL; ?>profile.php" class="dropdown-item">
                                <i class="ri-user-smile-line font-16 me-1"></i>
                                <span>My Account</span>
                            </a>

                            <!-- item-->
                            <a href="<?php echo APP_URL; ?>settings.php" class="dropdown-item">
                                <i class="ri-user-settings-line font-16 me-1"></i>
                                <span>Settings</span>
                            </a>

                            <!-- item-->
                            <a href="<?php echo APP_URL; ?>support.php" class="dropdown-item">
                                <i class="ri-lifebuoy-line font-16 me-1"></i>
                                <span>Support</span>
                            </a>

                            <!-- item-->
                            <a href="<?php echo APP_URL; ?>change-password.php" class="dropdown-item">
                                <i class="ri-lock-line font-16 me-1"></i>
                                <span>Change Password</span>
                            </a>

                            <!-- item-->
                            <a href="<?php echo APP_URL; ?>logout.php" class="dropdown-item">
                                <i class="ri-login-circle-line font-16 me-1"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <!-- ========== Topbar End ========== -->


