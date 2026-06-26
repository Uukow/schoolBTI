<?php
/**
 * AJAX: Send Email
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
    $subject = trim($data['subject'] ?? '');
    $body = trim($data['body'] ?? $data['content'] ?? '');
    $attachmentUrl = $data['attachment_url'] ?? null;
    
    if (empty($subject) || empty($body)) {
        jsonResponse(false, 'Subject and body are required');
    }
    
    $branchId = $currentUser['branch_id'] ?? null;
    $createdBy = $currentUser['id'] ?? null;
    
    // Get recipients based on type
    $recipients = [];
    
    if ($recipientType == 'All') {
        // Get all students, parents, teachers, staff
        $sql = "SELECT email, CONCAT(first_name, ' ', last_name) as name, 'Student' as type FROM students WHERE email IS NOT NULL AND email != ''";
        if ($classId) {
            $sql .= " AND current_class_id = $classId";
        }
        $stmt = executeQuery($sql);
        $students = fetchAll($stmt);
        foreach ($students as $s) {
            $recipients[] = ['email' => $s['email'], 'name' => $s['name'], 'type' => 'Student'];
        }
        
        // Get staff
        $sql = "SELECT email, CONCAT(first_name, ' ', last_name) as name FROM staff WHERE email IS NOT NULL AND email != ''";
        $stmt = executeQuery($sql);
        $staff = fetchAll($stmt);
        foreach ($staff as $s) {
            $recipients[] = ['email' => $s['email'], 'name' => $s['name'], 'type' => 'Staff'];
        }
    } elseif ($recipientType == 'Students' || $recipientType == 'Parents') {
        $sql = "SELECT email, CONCAT(first_name, ' ', last_name) as name FROM students WHERE email IS NOT NULL AND email != ''";
        if ($classId) {
            $sql .= " AND current_class_id = $classId";
        }
        $stmt = executeQuery($sql);
        $students = fetchAll($stmt);
        foreach ($students as $s) {
            $recipients[] = ['email' => $s['email'], 'name' => $s['name'], 'type' => $recipientType];
        }
    } elseif ($recipientType == 'Teachers' || $recipientType == 'Staff') {
        $sql = "SELECT email, CONCAT(first_name, ' ', last_name) as name FROM staff WHERE email IS NOT NULL AND email != ''";
        $stmt = executeQuery($sql);
        $staff = fetchAll($stmt);
        foreach ($staff as $s) {
            $recipients[] = ['email' => $s['email'], 'name' => $s['name'], 'type' => $recipientType];
        }
    }
    
    // Insert email logs
    $successCount = 0;
    $failCount = 0;
    
    foreach ($recipients as $recipient) {
        $sql = "INSERT INTO email_logs 
                (recipient_type, recipient_id, recipient_name, recipient_email, subject, body, attachment_url, status, branch_id, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())";
        
        executeQuery($sql, 'sisssssisi', [
            $recipient['type'],
            null,
            $recipient['name'],
            $recipient['email'],
            $subject,
            $body,
            $attachmentUrl,
            $branchId,
            $createdBy,
        ]);
        
        // TODO: Integrate with actual email service (PHPMailer, etc.) here
        // For now, mark as sent
        $successCount++;
    }
    
    logActivity($createdBy, 'Send Email', 'Communication', "Sent email to $successCount recipients");
    
    jsonResponse(true, "Email queued for $successCount recipients");
} catch (Exception $e) {
    jsonResponse(false, 'Failed to send email: ' . $e->getMessage());
}
