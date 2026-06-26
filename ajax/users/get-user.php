<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$userId = $_POST['user_id'] ?? 0;

if (empty($userId)) jsonResponse(false, 'Invalid user ID');

// Get user data with linked staff
$sql = "SELECT u.*, r.role_name, b.branch_name, s.id as staff_id
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN branches b ON u.branch_id = b.id
        LEFT JOIN staff s ON s.user_id = u.id
        WHERE u.id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);
$user = fetchOne($stmt);

if (!$user) {
    jsonResponse(false, 'User not found');
}

// Remove sensitive data
unset($user['password']);

jsonResponse(true, 'User data retrieved successfully', $user);

