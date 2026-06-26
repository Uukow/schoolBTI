<?php
/**
 * Get Grading Scheme Details
 * 
 * Returns grading scheme with all grade scale items
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

try {
    $schemeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$schemeId) {
        jsonResponse(false, 'Scheme ID is required');
    }
    
    // Get scheme
    $sql = "SELECT gs.*, acs.session_name, b.branch_name
            FROM grading_schemes gs
            LEFT JOIN academic_sessions acs ON gs.session_id = acs.id
            LEFT JOIN branches b ON gs.branch_id = b.id
            WHERE gs.id = ?";
    
    $stmt = executeQuery($sql, 'i', [$schemeId]);
    $scheme = fetchOne($stmt);
    
    if (!$scheme) {
        jsonResponse(false, 'Grading scheme not found');
    }
    
    // Get grade items
    $itemsSql = "SELECT * FROM grading_scale_items 
                WHERE grading_scheme_id = ? 
                ORDER BY display_order, min_percentage DESC";
    
    $itemsStmt = executeQuery($itemsSql, 'i', [$schemeId]);
    $items = fetchAll($itemsStmt);
    
    jsonResponse(true, 'Grading scheme retrieved successfully', [
        'scheme' => $scheme,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

