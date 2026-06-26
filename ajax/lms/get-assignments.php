<?php
/**
 * AJAX: Get Assignments
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
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT a.*, 
            c.class_name,
            s.subject_name,
            (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.id) as submission_count
            FROM assignments a
            LEFT JOIN classes c ON a.class_id = c.id
            LEFT JOIN subjects s ON a.subject_id = s.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($classId) {
        $sql .= " AND a.class_id = ?";
        $params[] = $classId;
        $types .= 'i';
    }
    
    if ($subjectId) {
        $sql .= " AND a.subject_id = ?";
        $params[] = $subjectId;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
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
    $assignments = fetchAll($stmt);
    
    $formatted = [];
    foreach ($assignments as $ass) {
        $formatted[] = [
            'id' => $ass['id'],
            'title' => $ass['title'],
            'description' => $ass['description'] ?? '',
            'class_id' => $ass['class_id'],
            'class_name' => $ass['class_name'],
            'subject_id' => $ass['subject_id'],
            'subject_name' => $ass['subject_name'],
            'due_date' => $ass['due_date'],
            'max_marks' => $ass['max_marks'],
            'status' => $ass['status'] ?? 'Active',
            'created_by' => $ass['created_by'] ?? '',
            'created_at' => $ass['created_at'],
            'submission_count' => $ass['submission_count'] ?? 0,
        ];
    }
    
    jsonResponse(true, 'Assignments loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load assignments: ' . $e->getMessage());
}

