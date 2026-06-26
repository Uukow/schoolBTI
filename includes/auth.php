<?php
/**
 * Authentication System
 * 
 * Handles user authentication, session management, and access control
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 * 
 * @return array|null User data or null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ?";
    
    $stmt = executeQuery($sql, 'i', [$_SESSION['user_id']]);
    $user = fetchOne($stmt);
    
    if ($user) {
        $_SESSION['user_data'] = $user;
        return $user;
    }
    
    return null;
}

/**
 * Check if user has specific role
 * 
 * @param string|array $roles Role name(s) to check
 * @return bool True if user has role, false otherwise
 */
function hasRole($roles) {
    $user = getCurrentUser();
    
    if (!$user) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($user['role_name'], $roles);
    }
    
    return $user['role_name'] === $roles;
}

/**
 * Require login (redirect if not logged in)
 * 
 * @param string $redirectUrl URL to redirect to if not logged in
 */
function requireLogin($redirectUrl = null) {
    if (!isLoggedIn()) {
        if ($redirectUrl === null) {
            $redirectUrl = APP_URL . 'login.php?redirect=' . urlencode(getCurrentUrl());
        }
        redirect($redirectUrl);
    }
}

/**
 * Require specific role
 * 
 * @param string|array $roles Required role(s)
 * @param string $redirectUrl URL to redirect to if unauthorized
 */
function requireRole($roles, $redirectUrl = null) {
    requireLogin();
    
    if (!hasRole($roles)) {
        if ($redirectUrl === null) {
            $redirectUrl = APP_URL . 'dashboard.php';
        }
        
        $_SESSION['error'] = 'You do not have permission to access this page.';
        redirect($redirectUrl);
    }
}

/**
 * Login user
 * 
 * @param string $username Username or email
 * @param string $password Password
 * @return array Result with success status and message
 */
