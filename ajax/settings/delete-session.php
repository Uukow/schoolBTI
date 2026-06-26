<?php
/**
 * AJAX: Delete Academic Session
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$sessionId = (int)($_POST['id'] ?? 0);

if (empty($sessionId)) jsonResponse(false, 'Invalid session ID');

// Check if session is active
$checkSql = "SELECT is_active FROM academic_sessions WHERE id = ?";
$stmt = executeQuery($checkSql, 'i', [$sessionId]);
$session = fetchOne($stmt);

if ($session && $session['is_active']) {
    jsonResponse(false, 'Cannot delete active session. Please activate another session first.');
}

$sql = "DELETE FROM academic_sessions WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$sessionId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Session', 'Settings', "Deleted session ID: $sessionId");
    jsonResponse(true, 'Session deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete session');
}

