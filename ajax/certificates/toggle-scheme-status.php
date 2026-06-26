<?php
/**
 * Toggle Grading Scheme Active Status
 * 
 * AJAX endpoint to activate or deactivate a grading scheme
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication and authorization
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to perform this action');
}

try {
    $schemeId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;
    
    if (!$schemeId) {
        jsonResponse(false, 'Scheme ID is required');
    }
    
    // Validate is_active value (must be 0 or 1)
    if (!in_array($isActive, [0, 1])) {
        jsonResponse(false, 'Invalid status value');
    }
    
    // Check if scheme exists
    $checkSql = "SELECT id, scheme_name, is_active FROM grading_schemes WHERE id = ?";
    $checkStmt = executeQuery($checkSql, 'i', [$schemeId]);
    $scheme = fetchOne($checkStmt);
    
    if (!$scheme) {
        jsonResponse(false, 'Grading scheme not found');
    }
    
    // Update status
    $sql = "UPDATE grading_schemes SET is_active = ?, updated_at = NOW() WHERE id = ?";
    $stmt = executeQuery($sql, 'ii', [$isActive, $schemeId]);
    
    // Log activity
    $action = $isActive == 1 ? 'Activate' : 'Deactivate';
    logActivity($_SESSION['user_id'], $action . ' Grading Scheme', 'Certificates', 
                "{$action}d grading scheme: {$scheme['scheme_name']}");
    
    $statusText = $isActive == 1 ? 'activated' : 'deactivated';
    jsonResponse(true, "Grading scheme {$statusText} successfully");
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

