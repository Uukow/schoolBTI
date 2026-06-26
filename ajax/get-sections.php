<?php
/**
 * AJAX: Get Sections by Class
 * 
 * Fetch sections for a specific class
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../config/config.php';

// Support both session-based (web) and user_id parameter (Flutter/mobile) authentication
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    // Flutter/mobile app authentication - verify user exists and is active
    $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $user = fetchOne($stmt);
    
    if (!$user) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    // Web session-based authentication
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
}

$classId = $_GET['class_id'] ?? 0;

if (empty($classId)) {
    jsonResponse(false, 'Invalid class ID');
}

// Get sections for the class
$sql = "SELECT * FROM sections WHERE class_id = ? AND is_active = 1 ORDER BY section_name";
$stmt = executeQuery($sql, 'i', [$classId]);
$sections = fetchAll($stmt);

jsonResponse(true, 'Sections loaded', $sections);
