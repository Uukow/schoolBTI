<?php
/**
 * AJAX: Activate Academic Session
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$sessionId = (int)($_POST['id'] ?? 0);

if (empty($sessionId)) jsonResponse(false, 'Invalid session ID');

beginTransaction();

try {
    // Deactivate all sessions
    $deactivateSql = "UPDATE academic_sessions SET is_active = 0";
    executeQuery($deactivateSql);
    
    // Activate selected session
    $activateSql = "UPDATE academic_sessions SET is_active = 1 WHERE id = ?";
    executeQuery($activateSql, 'i', [$sessionId]);
    
    logActivity(getCurrentUser()['id'], 'Activate Session', 'Settings', "Activated session ID: $sessionId");
    
    commitTransaction();
    jsonResponse(true, 'Session activated successfully!');
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

