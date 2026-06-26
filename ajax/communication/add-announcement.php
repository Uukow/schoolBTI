<?php
/**
 * AJAX: Add Announcement
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
    
    $title = trim($data['title'] ?? '');
    $content = trim($data['content'] ?? '');
    $targetAudience = $data['target_audience'] ?? 'All';
    $classId = $data['class_id'] ?? null;
    $endDate = $data['end_date'] ?? null;
    $isUrgent = isset($data['is_urgent']) ? 1 : 0;
    
    if (empty($title) || empty($content)) {
        jsonResponse(false, 'Title and content are required');
    }
    
    $branchId = $currentUser['branch_id'] ?? null;
    $createdBy = $currentUser['id'] ?? null;
    
    // Handle class_id in target_ids if provided
    $targetIds = $classId ? json_encode(['class_id' => $classId]) : null;
    
    $sql = "INSERT INTO announcements 
            (title, content, target_audience, target_ids, branch_id, end_date, is_urgent, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $params = [
        $title,
        $content,
        $targetAudience,
        $targetIds,
        $branchId,
        $endDate,
        $isUrgent,
        $createdBy,
    ];
    
    $types = 'sssssiii';
    
    executeQuery($sql, $types, $params);
    
    if ($status == 'Published') {
        logActivity($createdBy, 'Create Announcement', 'Communication', "Created announcement: $title");
    }
    
    jsonResponse(true, 'Announcement added successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to add announcement: ' . $e->getMessage());
}
