<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$userId = $_POST['user_id'] ?? 0;
$username = sanitize($_POST['username'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$roleId = $_POST['role_id'] ?? 0;
$branchId = $_POST['branch_id'] ?? null;
$staffId = $_POST['staff_id'] ?? null;
$isActive = isset($_POST['is_active']) ? 1 : 0;

if (empty($userId) || empty($username) || empty($email) || empty($roleId)) {
    jsonResponse(false, 'All required fields must be filled');
}

// Check if user exists
$checkSql = "SELECT id, username, email FROM users WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$userId]);
$existingUser = fetchOne($checkStmt);

if (!$existingUser) {
    jsonResponse(false, 'User not found');
}

// Check if username or email already exists for another user
$duplicateSql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
$duplicateStmt = executeQuery($duplicateSql, 'ssi', [$username, $email, $userId]);
$duplicate = fetchOne($duplicateStmt);

if ($duplicate) {
    jsonResponse(false, 'Username or email already exists');
}

// Build update query
$updateFields = ['username = ?', 'email = ?', 'role_id = ?', 'branch_id = ?', 'is_active = ?'];
$branchIdValue = ($branchId !== null && $branchId !== '') ? (int)$branchId : null;
$params = [$username, $email, $roleId, $branchIdValue, $isActive];
$types = 'ssiii';

// Update password if provided
if (!empty($password)) {
    if (strlen($password) < 8) {
        jsonResponse(false, 'Password must be at least 8 characters');
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $updateFields[] = 'password = ?';
    $params[] = $hashedPassword;
    $types .= 's';
}

$params[] = (int)$userId;
$types .= 'i';

$sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
$stmt = executeQuery($sql, $types, $params);

if ($stmt) {
    // Handle staff linking
    if ($staffId !== null && $staffId !== '') {
        $staffId = (int)$staffId;
        
        // First, unlink any existing staff records linked to this user
        $unlinkSql = "UPDATE staff SET user_id = NULL WHERE user_id = ?";
        executeQuery($unlinkSql, 'i', [$userId]);
        
        // Link the selected staff record to this user
        $linkSql = "UPDATE staff SET user_id = ? WHERE id = ?";
        $linkStmt = executeQuery($linkSql, 'ii', [$userId, $staffId]);
        
        if ($linkStmt) {
            logActivity(getCurrentUser()['id'], 'Link Staff to User', 'Users', "Linked staff ID $staffId to user: $username (ID: $userId)");
        }
    } else {
        // If no staff selected, unlink any existing staff records
        $unlinkSql = "UPDATE staff SET user_id = NULL WHERE user_id = ?";
        executeQuery($unlinkSql, 'i', [$userId]);
    }
    
    logActivity(getCurrentUser()['id'], 'Update User', 'Users', "Updated user: $username (ID: $userId)");
    jsonResponse(true, 'User updated successfully');
} else {
    jsonResponse(false, 'Failed to update user');
}

