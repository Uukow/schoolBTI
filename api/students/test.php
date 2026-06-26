<?php
/**
 * Test endpoint to check if students API works
 */
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Test database connection
    if (!$conn) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    // Test query
    $query = "SELECT COUNT(*) as count FROM students";
    $result = $conn->query($query);
    
    if (!$result) {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        exit;
    }
    
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database test successful',
        'student_count' => $row['count']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}














