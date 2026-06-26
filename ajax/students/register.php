<?php
/**
 * Student Self-Registration
 * 
 * Allows students to register themselves for the portal
 * Creates both student record and user account
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$username = sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$branchId = $_POST['branch_id'] ?? '';
$gender = $_POST['gender'] ?? '';
$dateOfBirth = $_POST['date_of_birth'] ?? '';
$phone = sanitize($_POST['phone'] ?? '');
$address = sanitize($_POST['address'] ?? '');

// Validation
$errors = [];
if (empty($firstName)) $errors[] = 'First name is required';
if (empty($lastName)) $errors[] = 'Last name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($username)) $errors[] = 'Username is required';
if (empty($password)) $errors[] = 'Password is required';
if (empty($branchId)) $errors[] = 'Branch is required';
if (empty($gender)) $errors[] = 'Gender is required';
if (empty($dateOfBirth)) $errors[] = 'Date of birth is required';

if (!empty($errors)) {
    jsonResponse(false, implode(', ', $errors));
}

// Validate email format
if (!validateEmail($email)) {
    jsonResponse(false, 'Invalid email address');
}

// Validate password length
if (strlen($password) < PASSWORD_MIN_LENGTH) {
    jsonResponse(false, 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
}

// Get Student role ID
$roleSql = "SELECT id FROM roles WHERE role_name = 'Student' LIMIT 1";
$stmt = executeQuery($roleSql);
$role = fetchOne($stmt);

if (!$role) {
    jsonResponse(false, 'Student role not found. Please contact administrator.');
}

$studentRoleId = $role['id'];

// Check if username exists
$checkUsernameSql = "SELECT id FROM users WHERE username = ?";
$stmt = executeQuery($checkUsernameSql, 's', [$username]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Username already exists. Please choose a different username.');
}

// Check if email exists
$checkEmailSql = "SELECT id FROM users WHERE email = ?";
$stmt = executeQuery($checkEmailSql, 's', [$email]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Email address is already registered.');
}

// Check if student with this email already exists
$checkStudentSql = "SELECT id FROM students WHERE email = ?";
$stmt = executeQuery($checkStudentSql, 's', [$email]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'A student with this email already exists. Please contact administrator.');
}

// Hash password
$hashedPassword = hashPassword($password);

// Begin transaction
beginTransaction();

try {
    // Generate student ID
    $lastStudentSql = "SELECT MAX(id) as max_id FROM students";
    $result = executeQuery($lastStudentSql);
    $row = fetchOne($result);
    $nextStudentNum = ($row['max_id'] ?? 0) + 1;
    $studentId = generateUniqueId(STUDENT_ID_PREFIX, $nextStudentNum, 6);
    
    // Generate admission number
    $year = date('Y');
    $lastAdmissionSql = "SELECT admission_no FROM students WHERE admission_no LIKE ? ORDER BY id DESC LIMIT 1";
    $stmt = executeQuery($lastAdmissionSql, 's', ["ADM{$year}%"]);
    $lastAdmission = fetchOne($stmt);
    
    if ($lastAdmission) {
        $lastNum = intval(substr($lastAdmission['admission_no'], -5));
        $nextAdmissionNum = $lastNum + 1;
    } else {
        $nextAdmissionNum = 1;
    }
    $admissionNo = 'ADM' . $year . str_pad($nextAdmissionNum, 5, '0', STR_PAD_LEFT);
    
    // Insert user account first
    $userSql = "INSERT INTO users (username, email, password, role_id, branch_id, is_active, is_verified) 
                VALUES (?, ?, ?, ?, ?, 1, 1)";
    
    $userStmt = executeQuery($userSql, 'ssiii', [
        $username,
        $email,
        $hashedPassword,
        $studentRoleId,
        $branchId
    ]);
    
    if (!$userStmt) {
        throw new Exception('Failed to create user account');
    }
    
    $userId = getLastInsertId();
    
    // Insert student record
    $studentSql = "INSERT INTO students (
        user_id, student_id, admission_no, branch_id, first_name, last_name,
        gender, date_of_birth, email, phone, address, admission_date, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'Active')";
    
    $studentStmt = executeQuery($studentSql, 'ississsssss', [
        $userId,
        $studentId,
        $admissionNo,
        $branchId,
        $firstName,
        $lastName,
        $gender,
        $dateOfBirth,
        $email,
        $phone,
        $address
    ]);
    
    if (!$studentStmt) {
        // Rollback: Delete user if student creation failed
        $deleteUserSql = "DELETE FROM users WHERE id = ?";
        executeQuery($deleteUserSql, 'i', [$userId]);
        throw new Exception('Failed to create student record');
    }
    
    // Log activity (use system user or 0 if not available)
    if (function_exists('logActivity') && defined('SYSTEM_USER_ID')) {
        logActivity(SYSTEM_USER_ID ?? 0, 'Student Self-Register', 'Students', "Student registered: {$firstName} {$lastName} (ID: {$studentId})");
    }
    
    commitTransaction();
    
    jsonResponse(true, 'Registration successful! You can now login to the student portal.', [
        'student_id' => $studentId,
        'admission_no' => $admissionNo,
        'username' => $username
    ]);
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Registration failed: ' . $e->getMessage());
}

