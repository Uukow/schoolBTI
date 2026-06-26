<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$branchName = sanitize($_POST['branch_name'] ?? '');
$branchCode = sanitize($_POST['branch_code'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$establishedDate = $_POST['established_date'] ?? null;

if (empty($branchName) || empty($branchCode)) {
    jsonResponse(false, 'Branch name and code are required');
}

// Check if code exists
$checkSql = "SELECT id FROM branches WHERE branch_code = ?";
$stmt = executeQuery($checkSql, 's', [$branchCode]);
if (fetchOne($stmt)) {
    jsonResponse(false, 'Branch code already exists');
}

$sql = "INSERT INTO branches (branch_name, branch_code, address, phone, email, established_date, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 1)";

$stmt = executeQuery($sql, 'ssssss', [$branchName, $branchCode, $address, $phone, $email, $establishedDate]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Add Branch', 'Branches', "Created branch: $branchName ($branchCode)");
    jsonResponse(true, 'Branch added successfully!');
} else {
    jsonResponse(false, 'Failed to add branch');
}

