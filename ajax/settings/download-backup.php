<?php
/**
 * AJAX: Download Backup File
 * 
 * Secure download handler for backup files
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    die('Unauthorized');
}

if (!hasRole(['Super Admin'])) {
    http_response_code(403);
    die('Permission denied');
}

// Get backup ID from request
$backupId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$backupId) {
    http_response_code(400);
    die('Invalid backup ID');
}

// Get backup details from database
$sql = "SELECT * FROM backup_history WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$backupId]);
$backup = fetchOne($stmt);

if (!$backup) {
    http_response_code(404);
    die('Backup not found');
}

// Check if backup was successful
if ($backup['status'] != 'Success') {
    http_response_code(400);
    die('Backup was not successful');
}

// Build file path
$filePath = ABSPATH . 'backups/' . $backup['backup_path'];

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    die('Backup file not found on server');
}

// Check if it's a file (not a directory)
if (!is_file($filePath)) {
    http_response_code(400);
    die('Invalid backup file');
}

// Get file info
$fileName = $backup['backup_path'];
$fileSize = filesize($filePath);
$mimeType = 'application/sql';

// Log download activity
if (function_exists('logActivity')) {
    logActivity(getCurrentUser()['id'], 'Download Backup', 'Settings', "Downloaded backup: {$backup['backup_name']}");
}

// Set headers for file download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Expires: 0');

// Disable output buffering for large files
if (ob_get_level()) {
    ob_end_clean();
}

// Read and output file
readfile($filePath);
exit;

