<?php
/**
 * API Add Branch Endpoint
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$requiredFields = ['branch_name', 'branch_code', 'address'];
foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        sendApiResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required', null, 400);
    }
}

try {
    $branchName = sanitize($input['branch_name']);
    $branchCode = sanitize($input['branch_code']);
    $address = sanitize($input['address']);
    $city = isset($input['city']) ? sanitize($input['city']) : null;
    $state = isset($input['state']) ? sanitize($input['state']) : null;
    $country = isset($input['country']) ? sanitize($input['country']) : null;
    $phone = isset($input['phone']) ? sanitize($input['phone']) : null;
    $email = isset($input['email']) ? sanitize($input['email']) : null;
    $principalName = isset($input['principal_name']) ? sanitize($input['principal_name']) : null;
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;
    
    // Check if branch code already exists
    $checkSql = "SELECT id FROM branches WHERE branch_code = ?";
    $checkStmt = executeQuery($checkSql, 's', [$branchCode]);
    if (fetchOne($checkStmt)) {
        sendApiResponse(false, 'Branch code already exists', null, 400);
    }
    
    // Insert branch
    $sql = "INSERT INTO branches (branch_name, branch_code, address, city, state, country, phone, email, principal_name, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = executeQuery($sql, 'sssssssssi', [
        $branchName,
        $branchCode,
        $address,
        $city,
        $state,
        $country,
        $phone,
        $email,
        $principalName,
        $isActive
    ]);
    
    if ($stmt) {
        sendApiResponse(true, 'Branch added successfully', ['id' => mysqli_insert_id($GLOBALS['conn'])]);
    } else {
        sendApiResponse(false, 'Failed to add branch', null, 500);
    }
    
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}














