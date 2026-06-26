<?php
/**
 * Set Grading Scheme as Default
 * 
 * AJAX endpoint to set a grading scheme as the default for all certificates
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
    
    if (!$schemeId) {
        jsonResponse(false, 'Scheme ID is required');
    }
    
    global $conn;
    $conn->begin_transaction();
    
    // Check if scheme exists
    $checkSql = "SELECT id, scheme_name, branch_id, is_active FROM grading_schemes WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $schemeId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $scheme = $result->fetch_assoc();
    
    if (!$scheme) {
        $conn->rollback();
        jsonResponse(false, 'Grading scheme not found');
    }
    
    // Ensure scheme is active before setting as default
    if (!$scheme['is_active']) {
        $conn->rollback();
        jsonResponse(false, 'Cannot set an inactive grading scheme as default. Please activate it first.');
    }
    
    // Unset other defaults (for the same branch or globally)
    $updateSql = "UPDATE grading_schemes SET is_default = 0 WHERE id != ?";
    if ($scheme['branch_id']) {
        // If this scheme is branch-specific, only unset defaults for the same branch or global ones
        $updateSql .= " AND (branch_id = {$scheme['branch_id']} OR branch_id IS NULL)";
    } else {
        // If this scheme is global, unset all other defaults
        $updateSql .= " AND branch_id IS NULL";
    }
    
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('i', $schemeId);
    
    if (!$updateStmt->execute()) {
        $conn->rollback();
        throw new Exception('Failed to update other grading schemes: ' . $updateStmt->error);
    }
    
    // Set this scheme as default
    $setSql = "UPDATE grading_schemes SET is_default = 1, updated_at = NOW() WHERE id = ?";
    $setStmt = $conn->prepare($setSql);
    $setStmt->bind_param('i', $schemeId);
    
    if (!$setStmt->execute()) {
        $conn->rollback();
        throw new Exception('Failed to set grading scheme as default: ' . $setStmt->error);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'Set Default Grading Scheme', 'Certificates', 
                "Set grading scheme as default: {$scheme['scheme_name']}");
    
    $conn->commit();
    jsonResponse(true, 'Grading scheme set as default successfully');
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    jsonResponse(false, $e->getMessage());
}

