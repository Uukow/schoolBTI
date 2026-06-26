<?php
/**
 * AJAX: Add Study Material
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
    $description = trim($data['description'] ?? '');
    $classId = $data['class_id'] ?? null;
    $subjectId = $data['subject_id'] ?? null;
    $filePath = $data['file_url'] ?? $data['file_path'] ?? null;
    $fileType = $data['file_type'] ?? null;
    $fileSize = $data['file_size'] ?? null;
    
    if (empty($title) || empty($description)) {
        jsonResponse(false, 'Title and description are required');
    }
    
    if (!$classId || !$subjectId) {
        jsonResponse(false, 'Class and subject are required');
    }
    
    // Get current active session
    $sessionSql = "SELECT id FROM academic_sessions WHERE is_active = 1 LIMIT 1";
    $sessionStmt = executeQuery($sessionSql);
    $session = fetchOne($sessionStmt);
    
    if (!$session) {
        jsonResponse(false, 'No active academic session found');
    }
    
    $uploadedBy = $currentUser['id'] ?? null;
    
    $sql = "INSERT INTO study_materials 
            (title, description, class_id, subject_id, session_id, file_path, file_type, file_size, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $title,
        $description,
        $classId,
        $subjectId,
        $session['id'],
        $filePath,
        $fileType,
        $fileSize,
        $uploadedBy,
    ];
    
    $types = 'ssiiissii';
    
    executeQuery($sql, $types, $params);
    
    jsonResponse(true, 'Study material added successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to add study material: ' . $e->getMessage());
}

