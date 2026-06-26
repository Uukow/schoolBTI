<?php
/**
 * AJAX: Add Academic Session
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$sessionName = sanitize($_POST['session_name'] ?? '');
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$isActive = isset($_POST['is_active']) ? 1 : 0;

if (empty($sessionName) || empty($startDate) || empty($endDate)) {
    jsonResponse(false, 'Session name, start date, and end date are required');
}

if (strtotime($endDate) <= strtotime($startDate)) {
    jsonResponse(false, 'End date must be after start date');
}

beginTransaction();

try {
    // If setting as active, deactivate all other sessions
    if ($isActive) {
        $deactivateSql = "UPDATE academic_sessions SET is_active = 0";
        executeQuery($deactivateSql);
    }
    
    // Insert new session
    $sql = "INSERT INTO academic_sessions (session_name, start_date, end_date, is_active)
            VALUES (?, ?, ?, ?)";
    
    executeQuery($sql, 'sssi', [$sessionName, $startDate, $endDate, $isActive]);
    
    logActivity(getCurrentUser()['id'], 'Add Academic Session', 'Settings', "Added session: $sessionName");
    
    commitTransaction();
    jsonResponse(true, 'Academic session added successfully!');
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

