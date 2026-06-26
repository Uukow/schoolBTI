<?php
/**
 * AJAX: Get Announcements
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
    $targetAudience = $_GET['target_audience'] ?? null;
    
    $sql = "SELECT a.*, 
            u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Note: announcements table doesn't have status field
    // Filter by is_urgent or date range if needed
    
    if ($targetAudience) {
        $sql .= " AND (a.target_audience = ? OR a.target_audience = 'All')";
        $params[] = $targetAudience;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND (a.branch_id IS NULL OR a.branch_id = ?)";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY a.created_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $announcements = fetchAll($stmt);
    
    $formatted = [];
    foreach ($announcements as $ann) {
        // Parse target_ids to get class_id if present
        $classId = null;
        $className = null;
        if (!empty($ann['target_ids'])) {
            $targetIds = json_decode($ann['target_ids'], true);
            if (isset($targetIds['class_id'])) {
                $classId = $targetIds['class_id'];
                // Get class name
                $classSql = "SELECT class_name FROM classes WHERE id = ? LIMIT 1";
                $classStmt = executeQuery($classSql, 'i', [$classId]);
                $class = fetchOne($classStmt);
                if ($class) {
                    $className = $class['class_name'];
                }
            }
        }
        
        $formatted[] = [
            'id' => $ann['id'],
            'title' => $ann['title'],
            'content' => $ann['content'] ?? '',
            'target_audience' => $ann['target_audience'] ?? 'All',
            'class_id' => $classId,
            'class_name' => $className,
            'attachment_url' => null, // announcements table doesn't have attachment_url
            'status' => 'Published', // All announcements are published
            'created_by' => $ann['created_by_name'] ?? '',
            'created_at' => $ann['created_at'],
            'published_at' => $ann['created_at'], // Use created_at as published_at
        ];
    }
    
    jsonResponse(true, 'Announcements loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load announcements: ' . $e->getMessage());
}

