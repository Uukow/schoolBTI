<?php
/**
 * AJAX: Get Study Materials
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
    $classId = $_GET['class_id'] ?? null;
    $subjectId = $_GET['subject_id'] ?? null;
    
    $sql = "SELECT sm.*, 
            c.class_name,
            s.subject_name,
            u.username as uploaded_by_name
            FROM study_materials sm
            LEFT JOIN classes c ON sm.class_id = c.id
            LEFT JOIN subjects s ON sm.subject_id = s.id
            LEFT JOIN users u ON sm.uploaded_by = u.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($classId) {
        $sql .= " AND sm.class_id = ?";
        $params[] = $classId;
        $types .= 'i';
    }
    
    if ($subjectId) {
        $sql .= " AND sm.subject_id = ?";
        $params[] = $subjectId;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY sm.uploaded_at DESC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $materials = fetchAll($stmt);
    
    $formatted = [];
    foreach ($materials as $mat) {
        $formatted[] = [
            'id' => $mat['id'],
            'title' => $mat['title'],
            'description' => $mat['description'] ?? '',
            'file_url' => $mat['file_path'] ?? null,
            'file_type' => $mat['file_type'],
            'file_size' => $mat['file_size'],
            'class_id' => $mat['class_id'],
            'class_name' => $mat['class_name'],
            'subject_id' => $mat['subject_id'],
            'subject_name' => $mat['subject_name'],
            'uploaded_by' => $mat['uploaded_by_name'] ?? '',
            'uploaded_at' => $mat['uploaded_at'],
            'tags' => null,
        ];
    }
    
    jsonResponse(true, 'Study materials loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load study materials: ' . $e->getMessage());
}

