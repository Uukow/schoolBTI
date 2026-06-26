<?php
/**
 * School ERP System - Installation & Setup
 * 
 * This file handles the initial installation and configuration
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define constants before config
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');

// Start session
session_start();

// Check if already installed
if (file_exists(ABSPATH . 'config/.installed')) {
    header('Location: index.php');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 1) {
        // Database Configuration Step
        $_SESSION['db_host'] = $_POST['db_host'] ?? 'localhost';
        $_SESSION['db_user'] = $_POST['db_user'] ?? 'root';
        $_SESSION['db_pass'] = $_POST['db_pass'] ?? '';
        $_SESSION['db_name'] = $_POST['db_name'] ?? 'schoolerp_db';
        
        // Test database connection
        $conn = @new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass']);
        
        if ($conn->connect_error) {
            $error = 'Database connection failed: ' . $conn->connect_error;
        } else {
            // Create database if it doesn't exist
            $dbName = $_SESSION['db_name'];
            $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            
            if ($conn->query($sql) === TRUE) {
                $conn->close();
                header('Location: setup.php?step=2');
                exit;
            } else {
                $error = 'Failed to create database: ' . $conn->error;
            }
        }
    } elseif ($step == 2) {
        // Import Database Schema
        $conn = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
        
        if ($conn->connect_error) {
            $error = 'Database connection failed';
        } else {
            // Read and execute SQL file
            $sqlFile = ABSPATH . 'database/schema.sql';
            
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                
                // Remove comments
                $sql = preg_replace('/^--.*$/m', '', $sql);
                $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
                
                // Split into individual queries (handle multi-line statements)
                $queries = array_filter(array_map('trim', preg_split('/;[\r\n]+/', $sql)));
                
                $errorOccurred = false;
                $errorMessage = '';
                $successCount = 0;
                
                // Disable foreign key checks temporarily
                $conn->query('SET FOREIGN_KEY_CHECKS=0');
                $conn->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
                
                foreach ($queries as $query) {
                    if (!empty($query) && strlen(trim($query)) > 5) {
                        $result = $conn->query($query);
                        if ($result === false) {
                            $errorOccurred = true;
                            $errorMessage = $conn->error;
                            // Continue to show which query failed
                            $error = 'SQL Error: ' . $errorMessage . '<br><br>Query: ' . substr($query, 0, 200) . '...';
                            break;
                        } else {
                            $successCount++;
                        }
                    }
                }
                
                // Re-enable foreign key checks
                $conn->query('SET FOREIGN_KEY_CHECKS=1');
                
                if (!$errorOccurred) {
                    header('Location: setup.php?step=3');
                    exit;
                }
            } else {
                $error = 'Database schema file not found';
            }
            
            $conn->close();
        }
    } elseif ($step == 3) {
        // Create Admin Account
        $_SESSION['admin_username'] = $_POST['admin_username'] ?? '';
        $_SESSION['admin_email'] = $_POST['admin_email'] ?? '';
        $_SESSION['admin_password'] = $_POST['admin_password'] ?? '';
        $_SESSION['school_name'] = $_POST['school_name'] ?? '';
        $_SESSION['school_email'] = $_POST['school_email'] ?? '';
        
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($_SESSION['admin_username']) || empty($_SESSION['admin_email']) || empty($_SESSION['admin_password'])) {
            $error = 'All fields are required';
        } elseif ($_SESSION['admin_password'] !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($_SESSION['admin_password']) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            $conn = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
            
            if ($conn->connect_error) {
                $error = 'Database connection failed';
            } else {
                // Create default branch
                $branchSql = "INSERT INTO branches (branch_name, branch_code, address, is_active) 
                              VALUES (?, 'MAIN', 'Main Campus', 1)";
                $stmt = $conn->prepare($branchSql);
                $stmt->bind_param('s', $_SESSION['school_name']);
                $stmt->execute();
                $branchId = $conn->insert_id;
                
                // Get Super Admin role ID
                $roleSql = "SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1";
                $result = $conn->query($roleSql);
                $role = $result->fetch_assoc();
                $roleId = $role['id'];
                
                // Hash password
                $hashedPassword = password_hash($_SESSION['admin_password'], PASSWORD_DEFAULT);
                
                // Create admin user
                $userSql = "INSERT INTO users (username, email, password, role_id, branch_id, is_active, is_verified) 
                            VALUES (?, ?, ?, ?, ?, 1, 1)";
                $stmt = $conn->prepare($userSql);
                $stmt->bind_param('sssii', $_SESSION['admin_username'], $_SESSION['admin_email'], $hashedPassword, $roleId, $branchId);
                
                if ($stmt->execute()) {
                    $userId = $conn->insert_id;
                    
                    // Update system settings
                    $settingsSql = "INSERT INTO system_settings (school_name, school_email, current_session) 
                                   VALUES (?, ?, NULL)";
                    $stmt = $conn->prepare($settingsSql);
                    $stmt->bind_param('ss', $_SESSION['school_name'], $_SESSION['school_email']);
                    $stmt->execute();
                    
                    // Create academic session
                    $sessionSql = "INSERT INTO academic_sessions (session_name, start_date, end_date, is_active) 
                                  VALUES ('2025-2026', '2025-01-01', '2025-12-31', 1)";
                    $conn->query($sessionSql);
                    
                    // Update config file
                    updateConfigFile();
                    
                    // Create .installed file
                    if (!file_exists(ABSPATH . 'config')) {
                        mkdir(ABSPATH . 'config', 0755, true);
                    }
                    file_put_contents(ABSPATH . 'config/.installed', date('Y-m-d H:i:s'));
                    
                    $conn->close();
                    header('Location: setup.php?step=4');
                    exit;
                } else {
                    $error = 'Failed to create admin account: ' . $conn->error;
                }
                
                $conn->close();
            }
        }
    }
}

/**
 * Update config file with database credentials
 */
