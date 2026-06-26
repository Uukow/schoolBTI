<?php
/**
 * Forgot Password Page
 * 
 * Request password reset
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'config/config.php';

// If already logged in, redirect
if (isLoggedIn()) {
    redirect(APP_URL . 'dashboard.php');
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Email address is required';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $result = sendPasswordResetEmail($email);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>template_extracted/assets/images/favicon.ico">
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
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
            max-width: 450px;
            width: 100%;
        }
        .auth-brand { text-align: center; padding: 30px 0; }
        .auth-form-container { padding: 0 40px 40px 40px; }
    </style>
</head>

<body class="authentication-bg">
    <div class="auth-fluid-pages pb-2">
        <div class="auth-brand">
            <a href="<?php echo APP_URL; ?>">
                <img src="<?php echo APP_URL; ?>template_extracted/assets/images/logo-dark.png" alt="logo" height="50">
            </a>
        </div>
        
        <div class="auth-form-container">
            <div class="text-center mb-4">
                <h4 class="mb-1">Forgot Password?</h4>
                <p class="text-muted">Enter your email and we'll send you reset instructions</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="ri-check-line me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Enter your email" required autofocus>
                    </div>
                </div>
                
                <div class="d-grid mb-3">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="ri-mail-send-line me-1"></i> Send Reset Link
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="<?php echo APP_URL; ?>login.php" class="text-muted">
                        <i class="ri-arrow-left-line me-1"></i>Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/vendor.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/app.js"></script>
</body>
</html>

