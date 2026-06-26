<?php
/**
 * Test endpoint to verify backup AJAX is working
 */

require_once '../../config/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasRole(['Super Admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Simple test response
header('Content-Type: application/json');
echo json_encode([
    'success' => true, 
    'message' => 'Backup endpoint is working! Database: ' . DB_NAME,
    'timestamp' => date('Y-m-d H:i:s')
]);

