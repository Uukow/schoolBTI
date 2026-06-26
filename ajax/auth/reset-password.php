<?php
/**
 * AJAX: Reset Password with Token
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

try {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    $token = sanitize($input['token'] ?? '');
    $newPassword = $input['password'] ?? '';
    
    if (empty($token)) {
        jsonResponse(false, 'Reset token is required');
    }
    
    if (empty($newPassword)) {
        jsonResponse(false, 'New password is required');
    }
    
    // Validate password length
    if (strlen($newPassword) < 6) {
        jsonResponse(false, 'Password must be at least 6 characters long');
    }
    
    // Use existing function from auth.php
    $result = resetPassword($token, $newPassword);
    
    if ($result['success']) {
        jsonResponse(true, 'Password has been reset successfully. You can now login with your new password.');
    } else {
        jsonResponse(false, $result['message'] ?? 'Failed to reset password');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Failed to process request: ' . $e->getMessage());
}

