<?php
/**
 * Login Page
 * 
 * User authentication page
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define constants
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');

// Include configuration
require_once ABSPATH . 'config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(APP_URL . 'dashboard.php');
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        try {
            // Check if this is a student trying to login
            $checkStudentSql = "SELECT u.id, r.role_name
                               FROM users u
                               LEFT JOIN roles r ON u.role_id = r.id
                               WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1";
            $stmt = executeQuery($checkStudentSql, 'ss', [$username, $username]);
            
            if ($stmt === false) {
                $error = 'Database error. Please try again later.';
            } else {
                $userCheck = fetchOne($stmt);
                
                if ($userCheck && isset($userCheck['role_name']) && $userCheck['role_name'] === 'Student') {
                    $error = 'Students must login through the Student Portal. <a href="' . APP_URL . 'student-login.php" class="alert-link fw-bold">Click here to go to Student Login</a>';
                } else {
                    $result = loginUser($username, $password);
                    
                    if ($result && isset($result['success']) && $result['success']) {
                        // Double check role after login
                        $loggedInUser = getCurrentUser();
                        if ($loggedInUser && isset($loggedInUser['role_name']) && $loggedInUser['role_name'] === 'Student') {
                            // Logout and redirect to student login
                            logoutUser();
                            redirect(APP_URL . 'student-login.php');
                        }
                        
                        // Redirect to intended page or dashboard
                        $redirectUrl = $_GET['redirect'] ?? APP_URL . 'dashboard.php';
                        redirect($redirectUrl);
                    } else {
                        $error = isset($result['message']) ? $result['message'] : 'Login failed. Please try again.';
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred during login. Please try again.';
        } catch (Error $e) {
            error_log("Login fatal error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $error = 'A system error occurred. Please contact the administrator.';
        }
    }
}

// Check for logout message
if (isset($_GET['logout'])) {
    $success = 'You have been successfully logged out.';
}

// Check for session messages
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Login - <?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- App favicon -->
    <?php
    // Get system favicon from settings
    $loginFavicon = null;
    if (class_exists('SettingsManager')) {
        $settingsManager = SettingsManager::getInstance();
        $loginFavicon = $settingsManager->get('system_favicon');
    } else {
        try {
            $faviconSql = "SELECT system_favicon FROM system_settings LIMIT 1";
            $faviconStmt = executeQuery($faviconSql);
            $faviconSettings = fetchOne($faviconStmt);
            $loginFavicon = $faviconSettings['system_favicon'] ?? null;
        } catch (Exception $e) {
            $loginFavicon = null;
        }
    }
    
    $loginFaviconUrl = !empty($loginFavicon) && file_exists(ABSPATH . $loginFavicon) 
        ? APP_URL . $loginFavicon 
        : APP_URL . 'template_extracted/assets/images/favicon.ico';
    ?>
    <link rel="shortcut icon" href="<?php echo $loginFaviconUrl; ?>">
    <link rel="icon" type="image/png" href="<?php echo $loginFaviconUrl; ?>">
    
    <!-- Vendor css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    
    <!-- App css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    
    <!-- Icons css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    
    <style>
        body.authentication-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-fluid-pages {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .auth-brand {
            text-align: center;
            padding: 30px 0;
        }
        .auth-brand img {
            max-height: 50px;
        }
        .auth-form-container {
            padding: 0 40px 40px 40px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body class="authentication-bg">

    <div class="auth-fluid-pages pb-2">
        <div class="auth-brand">
            <?php
            // Get system logo from settings
            $loginLogo = null;
            if (class_exists('SettingsManager')) {
                $settingsManager = SettingsManager::getInstance();
                $loginLogo = $settingsManager->get('system_logo');
            } else {
                try {
                    $logoSql = "SELECT system_logo FROM system_settings LIMIT 1";
                    $logoStmt = executeQuery($logoSql);
                    $logoSettings = fetchOne($logoStmt);
                    $loginLogo = $logoSettings['system_logo'] ?? null;
                } catch (Exception $e) {
                    $loginLogo = null;
                }
            }
            
            $loginLogoUrl = !empty($loginLogo) && file_exists(ABSPATH . $loginLogo) 
                ? APP_URL . $loginLogo 
                : APP_URL . 'template_extracted/assets/images/logo-dark.png';
            ?>
            <a href="<?php echo APP_URL; ?>">
                <img src="<?php echo $loginLogoUrl; ?>" alt="logo" style="max-height: 50px; width: auto;">
            </a>
        </div>
        
        <div class="auth-form-container">
            <div class="text-center mb-4">
                <h4 class="mb-1">Sign In</h4>
                <p class="text-muted">Enter your credentials to access your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-user-line"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter username or email" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-lock-line"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password" required>
                        <span class="input-group-text cursor-pointer toggle-password" toggle="#password">
                            <i class="ri-eye-off-line"></i>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>
                
                <div class="d-grid mb-3">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="ri-login-circle-line me-1"></i> Sign In
                    </button>
                </div>
                
                <div class="text-center">
                    <p class="text-muted mb-2">
                        <a href="<?php echo APP_URL; ?>forgot-password.php" class="text-muted">
                            <i class="ri-lock-unlock-line me-1"></i>Forgot your password?
                        </a>
                    </p>
                    <p class="text-muted">
                        Are you a student? 
                        <a href="<?php echo APP_URL; ?>student-login.php" class="text-primary fw-bold">Student Login</a>
                    </p>
                </div>
            </form>
            
            <!-- License Notice -->
            <div class="text-center mt-4 pt-3 border-top">
                <small class="text-muted d-block mb-2">
                    <strong><?php echo APP_NAME; ?></strong>
                    <br>Developed by <strong>Uukow Technology Solutions (UTech)</strong>
                </small>
                <small class="text-muted d-block mb-2">
                    <i class="ri-shield-check-line"></i> 
                    <strong>Proprietary Software - Unauthorized use is prohibited</strong>
                </small>
                <small class="text-muted d-block">
                    For licensing: 
                    <a href="https://uukowtech.com" target="_blank" class="text-primary">uukowtech.com</a> | 
                    <a href="mailto:info@uukowtech.com" class="text-primary">info@uukowtech.com</a> | 
                    <a href="https://wa.me/252613888976" target="_blank" class="text-primary">
                        <i class="ri-whatsapp-line"></i> +252613888976
                    </a>
                </small>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/vendor.min.js"></script>
    
    <!-- App js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/app.js"></script>
    
    <script>
        // Password toggle
        $('.toggle-password').click(function() {
            var input = $($(this).attr('toggle'));
            if (input.attr('type') == 'password') {
                input.attr('type', 'text');
                $(this).find('i').removeClass('ri-eye-off-line').addClass('ri-eye-line');
            } else {
                input.attr('type', 'password');
                $(this).find('i').removeClass('ri-eye-line').addClass('ri-eye-off-line');
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>
</html>


