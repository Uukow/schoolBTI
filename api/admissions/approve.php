<?php
/**
 * Approve Admission API
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input['admission_id'])) {
        echo json_encode(['success' => false, 'message' => 'Admission ID is required']);
        exit;
    }

    $admissionId = intval($input['admission_id']);
    $remarks = $input['remarks'] ?? null;
    
    // Check if admissions table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'admissions'");
    
    if (!$checkTable || $checkTable->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Admissions table not found']);
        exit;
    }

    // Update admission status to Approved
    $query = "UPDATE admissions 
              SET status = 'Approved', 
                  reviewed_date = CURDATE(), 
                  reviewed_by = 1,
                  remarks = ?
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $remarks, $admissionId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Admission approved successfully',
            'data' => ['id' => $admissionId]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve admission']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    error_log("Error in approve admission API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}














