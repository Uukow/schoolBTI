<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$branchId = $_POST['id'] ?? 0;
$branchName = sanitize($_POST['branch_name'] ?? '');
$branchCode = sanitize($_POST['branch_code'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$establishedDate = $_POST['established_date'] ?? null;
$isActive = isset($_POST['is_active']) ? 1 : 0;

if (empty($branchId) || empty($branchName) || empty($branchCode)) {
    jsonResponse(false, 'Branch ID, name and code are required');
}

// Check if branch exists
$checkSql = "SELECT id FROM branches WHERE id = ?";
$stmt = executeQuery($checkSql, 'i', [$branchId]);
if (!fetchOne($stmt)) {
    jsonResponse(false, 'Branch not found');
}

// Check if code exists for another branch
$checkCodeSql = "SELECT id FROM branches WHERE branch_code = ? AND id != ?";
$stmt = executeQuery($checkCodeSql, 'si', [$branchCode, $branchId]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Branch code already exists');
}

$sql = "UPDATE branches SET 
        branch_name = ?, 
        branch_code = ?, 
        address = ?, 
        phone = ?, 
        email = ?, 
        established_date = ?, 
        is_active = ?
        WHERE id = ?";

$stmt = executeQuery($sql, 'ssssssii', [
    $branchName, 
    $branchCode, 
    $address, 
    $phone, 
    $email, 
    $establishedDate, 
    $isActive,
    $branchId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Edit Branch', 'Branches', "Updated branch: $branchName ($branchCode)");
    jsonResponse(true, 'Branch updated successfully!');
} else {
    jsonResponse(false, 'Failed to update branch');
}

