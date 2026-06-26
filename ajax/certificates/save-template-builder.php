<?php
/**
 * Save Certificate Template from Builder
 * 
 * AJAX endpoint to save/update certificate template created in the visual builder
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
    // Validate required fields
    $required = ['template_name', 'certificate_type', 'page_orientation', 'page_size', 'body_html'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            jsonResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    $templateId = !empty($_POST['template_id']) ? intval($_POST['template_id']) : 0;
    $templateName = sanitize($_POST['template_name']);
    $certificateType = sanitize($_POST['certificate_type']);
    $branchId = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null;
    $pageOrientation = sanitize($_POST['page_orientation']);
    $pageSize = sanitize($_POST['page_size']);
    $headerHtml = $_POST['header_html'] ?? '';
    $bodyHtml = $_POST['body_html']; // Don't sanitize HTML content
    $footerHtml = $_POST['footer_html'] ?? '';
    $signature1Label = sanitize($_POST['signature_1_label'] ?? 'Principal');
    $signature2Label = sanitize($_POST['signature_2_label'] ?? 'Registrar');
    $includeQrCode = isset($_POST['include_qr_code']) ? intval($_POST['include_qr_code']) : 0;
    $includeWatermark = isset($_POST['include_watermark']) ? intval($_POST['include_watermark']) : 0;
    $isDefault = isset($_POST['is_default']) ? intval($_POST['is_default']) : 0;
    
    global $conn;
    $conn->begin_transaction();
    
    // If setting as default, unset other defaults of the same type
    if ($isDefault) {
        $updateSql = "UPDATE certificate_templates SET is_default = 0 
                      WHERE certificate_type = ?";
        if ($branchId) {
            $updateSql .= " AND branch_id = $branchId";
        } else {
            $updateSql .= " AND (branch_id IS NULL OR branch_id = $branchId)";
        }
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('s', $certificateType);
        $stmt->execute();
    }
    
    if ($templateId) {
        // Update existing template
        $sql = "UPDATE certificate_templates SET 
                template_name = ?, certificate_type = ?, branch_id = ?, 
                page_orientation = ?, page_size = ?, header_html = ?, body_html = ?, footer_html = ?, 
                signature_1_label = ?, signature_2_label = ?, include_qr_code = ?, include_watermark = ?, 
                is_default = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssissssssssiii', $templateName, $certificateType, $branchId, 
                          $pageOrientation, $pageSize, $headerHtml, $bodyHtml, $footerHtml,
                          $signature1Label, $signature2Label, $includeQrCode, $includeWatermark,
                          $isDefault, $templateId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update certificate template: ' . $stmt->error);
        }
        
        $action = 'Update';
        $message = 'Certificate template updated successfully';
    } else {
        // Insert new template
        $sql = "INSERT INTO certificate_templates (template_name, certificate_type, branch_id, 
                page_orientation, page_size, header_html, body_html, footer_html, 
                signature_1_label, signature_2_label, include_qr_code, include_watermark, 
                is_default, is_active, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $userId = $_SESSION['user_id'];
        $stmt->bind_param('ssissssssssiii', $templateName, $certificateType, $branchId, 
                          $pageOrientation, $pageSize, $headerHtml, $bodyHtml, $footerHtml,
                          $signature1Label, $signature2Label, $includeQrCode, $includeWatermark,
                          $isDefault, $userId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create certificate template: ' . $stmt->error);
        }
        
        $templateId = $conn->insert_id;
        $action = 'Create';
        $message = 'Certificate template created successfully';
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], $action . ' Certificate Template', 'Certificates', 
                "{$action}d template: $templateName");
    
    $conn->commit();
    jsonResponse(true, $message, ['template_id' => $templateId]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    jsonResponse(false, $e->getMessage());
}


