<?php
/**
 * AJAX: Get SMS History
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT s.*, u.username as created_by_name
            FROM communication_logs s
            LEFT JOIN users u ON s.sent_by = u.id
            WHERE s.communication_type = 'SMS'";
    
    $params = [];
    $types = '';
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND (s.branch_id IS NULL OR s.branch_id = ?)";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $smsList = fetchAll($stmt);
    
    $formatted = [];
    foreach ($smsList as $sms) {
        $formatted[] = [
            'id' => $sms['id'],
            'recipient_type' => $sms['recipient_type'] ?? 'All',
            'recipient_id' => $sms['recipient_id'],
            'recipient_name' => $sms['recipient_name'],
            'recipient_phone' => $sms['recipient_phone'],
            'message' => $sms['message'] ?? $sms['content'] ?? '',
            'status' => $sms['status'] ?? 'Pending',
            'sent_at' => $sms['sent_at'] ?? null,
            'error_message' => $sms['error_message'] ?? null,
            'created_by' => $sms['created_by_name'] ?? '',
            'created_at' => $sms['created_at'],
        ];
    }
    
    jsonResponse(true, 'SMS history loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load SMS history: ' . $e->getMessage());
}

