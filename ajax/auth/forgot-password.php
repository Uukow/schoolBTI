<?php
/**
 * AJAX: Forgot Password Request
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
    
    $email = sanitize($input['email'] ?? '');
    
    if (empty($email)) {
        jsonResponse(false, 'Email address is required');
    }
    
    if (!validateEmail($email)) {
        jsonResponse(false, 'Please enter a valid email address');
    }
    
    // Use existing function from auth.php
    $result = sendPasswordResetEmail($email);
    
    if ($result['success']) {
        jsonResponse(true, 'If the email exists in our system, a password reset link has been sent to your email address.');
    } else {
        jsonResponse(false, $result['message'] ?? 'Failed to send reset email');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Failed to process request: ' . $e->getMessage());
}

