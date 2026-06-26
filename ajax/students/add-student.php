<?php
/**
 * AJAX: Add New Student
 * 
 * Add a new student via API (for Flutter/mobile apps)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? json_decode(file_get_contents('php://input'), true)['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication - get user by ID
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
    
    // Check if user has permission
    $allowedRoles = ['Super Admin', 'Admin', 'Receptionist'];
    if (!in_array($currentUser['role_name'], $allowedRoles)) {
        jsonResponse(false, 'Permission denied');
    }
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    
    $currentUser = getCurrentUser();
    
    // Check if user has permission
    requireRole(['Super Admin', 'Admin', 'Receptionist']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data');
}

// Extract and sanitize data
$branchId = $currentUser['branch_id'] ?? ($data['branch_id'] ?? null);
$firstName = sanitize($data['first_name'] ?? '');
$lastName = sanitize($data['last_name'] ?? '');
$middleName = sanitize($data['middle_name'] ?? '');
$gender = $data['gender'] ?? '';
$dateOfBirth = $data['date_of_birth'] ?? $data['dob'] ?? '';
$email = sanitize($data['email'] ?? '');
$phone = sanitize($data['phone'] ?? '');
$address = sanitize($data['address'] ?? '');
$city = sanitize($data['city'] ?? '');
$state = sanitize($data['state'] ?? '');
$postalCode = sanitize($data['postal_code'] ?? '');
$classId = $data['class_id'] ?? null;
$sectionId = $data['section_id'] ?? null;
$admissionDate = $data['admission_date'] ?? date('Y-m-d');

// Validation
$errors = [];
if (empty($firstName)) $errors[] = 'First name is required';
if (empty($lastName)) $errors[] = 'Last name is required';
if (empty($gender)) $errors[] = 'Gender is required';
if (empty($dateOfBirth)) $errors[] = 'Date of birth is required';
if (empty($branchId)) $errors[] = 'Branch is required';

if (!empty($errors)) {
    jsonResponse(false, implode(', ', $errors));
}

// Begin transaction
beginTransaction();

try {
    // Generate student ID
    $sql = "SELECT MAX(id) as max_id FROM students";
    $result = executeQuery($sql);
    $row = fetchOne($result);
    $nextId = ($row['max_id'] ?? 0) + 1;
    $studentId = generateUniqueId(STUDENT_ID_PREFIX, $nextId, 6);
    
    // Generate admission number
    $admissionNo = 'ADM' . date('Y') . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    
    // Insert student
    $sql = "INSERT INTO students (
        student_id, admission_no, branch_id, first_name, last_name, middle_name,
        gender, date_of_birth, email, phone, address, city, state, postal_code,
        admission_date, current_class_id, current_section_id, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
    
    // Parameter types: s=string, i=integer
    // 17 parameters: student_id(s), admission_no(s), branch_id(i), first_name(s), last_name(s), 
    // middle_name(s), gender(s), date_of_birth(s), email(s), phone(s), address(s), city(s), 
    // state(s), postal_code(s), admission_date(s), current_class_id(i), current_section_id(i)
    $stmt = executeQuery($sql, 'ssissssssssssssii', [
        $studentId, $admissionNo, $branchId, $firstName, $lastName, $middleName,
        $gender, $dateOfBirth, $email, $phone, $address, $city, $state, $postalCode,
        $admissionDate, $classId, $sectionId
    ]);
    
    $newStudentId = getLastInsertId();
    
    // Log activity
    logActivity(
        $currentUser['id'],
        'Add Student',
        'Students',
        "Added student: $firstName $lastName (ID: $studentId)"
    );
    
    commitTransaction();
    
    // Get the created student data
    $studentSql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name
                   FROM students s
                   LEFT JOIN classes c ON s.current_class_id = c.id
                   LEFT JOIN sections sec ON s.current_section_id = sec.id
                   LEFT JOIN branches b ON s.branch_id = b.id
                   WHERE s.id = ?";
    $studentStmt = executeQuery($studentSql, 'i', [$newStudentId]);
    $studentData = fetchOne($studentStmt);
    
    jsonResponse(true, 'Student added successfully!', $studentData);
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Failed to add student: ' . $e->getMessage());
}

