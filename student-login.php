<?php
/**
 * Student Login Page
 * 
 * Student-specific login page - ONLY allows students to login
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define constants
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');

// Include configuration
require_once ABSPATH . 'config/config.php';

// If already logged in as student, redirect to student dashboard
if (isLoggedIn() && hasRole(['Student'])) {
    redirect(APP_URL . 'modules/student/dashboard.php');
}

// If logged in as non-student, redirect to main dashboard
if (isLoggedIn() && !hasRole(['Student'])) {
    $_SESSION['error'] = 'You are logged in as a different user type. Please logout first to access the student portal.';
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
        // Check if user exists and is a student (case-insensitive)
        $checkStudentSql = "SELECT u.id, u.username, u.locked_until, r.role_name
                           FROM users u
                           LEFT JOIN roles r ON u.role_id = r.id
                           WHERE (LOWER(u.username) = LOWER(?) OR LOWER(u.email) = LOWER(?)) AND u.is_active = 1";
        
        $stmt = executeQuery($checkStudentSql, 'ss', [$username, $username]);
        $userCheck = fetchOne($stmt);
        
        if (!$userCheck) {
            $error = 'Invalid username or password. Please check your credentials and try again.';
        } elseif ($userCheck['role_name'] !== 'Student') {
            $error = 'Only students can access the student portal. Please use the staff/admin login page.';
        } else {
            // Use the actual username from database
            $actualUsername = $userCheck['username'];
            
            // Check if account is locked
            if ($userCheck['locked_until'] && strtotime($userCheck['locked_until']) > time()) {
                $remainingTime = ceil((strtotime($userCheck['locked_until']) - time()) / 60);
                $error = "Account is locked. Try again in {$remainingTime} minutes.";
            } else {
                // Get full user details with password for verification
                $fullUserSql = "SELECT u.*, r.role_name 
                               FROM users u 
                               LEFT JOIN roles r ON u.role_id = r.id 
                               WHERE u.id = ? AND u.is_active = 1";
                $stmt = executeQuery($fullUserSql, 'i', [$userCheck['id']]);
                $fullUser = fetchOne($stmt);
                
                if (!$fullUser) {
                    $error = 'Invalid username or password';
                } elseif ($fullUser['role_name'] !== 'Student') {
                    $error = 'Only students can access the student portal.';
                } else {
                    // Verify password directly
                    if (!verifyPassword($password, $fullUser['password'])) {
                        // Increment login attempts
                        $attempts = $fullUser['login_attempts'] + 1;
                        $lockedUntil = null;
                        
                        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                            $lockedUntil = date('Y-m-d H:i:s', time() + ACCOUNT_LOCKOUT_TIME);
                            $updateSql = "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?";
                            executeQuery($updateSql, 'isi', [$attempts, $lockedUntil, $fullUser['id']]);
                            $error = 'Account locked due to multiple failed attempts. Try again later.';
                        } else {
                            $updateSql = "UPDATE users SET login_attempts = ? WHERE id = ?";
                            executeQuery($updateSql, 'ii', [$attempts, $fullUser['id']]);
                            $remainingAttempts = MAX_LOGIN_ATTEMPTS - $attempts;
                            $error = "Invalid username or password. {$remainingAttempts} attempts remaining.";
                        }
                    } else {
                        // Password is correct - proceed with login
                        // Reset login attempts
                        $updateSql = "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?";
                        executeQuery($updateSql, 'i', [$fullUser['id']]);
                        
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['user_id'] = $fullUser['id'];
                        $_SESSION['username'] = $fullUser['username'];
                        $_SESSION['role_id'] = $fullUser['role_id'];
                        $_SESSION['role_name'] = $fullUser['role_name'];
                        $_SESSION['branch_id'] = $fullUser['branch_id'];
                        $_SESSION['user_data'] = $fullUser;
                        $_SESSION['login_time'] = time();
                        
                        // Log activity
                        logActivity($fullUser['id'], 'Student Login', 'Authentication', 'Student logged in successfully');
                        
                        // Redirect to student dashboard
                        $redirectUrl = $_GET['redirect'] ?? APP_URL . 'modules/student/dashboard.php';
                        redirect($redirectUrl);
                    }
                }
            }
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
    <title>Student Login - <?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- App favicon -->
    <?php
    // Get system favicon from settings
    $studentLoginFavicon = null;
    if (class_exists('SettingsManager')) {
        $settingsManager = SettingsManager::getInstance();
        $studentLoginFavicon = $settingsManager->get('system_favicon');
    } else {
        try {
            $faviconSql = "SELECT system_favicon FROM system_settings LIMIT 1";
            $faviconStmt = executeQuery($faviconSql);
            $faviconSettings = fetchOne($faviconStmt);
            $studentLoginFavicon = $faviconSettings['system_favicon'] ?? null;
        } catch (Exception $e) {
            $studentLoginFavicon = null;
        }
    }
    
    $studentLoginFaviconUrl = !empty($studentLoginFavicon) && file_exists(ABSPATH . $studentLoginFavicon) 
        ? APP_URL . $studentLoginFavicon 
        : APP_URL . 'template_extracted/assets/images/favicon.ico';
    ?>
    <link rel="shortcut icon" href="<?php echo $studentLoginFaviconUrl; ?>">
    <link rel="icon" type="image/png" href="<?php echo $studentLoginFaviconUrl; ?>">
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
        .student-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
            font-weight: 600;
        }
    </style>
</head>

<body class="authentication-bg">

    <div class="auth-fluid-pages pb-2">
        <div class="auth-brand">
            <?php
            // Get system logo from settings
            $studentLoginLogo = null;
            if (class_exists('SettingsManager')) {
                $settingsManager = SettingsManager::getInstance();
                $studentLoginLogo = $settingsManager->get('system_logo');
            } else {
                try {
                    $logoSql = "SELECT system_logo FROM system_settings LIMIT 1";
                    $logoStmt = executeQuery($logoSql);
                    $logoSettings = fetchOne($logoStmt);
                    $studentLoginLogo = $logoSettings['system_logo'] ?? null;
                } catch (Exception $e) {
                    $studentLoginLogo = null;
                }
            }
            
            $studentLoginLogoUrl = !empty($studentLoginLogo) && file_exists(ABSPATH . $studentLoginLogo) 
                ? APP_URL . $studentLoginLogo 
                : APP_URL . 'template_extracted/assets/images/logo-dark.png';
            ?>
            <a href="<?php echo APP_URL; ?>" style="color: white; text-decoration: none;">
                <img src="<?php echo $studentLoginLogoUrl; ?>" alt="logo" style="max-height: 50px; width: auto;">
                <div class="mt-2">
                    <h4 class="mb-0">Student Portal</h4>
                    <span class="student-badge">
                        <i class="ri-graduation-cap-line"></i> Student Login
                    </span>
                </div>
            </a>
        </div>
        
        <div class="auth-form-container">
            <div class="text-center mb-4">
                <h4 class="mb-1">Welcome Back!</h4>
                <p class="text-muted">Sign in to access your student portal</p>
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
            
            <form method="POST" action="student-login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
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
                        Need help? 
                        <a href="<?php echo APP_URL; ?>modules/support/create-ticket.php" class="text-primary">Contact Support</a>
                    </p>
                    <hr class="my-3">
                    <p class="text-muted mb-0">
                        <small>
                            Staff or Admin? 
                            <a href="<?php echo APP_URL; ?>login.php" class="text-muted">Login here</a>
                        </small>
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

