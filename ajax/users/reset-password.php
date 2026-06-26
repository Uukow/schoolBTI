<?php
require_once '../../config/config.php';
require_once '../../includes/mailer.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$userId = $_POST['user_id'] ?? 0;

if (empty($userId)) jsonResponse(false, 'Invalid user ID');

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);
$user = fetchOne($stmt);

if (!$user) jsonResponse(false, 'User not found');

// Generate new password (12 characters for better security)
$newPassword = bin2hex(random_bytes(6)); // 12 character password
$hashedPassword = hashPassword($newPassword);

// Update password
$updateSql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expire = NULL WHERE id = ?";
$stmt = executeQuery($updateSql, 'si', [$hashedPassword, $userId]);

if ($stmt) {
    // Send email with new password using PHPMailer
    $emailResult = sendAdminPasswordResetEmail($user['email'], $user['username'], $newPassword);
    
    // Log activity
    logActivity(
        getCurrentUser()['id'], 
        'Reset User Password', 
        'Users', 
        "Reset password for user: {$user['username']}. Email sent: " . ($emailResult['success'] ? 'Yes' : 'No')
    );
    
    if ($emailResult['success']) {
        jsonResponse(true, 'Password reset successfully! New password has been sent to ' . $user['email'] . '. New password: ' . $newPassword);
    } else {
        // Password was reset but email failed
        jsonResponse(true, 'Password reset successfully! However, email could not be sent: ' . $emailResult['message'] . '. New password: ' . $newPassword);
    }
} else {
    jsonResponse(false, 'Failed to reset password');
}

