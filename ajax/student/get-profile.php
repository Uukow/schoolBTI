<?php
/**
 * Get Student Profile
 * 
 * Returns student profile information filtered by user_id
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
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        // Set session for compatibility
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

    // Get student record
    $studentSql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name
                   FROM students s
                   LEFT JOIN classes c ON s.current_class_id = c.id
                   LEFT JOIN sections sec ON s.current_section_id = sec.id
                   LEFT JOIN branches b ON s.branch_id = b.id
                   WHERE s.user_id = ?";
    $stmt = executeQuery($studentSql, 'i', [$userId]);
    $student = fetchOne($stmt);

    if (!$student) {
        jsonResponse(false, 'Student profile not found');
        exit;
    }

    // Format response
    $response = [
        'id' => $student['id'],
        'user_id' => $student['user_id'],
        'student_id' => $student['student_id'],
        'admission_no' => $student['admission_no'],
        'first_name' => $student['first_name'],
        'last_name' => $student['last_name'],
        'middle_name' => $student['middle_name'],
        'gender' => $student['gender'],
        'date_of_birth' => $student['date_of_birth'],
        'email' => $student['email'],
        'phone' => $student['phone'],
        'address' => $student['address'],
        'city' => $student['city'],
        'state' => $student['state'],
        'postal_code' => $student['postal_code'],
        'photo' => !empty($student['photo']) && $student['photo'] != '0' ? $student['photo'] : null,
        'admission_date' => $student['admission_date'],
        'current_class_id' => $student['current_class_id'],
        'class_name' => $student['class_name'],
        'current_section_id' => $student['current_section_id'],
        'section_name' => $student['section_name'],
        'status' => $student['status'],
        'branch_name' => $student['branch_name'],
    ];

    jsonResponse(true, 'Student profile retrieved', $response);
    exit;
} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'Error: ' . $e->getMessage());
    exit;
}

