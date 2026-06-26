<?php
/**
 * AJAX: Delete Backup
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin'])) jsonResponse(false, 'Permission denied');

$backupId = (int)($_POST['id'] ?? 0);

if (empty($backupId)) jsonResponse(false, 'Invalid backup ID');

// Get backup info
$sql = "SELECT * FROM backup_history WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$backupId]);
$backup = fetchOne($stmt);

if (!$backup) {
    jsonResponse(false, 'Backup not found');
}

// Delete file if exists
$backupPath = ABSPATH . 'backups/' . $backup['backup_path'];
if (file_exists($backupPath)) {
    unlink($backupPath);
}

// Delete from database
$deleteSql = "DELETE FROM backup_history WHERE id = ?";
$stmt = executeQuery($deleteSql, 'i', [$backupId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Backup', 'Settings', "Deleted backup: {$backup['backup_name']}");
    jsonResponse(true, 'Backup deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete backup');
}

