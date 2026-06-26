<?php
/**
 * AJAX: Get Email History
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
    
    $sql = "SELECT e.*, u.username as created_by_name
            FROM communication_logs e
            LEFT JOIN users u ON e.sent_by = u.id
            WHERE e.communication_type = 'Email'";
    
    $params = [];
    $types = '';
    
    if ($status) {
        $sql .= " AND e.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Note: communication_logs doesn't have branch_id
    // Filter by sent_by if needed
    
    $sql .= " ORDER BY e.sent_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $emails = fetchAll($stmt);
    
    $formatted = [];
    foreach ($emails as $email) {
        $formatted[] = [
            'id' => $email['id'],
            'recipient_type' => 'All', // communication_logs doesn't have recipient_type
            'recipient_id' => null,
            'recipient_name' => null,
            'recipient_email' => $email['recipient'] ?? '',
            'subject' => $email['subject'] ?? '',
            'body' => $email['message'] ?? '',
            'attachment_url' => null,
            'status' => $email['status'] ?? 'Pending',
            'sent_at' => $email['created_at'] ?? null,
            'error_message' => null,
            'created_by' => $email['created_by_name'] ?? '',
            'created_at' => $email['created_at'],
        ];
    }
    
    jsonResponse(true, 'Email history loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load email history: ' . $e->getMessage());
}

