<?php
/**
 * Login API Endpoint
 * 
 * Handles JSON login requests from Flutter mobile app
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
    exit;
}

try {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    // Validate input
    if (empty($input['username']) || empty($input['password'])) {
        jsonResponse(false, 'Username and password are required');
        exit;
    }
    
    $username = sanitize($input['username']);
    $password = $input['password'];
    
    // Check if account is locked
    $lockCheckSql = "SELECT id, username, locked_until FROM users 
                     WHERE (username = ? OR email = ?) AND is_active = 1";
    
    $stmt = executeQuery($lockCheckSql, 'ss', [$username, $username]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        jsonResponse(false, 'Invalid username or password');
        exit;
    }
    
    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        $remainingTime = ceil((strtotime($user['locked_until']) - time()) / 60);
        jsonResponse(false, "Account is locked. Try again in {$remainingTime} minutes.");
        exit;
    }
    
    // Get full user details with password
    $sql = "SELECT u.*, r.role_name,
            CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as full_name,
            s.id as staff_id,
            st.id as student_id
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN staff s ON u.id = s.user_id
            LEFT JOIN students st ON u.id = st.user_id
            WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1";
    
    $stmt = executeQuery($sql, 'ss', [$username, $username]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        jsonResponse(false, 'Invalid username or password');
        exit;
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        $attempts = ($user['login_attempts'] ?? 0) + 1;
        $lockedUntil = null;
        
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + ACCOUNT_LOCKOUT_TIME);
            $updateSql = "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?";
            executeQuery($updateSql, 'isi', [$attempts, $lockedUntil, $user['id']]);
            
            jsonResponse(false, 'Account locked due to multiple failed attempts. Try again later.');
            exit;
        } else {
            $updateSql = "UPDATE users SET login_attempts = ? WHERE id = ?";
            executeQuery($updateSql, 'ii', [$attempts, $user['id']]);
            
            $remainingAttempts = MAX_LOGIN_ATTEMPTS - $attempts;
            jsonResponse(false, "Invalid username or password. {$remainingAttempts} attempts remaining.");
            exit;
        }
    }
    
    // Check if email is verified (if verification is enabled)
    if (!$user['is_verified'] && defined('REQUIRE_EMAIL_VERIFICATION') && REQUIRE_EMAIL_VERIFICATION) {
        jsonResponse(false, 'Please verify your email address before logging in.');
        exit;
    }
    
    // Reset login attempts and update last login
    $updateSql = "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?";
    executeQuery($updateSql, 'i', [$user['id']]);
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables (for web access if needed)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['branch_id'] = $user['branch_id'];
    
    // Prepare user data for response
    $userData = [
        'user_id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role_name'],
        'branch_id' => $user['branch_id'] ? (int)$user['branch_id'] : null,
        'full_name' => trim($user['full_name'] ?? $user['username']),
        'profile_image' => $user['profile_image'] ?? null,
        'staff_id' => $user['staff_id'] ? (int)$user['staff_id'] : null,
        'student_id' => $user['student_id'] ? (int)$user['student_id'] : null,
    ];
    
    // Return success with user data
    jsonResponse(true, 'Login successful', $userData);
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred during login. Please try again.');
    exit;
}

