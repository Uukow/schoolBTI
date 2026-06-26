<?php
/**
 * AJAX: Get Timetable
 * 
 * Fetch timetable for a class and section
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Start output buffering to catch any unwanted output
ob_start();

// Suppress error display for clean JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../config/config.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header early
header('Content-Type: application/json; charset=utf-8');

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
$sectionId = $_GET['section_id'] ?? 0;

if (empty($classId) || empty($sectionId)) {
    jsonResponse(false, 'Class ID and Section ID are required');
}

$currentSession = getCurrentSession();
$sessionId = $currentSession['id'] ?? 1;

// Get timetable
$sql = "SELECT t.*, s.subject_name, st.first_name, st.last_name, c.class_name, sec.section_name
        FROM timetable t
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN staff st ON t.teacher_id = st.id
        LEFT JOIN classes c ON t.class_id = c.id
        LEFT JOIN sections sec ON t.section_id = sec.id
        WHERE t.class_id = ? AND t.section_id = ? AND t.session_id = ?
        ORDER BY 
            FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
            t.start_time";

$stmt = executeQuery($sql, 'iii', [$classId, $sectionId, $sessionId]);
$timetable = fetchAll($stmt);

jsonResponse(true, 'Timetable loaded', $timetable);

