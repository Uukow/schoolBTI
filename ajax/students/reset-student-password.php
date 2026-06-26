<?php
/**
 * Reset Student Password
 * 
 * Allows admin to reset a student's password
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
$studentSql = "SELECT s.*, u.id as user_id, u.username FROM students s 
               LEFT JOIN users u ON s.user_id = u.id 
               WHERE s.id = ?";
$stmt = executeQuery($studentSql, 'i', [$studentId]);
$student = fetchOne($stmt);

if (!$student) {
    jsonResponse(false, 'Student not found');
}

if (empty($student['user_id'])) {
    jsonResponse(false, 'Student does not have portal access enabled');
}

// Generate new password
$newPassword = bin2hex(random_bytes(4)); // 8 character hex password
$hashedPassword = hashPassword($newPassword);

// Update password
$updateSql = "UPDATE users SET password = ?, login_attempts = 0, locked_until = NULL WHERE id = ?";
$stmt = executeQuery($updateSql, 'si', [$hashedPassword, $student['user_id']]);

if (!$stmt) {
    jsonResponse(false, 'Failed to reset password');
}

// Log activity
logActivity(
    getCurrentUser()['id'],
    'Reset Student Password',
    'Students',
    "Reset password for student: {$student['first_name']} {$student['last_name']} (Username: {$student['username']})"
);

jsonResponse(true, 'Password reset successfully!', [
    'username' => $student['username'],
    'new_password' => $newPassword,
    'message' => 'Please provide these new credentials to the student.'
]);

