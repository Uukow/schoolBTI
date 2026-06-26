<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$username = sanitize($_POST['username'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$roleId = $_POST['role_id'] ?? 0;
$branchId = $_POST['branch_id'] ?? null;

if (empty($username) || empty($email) || empty($password) || empty($roleId)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Use the registerUser function from auth.php
$result = registerUser([
    'username' => $username,
    'email' => $email,
    'password' => $password,
    'role_id' => $roleId,
    'branch_id' => $branchId
]);

if ($result['success']) {
    // Send welcome email if mailer is available
    if (function_exists('sendWelcomeEmail')) {
        sendWelcomeEmail($email, $username, $password);
    }
    
    logActivity(getCurrentUser()['id'], 'Add User', 'Users', "Created user: $username");
    jsonResponse(true, 'User created successfully!');
} else {
    jsonResponse(false, $result['message']);
}

