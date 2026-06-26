<?php
/**
 * Update Student Profile
 * 
 * Allows students to update their own profile information
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
    // Support both session and user_id parameter authentication
    $userId = null;
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $userId = intval($_POST['user_id']);
        // Set session for compatibility
        $_SESSION['user_id'] = $userId;
    } elseif (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        $_SESSION['user_id'] = $userId;
    } elseif (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } else {
        jsonResponse(false, 'User not logged in');
        exit;
    }

    // Verify user is a student
    $userCheckSql = "SELECT u.id, r.role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($userCheckSql, 'i', [$userId]);
    $user = fetchOne($stmt);

    if (!$user || $user['role_name'] !== 'Student') {
        jsonResponse(false, 'Unauthorized: Student access only');
        exit;
    }

    // Get student record to verify ownership
    $studentSql = "SELECT id FROM students WHERE user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student) {
        jsonResponse(false, 'Student profile not found');
        exit;
    }

    $studentId = $student['id'];

    // Get allowed fields from POST/JSON
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : null;
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : null;
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : null;
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : null;
    $state = isset($_POST['state']) ? sanitize($_POST['state']) : null;
    $postalCode = isset($_POST['postal_code']) ? sanitize($_POST['postal_code']) : null;

    // Handle JSON input
    if (empty($email) && empty($phone) && empty($address)) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if ($data) {
            $email = isset($data['email']) ? sanitize($data['email']) : null;
            $phone = isset($data['phone']) ? sanitize($data['phone']) : null;
            $address = isset($data['address']) ? sanitize($data['address']) : null;
            $city = isset($data['city']) ? sanitize($data['city']) : null;
            $state = isset($data['state']) ? sanitize($data['state']) : null;
            $postalCode = isset($data['postal_code']) ? sanitize($data['postal_code']) : null;
        }
    }

    // Validate email if provided
    if ($email !== null && !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Invalid email format');
        exit;
    }

    // Check if email already exists for another student
    if ($email !== null && !empty($email)) {
        $emailCheckSql = "SELECT id FROM students WHERE email = ? AND id != ?";
        $stmt = executeQuery($emailCheckSql, 'si', [$email, $studentId]);
        $existing = fetchOne($stmt);
        if ($existing) {
            jsonResponse(false, 'Email already exists');
            exit;
        }
    }

    // Handle photo upload
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $uploadResult = uploadFile($_FILES['photo'], STUDENT_PHOTO_PATH, ['jpg', 'jpeg', 'png']);
        if ($uploadResult['success']) {
            // Get current photo path
            $currentPhotoSql = "SELECT photo FROM students WHERE id = ?";
            $stmt = executeQuery($currentPhotoSql, 'i', [$studentId]);
            $currentStudent = fetchOne($stmt);
            
            // Delete old photo if exists
            if ($currentStudent && $currentStudent['photo'] && 
                file_exists(ABSPATH . $currentStudent['photo'])) {
                deleteFile(ABSPATH . $currentStudent['photo']);
            }
            
            $photoPath = 'uploads/students/photos/' . $uploadResult['filename'];
        } else {
            jsonResponse(false, 'Failed to upload photo: ' . ($uploadResult['message'] ?? 'Unknown error'));
            exit;
        }
    }

    // Build update query dynamically
    $updateFields = [];
    $params = [];
    $types = '';

    if ($email !== null) {
        $updateFields[] = 'email = ?';
        $params[] = $email;
        $types .= 's';
    }

    if ($phone !== null) {
        $updateFields[] = 'phone = ?';
        $params[] = $phone;
        $types .= 's';
    }

    if ($address !== null) {
        $updateFields[] = 'address = ?';
        $params[] = $address;
        $types .= 's';
    }

    if ($city !== null) {
        $updateFields[] = 'city = ?';
        $params[] = $city;
        $types .= 's';
    }

    if ($state !== null) {
        $updateFields[] = 'state = ?';
        $params[] = $state;
        $types .= 's';
    }

    if ($postalCode !== null) {
        $updateFields[] = 'postal_code = ?';
        $params[] = $postalCode;
        $types .= 's';
    }

    if ($photoPath !== null) {
        $updateFields[] = 'photo = ?';
        $params[] = $photoPath;
        $types .= 's';
    }

    if (empty($updateFields)) {
        jsonResponse(false, 'No fields to update');
        exit;
    }

    // Add student ID to params
    $params[] = $studentId;
    $types .= 'i';

    // Update student
    $sql = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = executeQuery($sql, $types, $params);

    if ($stmt) {
        // Log activity
        logActivity(
            $userId,
            'Update Profile',
            'Student Portal',
            "Student updated their profile (ID: $studentId)"
        );

        // Get updated student data
        $updatedSql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name
                       FROM students s
                       LEFT JOIN classes c ON s.current_class_id = c.id
                       LEFT JOIN sections sec ON s.current_section_id = sec.id
                       LEFT JOIN branches b ON s.branch_id = b.id
                       WHERE s.id = ?";
        $stmt = executeQuery($updatedSql, 'i', [$studentId]);
        $updatedStudent = fetchOne($stmt);

        $response = [
            'id' => $updatedStudent['id'],
            'user_id' => $updatedStudent['user_id'],
            'student_id' => $updatedStudent['student_id'],
            'admission_no' => $updatedStudent['admission_no'],
            'first_name' => $updatedStudent['first_name'],
            'last_name' => $updatedStudent['last_name'],
            'middle_name' => $updatedStudent['middle_name'],
            'gender' => $updatedStudent['gender'],
            'date_of_birth' => $updatedStudent['date_of_birth'],
            'email' => $updatedStudent['email'],
            'phone' => $updatedStudent['phone'],
            'address' => $updatedStudent['address'],
            'city' => $updatedStudent['city'],
            'state' => $updatedStudent['state'],
            'postal_code' => $updatedStudent['postal_code'],
            'photo' => !empty($updatedStudent['photo']) && $updatedStudent['photo'] != '0' 
                ? $updatedStudent['photo'] 
                : null,
            'admission_date' => $updatedStudent['admission_date'],
            'current_class_id' => $updatedStudent['current_class_id'],
            'class_name' => $updatedStudent['class_name'],
            'current_section_id' => $updatedStudent['current_section_id'],
            'section_name' => $updatedStudent['section_name'],
            'status' => $updatedStudent['status'],
            'branch_name' => $updatedStudent['branch_name'],
        ];

        jsonResponse(true, 'Profile updated successfully', $response);
        exit;
    } else {
        jsonResponse(false, 'Failed to update profile');
        exit;
    }
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