function loginUser($username, $password) {
    global $conn;
    
    try {
        // Check if account is locked
        $lockCheckSql = "SELECT id, username, locked_until FROM users 
                         WHERE (username = ? OR email = ?) AND is_active = 1";
        
        $stmt = executeQuery($lockCheckSql, 'ss', [$username, $username]);
        
        if ($stmt === false) {
            error_log("loginUser: Database query failed");
            return ['success' => false, 'message' => 'Database error. Please try again later.'];
        }
        
        $user = fetchOne($stmt);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // Check if account is locked
        if (isset($user['locked_until']) && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remainingTime = ceil((strtotime($user['locked_until']) - time()) / 60);
            return [
                'success' => false, 
                'message' => "Account is locked. Try again in {$remainingTime} minutes."
            ];
        }
    
        // Get full user details with password
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1";
        
        $stmt = executeQuery($sql, 'ss', [$username, $username]);
        
        if ($stmt === false) {
            error_log("loginUser: Database query failed for full user details");
            return ['success' => false, 'message' => 'Database error. Please try again later.'];
        }
        
        $user = fetchOne($stmt);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // Verify password
        if (!isset($user['password']) || empty($user['password'])) {
            error_log("loginUser: User password field is missing or empty for user ID: " . (isset($user['id']) ? $user['id'] : 'unknown'));
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        if (!function_exists('verifyPassword')) {
            error_log("loginUser: verifyPassword function not found");
            return ['success' => false, 'message' => 'System error: Password verification function not available'];
        }
        
        if (!verifyPassword($password, $user['password'])) {
            // Increment login attempts
            $attempts = (isset($user['login_attempts']) ? $user['login_attempts'] : 0) + 1;
            $lockedUntil = null;
            
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + ACCOUNT_LOCKOUT_TIME);
                $updateSql = "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?";
                executeQuery($updateSql, 'isi', [$attempts, $lockedUntil, $user['id']]);
                
                return [
                    'success' => false, 
                    'message' => 'Account locked due to multiple failed attempts. Try again later.'
                ];
            } else {
                $updateSql = "UPDATE users SET login_attempts = ? WHERE id = ?";
                executeQuery($updateSql, 'ii', [$attempts, $user['id']]);
                
                $remainingAttempts = MAX_LOGIN_ATTEMPTS - $attempts;
                return [
                    'success' => false, 
                    'message' => "Invalid username or password. {$remainingAttempts} attempts remaining."
                ];
            }
        }
        
        // Check if email is verified (if verification is enabled)
        if (isset($user['is_verified']) && !$user['is_verified'] && (defined('REQUIRE_EMAIL_VERIFICATION') && constant('REQUIRE_EMAIL_VERIFICATION'))) {
            return [
                'success' => false, 
                'message' => 'Please verify your email address before logging in.'
            ];
        }
    
        // Reset login attempts and update last login
        $updateSql = "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?";
        executeQuery($updateSql, 'i', [$user['id']]);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = isset($user['role_name']) ? $user['role_name'] : '';
        $_SESSION['branch_id'] = isset($user['branch_id']) ? $user['branch_id'] : null;
        $_SESSION['user_data'] = $user;
        $_SESSION['login_time'] = time();
        
        // Log activity (don't fail if logging fails)
        if (function_exists('logActivity')) {
            try {
                logActivity($user['id'], 'Login', 'Authentication', 'User logged in successfully');
            } catch (Exception $e) {
                error_log("Activity logging failed: " . $e->getMessage());
            }
        }
        
        return ['success' => true, 'message' => 'Login successful'];
        
    } catch (Exception $e) {
        error_log("loginUser exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        error_log("loginUser stack trace: " . $e->getTraceAsString());
        return ['success' => false, 'message' => 'An error occurred during login. Please try again.'];
    } catch (Error $e) {
        error_log("loginUser fatal error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        error_log("loginUser stack trace: " . $e->getTraceAsString());
        return ['success' => false, 'message' => 'A system error occurred. Please contact the administrator.'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'Logout', 'Authentication', 'User logged out');
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    // Redirect to login
    redirect(APP_URL . 'login.php');
}

/**
 * Register new user
 * 
 * @param array $data User data
 * @return array Result with success status and message
 */
function registerUser($data) {
    // Validate required fields
    $required = ['username', 'email', 'password', 'role_id'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => ucfirst($field) . ' is required'];
        }
    }
    
    // Validate email
    if (!validateEmail($data['email'])) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    // Validate password length
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        return [
            'success' => false, 
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'
        ];
    }
    
    // Check if username exists
    $checkSql = "SELECT id FROM users WHERE username = ?";
    $stmt = executeQuery($checkSql, 's', [$data['username']]);
    
    if (fetchOne($stmt)) {
        return ['success' => false, 'message' => 'Username already exists'];
    }
    
    // Check if email exists
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $stmt = executeQuery($checkSql, 's', [$data['email']]);
    
    if (fetchOne($stmt)) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Hash password
    $hashedPassword = hashPassword($data['password']);
    
    // Generate verification token
    $verificationToken = generateToken();
    
    // Insert user
    $sql = "INSERT INTO users (username, email, password, role_id, branch_id, verification_token) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $branchId = $data['branch_id'] ?? null;
    
    $stmt = executeQuery($sql, 'ssiiss', [
        $data['username'],
        $data['email'],
        $hashedPassword,
        $data['role_id'],
        $branchId,
        $verificationToken
    ]);
    
    if ($stmt) {
        $userId = getLastInsertId();
        
        // Log activity
        logActivity($userId, 'Register', 'Authentication', 'New user registered');
        
        return [
            'success' => true, 
            'message' => 'Registration successful',
            'user_id' => $userId,
            'verification_token' => $verificationToken
        ];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}

/**
 * Send password reset email
 * 
 * @param string $email Email address
 * @return array Result with success status and message
 */
function sendPasswordResetEmail($email) {
    // Check if email exists
    $sql = "SELECT id, username, email FROM users WHERE email = ? AND is_active = 1";
    $stmt = executeQuery($sql, 's', [$email]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        return ['success' => true, 'message' => 'If email exists, reset link will be sent'];
    }
    
    // Generate reset token
    $resetToken = generateToken();
    $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
    
    // Update user with reset token
    $updateSql = "UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE id = ?";
    executeQuery($updateSql, 'ssi', [$resetToken, $expiry, $user['id']]);
    
    // Send email (implement with PHPMailer)
    $resetLink = APP_URL . 'reset-password.php?token=' . $resetToken;
    
    // TODO: Send actual email
    // sendEmail($user['email'], 'Password Reset', 'Click here to reset: ' . $resetLink);
    
    // Log activity
    logActivity($user['id'], 'Password Reset Request', 'Authentication', 'Password reset requested');
    
    return ['success' => true, 'message' => 'If email exists, reset link will be sent'];
}

/**
 * Reset password with token
 * 
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return array Result with success status and message
 */
