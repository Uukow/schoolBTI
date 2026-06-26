<?php
/**
 * Check Student Credentials (Diagnostic)
 * 
 * Helps diagnose login issues - checks if username exists and format
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

$username = sanitize($_POST['username'] ?? '');

if (empty($username)) {
    jsonResponse(false, 'Username is required');
}

// Check if username exists (case-insensitive)
$sql = "SELECT u.id, u.username, u.email, u.is_active, r.role_name, s.student_id, s.first_name, s.last_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN students s ON u.id = s.user_id
        WHERE (LOWER(u.username) = LOWER(?) OR LOWER(u.email) = LOWER(?))";
        
$stmt = executeQuery($sql, 'ss', [$username, $username]);
$user = fetchOne($stmt);

if (!$user) {
    jsonResponse(false, 'Username not found in database', [
        'searched_username' => $username,
        'suggestion' => 'Check if the username is correct or if the student has portal access enabled'
    ]);
}

$response = [
    'found' => true,
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role_name'],
    'is_active' => $user['is_active'] ? 'Yes' : 'No',
    'student_id' => $user['student_id'] ?? 'N/A',
    'student_name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
    'case_match' => $user['username'] === $username ? 'Exact match' : 'Case mismatch',
    'message' => 'User found. Check if password is correct or reset password if needed.'
];

jsonResponse(true, 'User account found', $response);

