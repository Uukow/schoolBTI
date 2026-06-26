<?php
/**
 * Revoke Certificate
 * 
 * Revoke an issued certificate
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
    $certificateId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $reason = sanitize($_POST['reason'] ?? '');
    
    if (!$certificateId) {
        jsonResponse(false, 'Certificate ID is required');
    }
    
    // Get certificate details
    $sql = "SELECT c.*, s.student_id, s.first_name, s.last_name
            FROM certificates c
            INNER JOIN students s ON c.student_id = s.id
            WHERE c.id = ?";
    $stmt = executeQuery($sql, 'i', [$certificateId]);
    $certificate = fetchOne($stmt);
    
    if (!$certificate) {
        jsonResponse(false, 'Certificate not found');
    }
    
    if ($certificate['status'] === 'revoked') {
        jsonResponse(false, 'Certificate is already revoked');
    }
    
    // Update certificate status
    global $conn;
    $updateSql = "UPDATE certificates SET 
                  status = 'revoked',
                  revoked_at = NOW(),
                  revoked_by = ?,
                  revoke_reason = ?,
                  updated_at = NOW()
                  WHERE id = ?";
    
    $updateStmt = $conn->prepare($updateSql);
    $userId = $_SESSION['user_id'];
    $updateStmt->bind_param('isi', $userId, $reason, $certificateId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to revoke certificate: ' . $updateStmt->error);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'Revoke Certificate', 'Certificates',
               "Revoked certificate {$certificate['certificate_number']} for student {$certificate['student_id']}");
    
    jsonResponse(true, 'Certificate revoked successfully');
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}


