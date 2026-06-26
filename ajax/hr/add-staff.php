<?php
/**
 * AJAX: Add Staff
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

try {
    $currentUser = null;
    $userId = $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

    if ($userId) {
        $sql = "SELECT u.*, r.role_name, b.branch_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                LEFT JOIN branches b ON u.branch_id = b.id 
                WHERE u.id = ? AND u.is_active = 1";
        $stmt = executeQuery($sql, 'i', [$userId]);
        if ($stmt === false) {
            jsonResponse(false, 'Database error: Failed to retrieve user information');
        }
        $currentUser = fetchOne($stmt);
        
        if (!$currentUser) {
            jsonResponse(false, 'Invalid user ID');
        }
        
        $allowedRoles = ['Super Admin', 'Admin'];
        $userRole = $currentUser['role_name'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            jsonResponse(false, 'Permission denied');
        }
    } else {
        if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
        if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');
        $currentUser = getCurrentUser();
        if (!$currentUser) {
            jsonResponse(false, 'Unable to retrieve user information');
        }
    }
} catch (Exception $e) {
    error_log("Auth error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    jsonResponse(false, 'Authentication error: ' . $e->getMessage());
} catch (Error $e) {
    error_log("Auth fatal error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    jsonResponse(false, 'A system error occurred during authentication. Please contact the administrator.');
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        $data = $_POST;
    }

    $staffId = isset($data['staff_id']) && !empty($data['staff_id']) ? sanitize($data['staff_id']) : '';
    $firstName = trim(sanitize($data['first_name'] ?? ''));
    $lastName = trim(sanitize($data['last_name'] ?? ''));
    $gender = sanitize($data['gender'] ?? 'Male');
    $dateOfBirth = trim(sanitize($data['date_of_birth'] ?? ''));
    $email = trim(sanitize($data['email'] ?? ''));
    $phone = trim(sanitize($data['phone'] ?? ''));
    $address = trim(sanitize($data['address'] ?? ''));
    $city = trim(sanitize($data['city'] ?? ''));
    $state = trim(sanitize($data['state'] ?? ''));
    $postalCode = trim(sanitize($data['postal_code'] ?? ''));
    $designation = trim(sanitize($data['designation'] ?? ''));
    $department = trim(sanitize($data['department'] ?? ''));
    $qualification = trim(sanitize($data['qualification'] ?? ''));
    $experienceYears = isset($data['experience_years']) && $data['experience_years'] !== '' ? (int)$data['experience_years'] : null;
    $joiningDate = trim(sanitize($data['joining_date'] ?? date('Y-m-d')));
    $employmentType = sanitize($data['employment_type'] ?? 'Full Time');
    $bankAccountNo = trim(sanitize($data['bank_account_no'] ?? ''));
    $bankName = trim(sanitize($data['bank_name'] ?? ''));
    $emergencyContact = trim(sanitize($data['emergency_contact'] ?? ''));
    $emergencyPhone = trim(sanitize($data['emergency_phone'] ?? ''));
    
    // Convert empty strings to NULL for optional fields
    $email = $email === '' ? null : $email;
    $address = $address === '' ? null : $address;
    $city = $city === '' ? null : $city;
    $state = $state === '' ? null : $state;
    $postalCode = $postalCode === '' ? null : $postalCode;
    $department = $department === '' ? null : $department;
    $qualification = $qualification === '' ? null : $qualification;
    $bankAccountNo = $bankAccountNo === '' ? null : $bankAccountNo;
    $bankName = $bankName === '' ? null : $bankName;
    $emergencyContact = $emergencyContact === '' ? null : $emergencyContact;
    $emergencyPhone = $emergencyPhone === '' ? null : $emergencyPhone;

    // Validate required fields (staff_id is optional, will be auto-generated if empty)
    if (empty($firstName) || empty($lastName) || empty($phone) || empty($designation)) {
        jsonResponse(false, 'Name, phone, and designation are required');
    }
    
    // Validate date_of_birth (required by database schema)
    if (empty($dateOfBirth)) {
        jsonResponse(false, 'Date of birth is required');
    }
    
    // Validate joining_date (required by database schema)
    if (empty($joiningDate)) {
        $joiningDate = date('Y-m-d');
    }

    // Handle branch_id - use form value if provided, otherwise use current user's branch
    $formBranchId = isset($data['branch_id']) && !empty($data['branch_id']) ? (int)$data['branch_id'] : null;
    $roleName = $currentUser['role_name'] ?? '';
    $isSuperAdmin = ($roleName === 'Super Admin');

    if ($formBranchId) {
        // If Super Admin, allow any branch
        // If regular Admin, only allow their own branch
        $userBranchId = isset($currentUser['branch_id']) ? (int)$currentUser['branch_id'] : null;
        if (!$isSuperAdmin && $formBranchId !== $userBranchId) {
            jsonResponse(false, 'You can only add staff to your own branch');
        }
        $branchId = $formBranchId;
    } else {
        $branchId = isset($currentUser['branch_id']) ? (int)$currentUser['branch_id'] : null;
    }

    if (empty($branchId)) {
        jsonResponse(false, 'Branch is required');
    }

    // Auto-generate staff ID if not provided
    if (empty($staffId)) {
        $lastStaffSql = "SELECT MAX(id) as max_id FROM staff";
        $result = executeQuery($lastStaffSql);
        if ($result === false) {
            jsonResponse(false, 'Database error: Failed to generate staff ID');
        }
        $row = fetchOne($result);
        $nextStaffNum = ($row['max_id'] ?? 0) + 1;
        $staffId = generateUniqueId(STAFF_ID_PREFIX, $nextStaffNum, 6);
        
        // Check if auto-generated ID already exists and find next available
        $checkSql = "SELECT id FROM staff WHERE staff_id = ?";
        $maxAttempts = 10; // Prevent infinite loop
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            $checkStmt = executeQuery($checkSql, 's', [$staffId]);
            if ($checkStmt === false) {
                jsonResponse(false, 'Database error: Failed to check staff ID');
            }
            if (!fetchOne($checkStmt)) {
                break; // ID is unique, exit loop
            }
            $nextStaffNum++;
            $staffId = generateUniqueId(STAFF_ID_PREFIX, $nextStaffNum, 6);
            $attempts++;
        }
        
        if ($attempts >= $maxAttempts) {
            jsonResponse(false, 'Failed to generate unique staff ID. Please try again.');
        }
    } else {
        // Check if manually provided staff ID already exists
        $checkSql = "SELECT id FROM staff WHERE staff_id = ?";
        $checkStmt = executeQuery($checkSql, 's', [$staffId]);
        if ($checkStmt === false) {
            jsonResponse(false, 'Database error: Failed to check staff ID');
        }
        if (fetchOne($checkStmt)) {
            jsonResponse(false, 'Staff ID already exists');
        }
    }

    $sql = "INSERT INTO staff (staff_id, branch_id, first_name, last_name, gender, date_of_birth, email, phone, address, city, state, postal_code, designation, department, qualification, experience_years, joining_date, employment_type, bank_account_no, bank_name, emergency_contact, emergency_phone, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";

    // Build type string: s=string, i=integer
    // Parameters in order:
    // 1. staff_id(s), 2. branch_id(i), 3. first_name(s), 4. last_name(s), 5. gender(s), 6. date_of_birth(s), 
    // 7. email(s), 8. phone(s), 9. address(s), 10. city(s), 11. state(s), 12. postal_code(s), 
    // 13. designation(s), 14. department(s), 15. qualification(s), 16. experience_years(i), 
    // 17. joining_date(s), 18. employment_type(s), 19. bank_account_no(s), 20. bank_name(s), 
    // 21. emergency_contact(s), 22. emergency_phone(s)
    // Type string: s-i-s-s-s-s-s-s-s-s-s-s-s-s-s-i-s-s-s-s-s-s-s-s
    $types = 'sisssssssssssssissssss'; // 22 characters: 2 i's (positions 2 and 16), 20 s's
    
    $params = [
        $staffId, $branchId, $firstName, $lastName, $gender, $dateOfBirth, $email, $phone,
        $address, $city, $state, $postalCode, $designation, $department, $qualification,
        $experienceYears, $joiningDate, $employmentType, $bankAccountNo, $bankName,
        $emergencyContact, $emergencyPhone
    ];
    
    // Verify parameter count matches
    if (strlen($types) !== count($params)) {
        error_log("Parameter mismatch: types=" . strlen($types) . ", params=" . count($params));
        jsonResponse(false, 'Internal error: Parameter count mismatch');
    }
    
    $stmt = executeQuery($sql, $types, $params);

    if ($stmt === false) {
        $error = 'Unknown database error';
        try {
            global $conn;
            if (isset($conn) && is_object($conn) && property_exists($conn, 'error')) {
                $error = $conn->error ?? 'Unknown database error';
            }
        } catch (Exception $e) {
            $error = 'Database error occurred';
        }
        error_log("Staff insert error: " . $error);
        jsonResponse(false, 'Failed to add staff: ' . $error);
    }

    if ($stmt) {
        $userId = $currentUser['id'] ?? 0;
        if ($userId > 0) {
            logActivity($userId, 'Add Staff', 'HR', "Added staff: $staffId");
        }
        jsonResponse(true, 'Staff added successfully!');
    } else {
        jsonResponse(false, 'Failed to add staff');
    }
} catch (Exception $e) {
    error_log("Add staff exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    // Show actual error in development, generic message in production
    $errorMsg = (defined('DEBUG_MODE') && DEBUG_MODE) 
        ? 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
        : 'An error occurred: ' . $e->getMessage();
    jsonResponse(false, $errorMsg);
} catch (Error $e) {
    error_log("Add staff fatal error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    // Show actual error for debugging (you can disable this in production)
    $errorMsg = 'Fatal Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine();
    // Uncomment the line below to show generic message in production
    // $errorMsg = 'A system error occurred. Please contact the administrator.';
    jsonResponse(false, $errorMsg);
}
