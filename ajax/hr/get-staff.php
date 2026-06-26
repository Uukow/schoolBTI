<?php
/**
 * AJAX: Get Staff
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

$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    $status = $_GET['status'] ?? null;
    $branchId = $_GET['branch_id'] ?? null;
    
    $sql = "SELECT s.*, b.branch_name
            FROM staff s
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND s.branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    } elseif ($branchId) {
        $sql .= " AND s.branch_id = ?";
        $params[] = $branchId;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY s.first_name, s.last_name";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $staff = fetchAll($stmt);
    
    $formatted = [];
    foreach ($staff as $s) {
        $formatted[] = [
            'id' => $s['id'],
            'staff_id' => $s['staff_id'],
            'first_name' => $s['first_name'],
            'last_name' => $s['last_name'],
            'gender' => $s['gender'],
            'date_of_birth' => $s['date_of_birth'],
            'email' => $s['email'],
            'phone' => $s['phone'],
            'address' => $s['address'],
            'city' => $s['city'],
            'state' => $s['state'],
            'postal_code' => $s['postal_code'],
            'photo' => $s['photo'],
            'designation' => $s['designation'],
            'department' => $s['department'],
            'qualification' => $s['qualification'],
            'experience_years' => $s['experience_years'],
            'joining_date' => $s['joining_date'],
            'leaving_date' => $s['leaving_date'],
            'employment_type' => $s['employment_type'],
            'status' => $s['status'],
            'bank_account_no' => $s['bank_account_no'],
            'bank_name' => $s['bank_name'],
            'emergency_contact' => $s['emergency_contact'],
            'emergency_phone' => $s['emergency_phone'],
            'branch_id' => $s['branch_id'],
            'branch_name' => $s['branch_name'],
            'created_at' => $s['created_at'],
        ];
    }
    
    jsonResponse(true, 'Staff loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load staff: ' . $e->getMessage());
}

