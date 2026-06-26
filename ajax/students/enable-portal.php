<?php
/**
 * Enable Student Portal Access
 * 
 * Creates user account for student and links it to student record
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$studentId = $_POST['student_id'] ?? 0;

if (empty($studentId)) {
    jsonResponse(false, 'Student ID is required');
}

// Get student details
$studentSql = "SELECT * FROM students WHERE id = ?";
$stmt = executeQuery($studentSql, 'i', [$studentId]);
$student = fetchOne($stmt);

if (!$student) {
    jsonResponse(false, 'Student not found');
}

// Check if student already has portal access
if (!empty($student['user_id'])) {
    jsonResponse(false, 'Student already has portal access enabled');
}

// Check if email is required and exists
if (empty($student['email'])) {
    jsonResponse(false, 'Student must have an email address to enable portal access');
}

// Get Student role ID
$roleSql = "SELECT id FROM roles WHERE role_name = 'Student' LIMIT 1";
$stmt = executeQuery($roleSql);
$role = fetchOne($stmt);

if (!$role) {
    jsonResponse(false, 'Student role not found. Please contact system administrator.');
}

$studentRoleId = $role['id'];

// Generate username from student ID or email
$username = strtolower(str_replace(' ', '', $student['student_id']));
$email = $student['email'];

// Check if username already exists
$checkUsernameSql = "SELECT id FROM users WHERE username = ?";
$stmt = executeQuery($checkUsernameSql, 's', [$username]);
if (fetchOne($stmt)) {
    // Username exists, try with email prefix
    $emailPrefix = explode('@', $email)[0];
    $username = $emailPrefix . '_' . substr($student['student_id'], -4);
    
    // Check again
    $stmt = executeQuery($checkUsernameSql, 's', [$username]);
    if (fetchOne($stmt)) {
        $username = $emailPrefix . '_' . time();
    }
}

// Check if email already exists in users
$checkEmailSql = "SELECT id FROM users WHERE email = ?";
$stmt = executeQuery($checkEmailSql, 's', [$email]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Email address is already registered. Please use a different email or contact administrator.');
}

// Generate temporary password (student will be asked to change on first login)
$tempPassword = generateToken(8); // 16 character random password
$hashedPassword = hashPassword($tempPassword);

// Begin transaction
beginTransaction();

try {
    // Insert user account
    $userSql = "INSERT INTO users (username, email, password, role_id, branch_id, is_active, is_verified) 
                VALUES (?, ?, ?, ?, ?, 1, 1)";
    
    $branchId = $student['branch_id'];
    
    $stmt = executeQuery($userSql, 'ssiii', [
        $username,
        $email,
        $hashedPassword,
        $studentRoleId,
        $branchId
    ]);
    
    if (!$stmt) {
        throw new Exception('Failed to create user account');
    }
    
    $userId = getLastInsertId();
    
    // Link user account to student record
    $updateStudentSql = "UPDATE students SET user_id = ? WHERE id = ?";
    $updateStmt = executeQuery($updateStudentSql, 'ii', [$userId, $studentId]);
    
    if (!$updateStmt) {
        throw new Exception('Failed to link user account to student');
    }
    
    // Log activity
    logActivity(
        getCurrentUser()['id'],
        'Enable Student Portal',
        'Students',
        "Enabled portal access for student: {$student['first_name']} {$student['last_name']} (ID: {$student['student_id']})"
    );
    
    commitTransaction();
    
    // Return success with credentials (for admin to provide to student)
    jsonResponse(true, 'Portal access enabled successfully!', [
        'username' => $username,
        'temp_password' => $tempPassword,
        'message' => 'Please provide these credentials to the student. They should change their password on first login.'
    ]);
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

