<?php
/**
 * AJAX: Send SMS
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
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? null;

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

// Check permissions
if (!hasRole(['Super Admin', 'Admin', 'Teacher'])) {
    jsonResponse(false, 'Permission denied');
}

try {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $recipientType = $data['recipient_type'] ?? 'All';
    $recipientId = $data['recipient_id'] ?? null;
    $classId = $data['class_id'] ?? null;
    $message = trim($data['message'] ?? $data['content'] ?? '');
    
    if (empty($message)) {
        jsonResponse(false, 'Message is required');
    }
    
    $branchId = $currentUser['branch_id'] ?? null;
    $createdBy = $currentUser['id'] ?? null;
    
    // Get recipients based on type
    $recipients = [];
    
    if ($recipientType == 'All') {
        // Get all students, parents, teachers, staff
        $sql = "SELECT phone, CONCAT(first_name, ' ', last_name) as name, 'Student' as type FROM students WHERE phone IS NOT NULL AND phone != ''";
        if ($classId) {
            $sql .= " AND current_class_id = $classId";
        }
        $stmt = executeQuery($sql);
        $students = fetchAll($stmt);
        foreach ($students as $s) {
            $recipients[] = ['phone' => $s['phone'], 'name' => $s['name'], 'type' => 'Student'];
        }
        
        // Get staff
        $sql = "SELECT phone, CONCAT(first_name, ' ', last_name) as name FROM staff WHERE phone IS NOT NULL AND phone != ''";
        $stmt = executeQuery($sql);
        $staff = fetchAll($stmt);
        foreach ($staff as $s) {
            $recipients[] = ['phone' => $s['phone'], 'name' => $s['name'], 'type' => 'Staff'];
        }
    } elseif ($recipientType == 'Students' || $recipientType == 'Parents') {
        $sql = "SELECT phone, CONCAT(first_name, ' ', last_name) as name FROM students WHERE phone IS NOT NULL AND phone != ''";
        if ($classId) {
            $sql .= " AND current_class_id = $classId";
        }
        $stmt = executeQuery($sql);
        $students = fetchAll($stmt);
        foreach ($students as $s) {
            $recipients[] = ['phone' => $s['phone'], 'name' => $s['name'], 'type' => $recipientType];
        }
    } elseif ($recipientType == 'Teachers' || $recipientType == 'Staff') {
        $sql = "SELECT phone, CONCAT(first_name, ' ', last_name) as name FROM staff WHERE phone IS NOT NULL AND phone != ''";
        $stmt = executeQuery($sql);
        $staff = fetchAll($stmt);
        foreach ($staff as $s) {
            $recipients[] = ['phone' => $s['phone'], 'name' => $s['name'], 'type' => $recipientType];
        }
    }
    
    // Insert SMS logs
    $successCount = 0;
    $failCount = 0;
    
    foreach ($recipients as $recipient) {
        $sql = "INSERT INTO sms_logs 
                (recipient_type, recipient_id, recipient_name, recipient_phone, message, status, branch_id, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())";
        
        executeQuery($sql, 'sisssisi', [
            $recipient['type'],
            null,
            $recipient['name'],
            $recipient['phone'],
            $message,
            $branchId,
            $createdBy,
        ]);
        
        // TODO: Integrate with actual SMS gateway API here
        // For now, mark as sent
        $successCount++;
    }
    
    logActivity($createdBy, 'Send SMS', 'Communication', "Sent SMS to $successCount recipients");
    
    jsonResponse(true, "SMS queued for $successCount recipients");
} catch (Exception $e) {
    jsonResponse(false, 'Failed to send SMS: ' . $e->getMessage());
}
