<?php
/**
 * Student Statistics API
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $stats = getStudentStatistics();
    echo json_encode(['success' => true, 'message' => 'Statistics retrieved successfully', 'data' => $stats]);
} catch (Exception $e) {
    error_log("Error in student stats API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getStudentStatistics() {
    global $conn;
    
    // Total students
    $totalQuery = "SELECT COUNT(*) as count FROM students";
    $totalResult = $conn->query($totalQuery);
    $totalStudents = $totalResult->fetch_assoc()['count'];
    
    // Active students
    $activeQuery = "SELECT COUNT(*) as count FROM students WHERE status = 'Active'";
    $activeResult = $conn->query($activeQuery);
    $activeStudents = $activeResult->fetch_assoc()['count'];
    
    // Inactive students
    $inactiveQuery = "SELECT COUNT(*) as count FROM students WHERE status = 'Inactive'";
    $inactiveResult = $conn->query($inactiveQuery);
    $inactiveStudents = $inactiveResult->fetch_assoc()['count'];
    
    // Graduated students
    $graduatedQuery = "SELECT COUNT(*) as count FROM students WHERE status = 'Graduated'";
    $graduatedResult = $conn->query($graduatedQuery);
    $graduatedStudents = $graduatedResult->fetch_assoc()['count'];
    
    // Male students
    $maleQuery = "SELECT COUNT(*) as count FROM students WHERE gender = 'Male'";
    $maleResult = $conn->query($maleQuery);
    $maleStudents = $maleResult->fetch_assoc()['count'];
    
    // Female students
    $femaleQuery = "SELECT COUNT(*) as count FROM students WHERE gender = 'Female'";
    $femaleResult = $conn->query($femaleQuery);
    $femaleStudents = $femaleResult->fetch_assoc()['count'];
    
    // New admissions (this month)
    $newAdmissionsQuery = "SELECT COUNT(*) as count FROM students 
                          WHERE MONTH(admission_date) = MONTH(CURDATE()) 
                          AND YEAR(admission_date) = YEAR(CURDATE())";
    $newAdmissionsResult = $conn->query($newAdmissionsQuery);
    $newAdmissions = $newAdmissionsResult->fetch_assoc()['count'];
    
    return [
        'total_students' => (int)$totalStudents,
        'active_students' => (int)$activeStudents,
        'inactive_students' => (int)$inactiveStudents,
        'graduated_students' => (int)$graduatedStudents,
        'male_students' => (int)$maleStudents,
        'female_students' => (int)$femaleStudents,
        'new_admissions' => (int)$newAdmissions,
    ];
}














