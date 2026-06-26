<?php
/**
 * Admissions Statistics API
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
    // Check if admissions table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'admissions'");
    
    if ($checkTable && $checkTable->num_rows > 0) {
        // Table exists, get real stats
        $stats = getAdmissionStatistics();
    } else {
        // Table doesn't exist, return mock data
        $stats = [
            'total_applications' => 0,
            'pending_review' => 0,
            'approved' => 0,
            'rejected' => 0,
            'enrolled' => 0,
            'this_month' => 0,
            'this_week' => 0,
        ];
    }
    
    echo json_encode(['success' => true, 'message' => 'Statistics retrieved successfully', 'data' => $stats]);
} catch (Exception $e) {
    error_log("Error in admissions stats API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getAdmissionStatistics() {
    global $conn;
    
    // Total applications
    $totalQuery = "SELECT COUNT(*) as count FROM admissions";
    $totalResult = $conn->query($totalQuery);
    $totalApplications = $totalResult->fetch_assoc()['count'];
    
    // Pending review
    $pendingQuery = "SELECT COUNT(*) as count FROM admissions WHERE status = 'Pending'";
    $pendingResult = $conn->query($pendingQuery);
    $pendingReview = $pendingResult->fetch_assoc()['count'];
    
    // Approved
    $approvedQuery = "SELECT COUNT(*) as count FROM admissions WHERE status = 'Approved'";
    $approvedResult = $conn->query($approvedQuery);
    $approved = $approvedResult->fetch_assoc()['count'];
    
    // Rejected
    $rejectedQuery = "SELECT COUNT(*) as count FROM admissions WHERE status = 'Rejected'";
    $rejectedResult = $conn->query($rejectedQuery);
    $rejected = $rejectedResult->fetch_assoc()['count'];
    
    // Enrolled
    $enrolledQuery = "SELECT COUNT(*) as count FROM admissions WHERE status = 'Enrolled'";
    $enrolledResult = $conn->query($enrolledQuery);
    $enrolled = $enrolledResult->fetch_assoc()['count'];
    
    // This month
    $thisMonthQuery = "SELECT COUNT(*) as count FROM admissions 
                       WHERE MONTH(application_date) = MONTH(CURDATE()) 
                       AND YEAR(application_date) = YEAR(CURDATE())";
    $thisMonthResult = $conn->query($thisMonthQuery);
    $thisMonth = $thisMonthResult->fetch_assoc()['count'];
    
    // This week
    $thisWeekQuery = "SELECT COUNT(*) as count FROM admissions 
                      WHERE YEARWEEK(application_date, 1) = YEARWEEK(CURDATE(), 1)";
    $thisWeekResult = $conn->query($thisWeekQuery);
    $thisWeek = $thisWeekResult->fetch_assoc()['count'];
    
    return [
        'total_applications' => (int)$totalApplications,
        'pending_review' => (int)$pendingReview,
        'approved' => (int)$approved,
        'rejected' => (int)$rejected,
        'enrolled' => (int)$enrolled,
        'this_month' => (int)$thisMonth,
        'this_week' => (int)$thisWeek,
    ];
}














