<?php
/**
 * Update Certificate Template
 * 
 * AJAX endpoint to change the template for an existing certificate
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to perform this action');
}

try {
    $certificateId = isset($_POST['certificate_id']) ? intval($_POST['certificate_id']) : 0;
    $templateId = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
    
    if (!$certificateId || !$templateId) {
        jsonResponse(false, 'Certificate ID and Template ID are required');
    }
    
    // Verify certificate exists
    $certSql = "SELECT id FROM certificates WHERE id = ?";
    $certStmt = executeQuery($certSql, 'i', [$certificateId]);
    $cert = fetchOne($certStmt);
    
    if (!$cert) {
        jsonResponse(false, 'Certificate not found');
    }
    
    // Verify template exists
    $templateSql = "SELECT id FROM certificate_templates WHERE id = ? AND is_active = 1";
    $templateStmt = executeQuery($templateSql, 'i', [$templateId]);
    $template = fetchOne($templateStmt);
    
    if (!$template) {
        jsonResponse(false, 'Template not found or inactive');
    }
    
    global $conn;
    
    // Update certificate template
    $updateSql = "UPDATE certificates SET template_id = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('ii', $templateId, $certificateId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update certificate template: ' . $updateStmt->error);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'Update Certificate Template', 'Certificates', 
                "Changed template for certificate ID: $certificateId");
    
    jsonResponse(true, 'Certificate template updated successfully');
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