function updateConfigFile() {
    $configFile = ABSPATH . 'config/config.php';
    
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        
        // Replace database constants
        $content = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '{$_SESSION['db_host']}');", $content);
        $content = preg_replace("/define\('DB_USER', '.*?'\);/", "define('DB_USER', '{$_SESSION['db_user']}');", $content);
        $content = preg_replace("/define\('DB_PASS', '.*?'\);/", "define('DB_PASS', '{$_SESSION['db_pass']}');", $content);
        $content = preg_replace("/define\('DB_NAME', '.*?'\);/", "define('DB_NAME', '{$_SESSION['db_name']}');", $content);
        
        file_put_contents($configFile, $content);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>School ERP Setup - Installation Wizard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="template_extracted/assets/images/favicon.ico">
    
    <!-- Vendor css -->
    <link href="template_extracted/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    
    <!-- App css -->
    <link href="template_extracted/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    
    <!-- Icons css -->
    <link href="template_extracted/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            max-width: 600px;
            width: 100%;
            margin: 20px;
        }
        .setup-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .setup-body {
            padding: 30px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin: 0 5px;
            background: #f1f3fa;
            color: #6c757d;
        }
        .step.active {
            background: #727cf5;
            color: white;
        }
        .step.completed {
            background: #0acf97;
            color: white;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .btn-setup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
        }
        .btn-setup:hover {
            opacity: 0.9;
            color: white;
        }
        .success-icon {
            font-size: 80px;
            color: #0acf97;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <h2><i class="ri-settings-3-line"></i> School ERP Setup</h2>
                <p class="mb-0">Installation Wizard</p>
            </div>
            
            <div class="setup-body">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                        <i class="ri-database-2-line"></i><br>Database
                    </div>
                    <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                        <i class="ri-download-cloud-line"></i><br>Import
                    </div>
                    <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                        <i class="ri-user-add-line"></i><br>Admin
                    </div>
                    <div class="step <?php echo $step == 4 ? 'active' : ''; ?>">
                        <i class="ri-check-line"></i><br>Complete
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Step 1: Database Configuration -->
                <?php if ($step == 1): ?>
                    <h4 class="mb-3">Database Configuration</h4>
                    <p class="text-muted mb-4">Enter your database connection details</p>
                    
                    <form method="POST" action="setup.php?step=1">
                        <div class="mb-3">
                            <label class="form-label">Database Host</label>
                            <input type="text" class="form-control" name="db_host" value="localhost" required>
                            <small class="text-muted">Usually "localhost"</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Database Username</label>
                            <input type="text" class="form-control" name="db_user" value="root" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Database Password</label>
                            <input type="password" class="form-control" name="db_pass">
                            <small class="text-muted">Leave blank if no password</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Database Name</label>
                            <input type="text" class="form-control" name="db_name" value="schoolerp_db" required>
                            <small class="text-muted">Will be created if doesn't exist</small>
                        </div>
                        
                        <button type="submit" class="btn btn-setup">
                            Next <i class="ri-arrow-right-line"></i>
                        </button>
                    </form>
                <?php endif; ?>
                
                <!-- Step 2: Import Database -->
                <?php if ($step == 2): ?>
                    <h4 class="mb-3">Import Database Schema</h4>
                    <p class="text-muted mb-4">Click the button below to import the database tables</p>
                    
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> This process will create all necessary tables and default data.
                    </div>
                    
                    <form method="POST" action="setup.php?step=2">
                        <button type="submit" class="btn btn-setup">
                            Import Database <i class="ri-download-cloud-line"></i>
                        </button>
                    </form>
                <?php endif; ?>
                
                <!-- Step 3: Create Admin Account -->
                <?php if ($step == 3): ?>
                    <h4 class="mb-3">Create Admin Account</h4>
                    <p class="text-muted mb-4">Set up your administrator credentials</p>
                    
                    <form method="POST" action="setup.php?step=3">
                        <div class="mb-3">
                            <label class="form-label">School Name</label>
                            <input type="text" class="form-control" name="school_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">School Email</label>
                            <input type="email" class="form-control" name="school_email" required>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Username</label>
                            <input type="text" class="form-control" name="admin_username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Email</label>
                            <input type="email" class="form-control" name="admin_email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Password</label>
                            <input type="password" class="form-control" name="admin_password" minlength="8" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                        </div>
                        
                        <button type="submit" class="btn btn-setup">
                            Create Admin & Complete Setup <i class="ri-check-line"></i>
                        </button>
                    </form>
                <?php endif; ?>
                
                <!-- Step 4: Installation Complete -->
                <?php if ($step == 4): ?>
                    <div class="text-center">
                        <div class="success-icon">
                            <i class="ri-checkbox-circle-line"></i>
                        </div>
                        <h3 class="mb-3">Installation Complete!</h3>
                        <p class="text-muted mb-4">
                            Your School ERP System has been successfully installed and configured.
                        </p>
                        
                        <div class="alert alert-success text-start">
                            <strong>Your Login Credentials:</strong><br>
                            <strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'admin'); ?><br>
                            <strong>Password:</strong> (The password you set)<br><br>
                            <small class="text-muted">
                                <i class="ri-information-line"></i> Please save these credentials securely. You won't see them again.
                            </small>
                        </div>
                        
                        <a href="login.php" class="btn btn-setup">
                            Go to Login Page <i class="ri-login-circle-line"></i>
                        </a>
                    </div>
                    
                    <?php
                    // Clear session
                    session_destroy();
                    ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <p class="text-white">
                <small>&copy; <?php echo date('Y'); ?> School ERP System. All rights reserved.</small>
            </p>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="template_extracted/assets/js/vendor.min.js"></script>
    
    <!-- App js -->
    <script src="template_extracted/assets/js/app.js"></script>
</body>
</html>

