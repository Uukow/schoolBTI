<?php
/**
 * Get Certificate Template Details
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

try {
    $templateId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$templateId) {
        jsonResponse(false, 'Template ID is required');
    }
    
    // Get template
    $sql = "SELECT ct.*, b.branch_name
            FROM certificate_templates ct
            LEFT JOIN branches b ON ct.branch_id = b.id
            WHERE ct.id = ?";
    
    $stmt = executeQuery($sql, 'i', [$templateId]);
    $template = fetchOne($stmt);
    
    if (!$template) {
        jsonResponse(false, 'Template not found');
    }
    
    jsonResponse(true, 'Template retrieved successfully', $template);
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