function resetPassword($token, $newPassword) {
    // Validate password length
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return [
            'success' => false, 
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'
        ];
    }
    
    // Check if token is valid
    $sql = "SELECT id FROM users 
            WHERE reset_token = ? AND reset_token_expire > NOW() AND is_active = 1";
    
    $stmt = executeQuery($sql, 's', [$token]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid or expired reset token'];
    }
    
    // Hash new password
    $hashedPassword = hashPassword($newPassword);
    
    // Update password and clear reset token
    $updateSql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expire = NULL WHERE id = ?";
    $stmt = executeQuery($updateSql, 'si', [$hashedPassword, $user['id']]);
    
    if ($stmt) {
        // Log activity
        logActivity($user['id'], 'Password Reset', 'Authentication', 'Password reset successful');
        
        return ['success' => true, 'message' => 'Password reset successful'];
    }
    
    return ['success' => false, 'message' => 'Password reset failed'];
}

/**
 * Change password for logged in user
 * 
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return array Result with success status and message
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Get user's current password
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    // Verify current password
    if (!verifyPassword($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Validate new password length
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return [
            'success' => false, 
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'
        ];
    }
    
    // Hash new password
    $hashedPassword = hashPassword($newPassword);
    
    // Update password
    $updateSql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = executeQuery($updateSql, 'si', [$hashedPassword, $userId]);
    
    if ($stmt) {
        // Log activity
        logActivity($userId, 'Password Change', 'Authentication', 'Password changed successfully');
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    }
    
    return ['success' => false, 'message' => 'Password change failed'];
}

/**
 * Verify email address
 * 
 * @param string $token Verification token
 * @return array Result with success status and message
 */
function verifyEmail($token) {
    $sql = "SELECT id FROM users WHERE verification_token = ? AND is_active = 1";
    $stmt = executeQuery($sql, 's', [$token]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid verification token'];
    }
    
    // Update user as verified
    $updateSql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?";
    $stmt = executeQuery($updateSql, 'i', [$user['id']]);
    
    if ($stmt) {
        logActivity($user['id'], 'Email Verification', 'Authentication', 'Email verified successfully');
        return ['success' => true, 'message' => 'Email verified successfully'];
    }
    
    return ['success' => false, 'message' => 'Email verification failed'];
}

/**
 * Check if current user can perform an action on a module
 * 
 * @param string $moduleKey Module key (e.g., 'students', 'fees')
 * @param string $actionKey Action key (e.g., 'create', 'view', 'update', 'delete')
 * @return bool True if user has permission
 */
function canPerform($moduleKey, $actionKey) {
    if (!class_exists('PermissionManager')) {
        require_once ABSPATH . 'includes/PermissionManager.php';
    }
    return PermissionManager::canPerform(null, $moduleKey, $actionKey);
}

/**
 * Require permission (redirect if user doesn't have permission)
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @param string $redirectUrl URL to redirect to if unauthorized
 */
function requirePermission($moduleKey, $actionKey, $redirectUrl = null) {
    requireLogin();
    
    if (!canPerform($moduleKey, $actionKey)) {
        if ($redirectUrl === null) {
            $redirectUrl = APP_URL . 'dashboard.php';
        }
        
        $_SESSION['error'] = 'You do not have permission to perform this action.';
        redirect($redirectUrl);
    }
}

/**
 * Check if user can perform multiple actions (all must be true)
 * 
 * @param string $moduleKey Module key
 * @param array $actionKeys Array of action keys
 * @return bool True if user has all permissions
 */
function canPerformAll($moduleKey, $actionKeys) {
    foreach ($actionKeys as $actionKey) {
        if (!canPerform($moduleKey, $actionKey)) {
            return false;
        }
    }
    return true;
}

/**
 * Check if user can perform any of the actions (at least one must be true)
 * 
 * @param string $moduleKey Module key
 * @param array $actionKeys Array of action keys
 * @return bool True if user has at least one permission
 */
function canPerformAny($moduleKey, $actionKeys) {
    foreach ($actionKeys as $actionKey) {
        if (canPerform($moduleKey, $actionKey)) {
            return true;
        }
    }
    return false;
}

/**
 * Get all permissions for current user
 * 
 * @return array Array of permissions [module_key => [action_key => granted]]
 */
function getUserPermissions() {
    $user = getCurrentUser();
    if (!$user) {
        return [];
    }
    
    if (!class_exists('PermissionManager')) {
        require_once ABSPATH . 'includes/PermissionManager.php';
    }
    
    return PermissionManager::getUserPermissions($user['id']);
}


