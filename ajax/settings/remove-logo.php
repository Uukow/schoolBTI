<?php
/**
 * AJAX: Remove System Logo/Favicon
 * 
 * Remove logo or favicon
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$fileType = $_POST['file_type'] ?? 'logo'; // 'logo' or 'favicon'
$allowedTypes = ['logo', 'favicon'];

if (!in_array($fileType, $allowedTypes)) {
    jsonResponse(false, 'Invalid file type');
}

// Get current settings record
$sql = "SELECT id FROM system_settings LIMIT 1";
$stmt = executeQuery($sql);
$settingsRecord = fetchOne($stmt);

if (!$settingsRecord) {
    jsonResponse(false, 'Settings record not found');
}

// Determine column name
$columnName = $fileType === 'logo' ? 'system_logo' : 'system_favicon';

// Get current file path
$fileSql = "SELECT `{$columnName}` FROM system_settings WHERE id = ?";
$fileStmt = executeQuery($fileSql, 'i', [$settingsRecord['id']]);
$fileData = fetchOne($fileStmt);
$filePath = $fileData[$columnName] ?? null;

// Delete file from filesystem
if ($filePath && file_exists(ABSPATH . $filePath)) {
    @unlink(ABSPATH . $filePath);
}

// Update database
$updateSql = "UPDATE system_settings SET `{$columnName}` = NULL, `updated_at` = NOW(), `updated_by` = ? WHERE id = ?";
$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? null;

$updateStmt = executeQuery($updateSql, 'ii', [$userId, $settingsRecord['id']]);

if ($updateStmt) {
    // Log activity
    logActivity($userId, 'Remove ' . ucfirst($fileType), 'Settings', "Removed {$fileType}");
    
    jsonResponse(true, ucfirst($fileType) . ' removed successfully!');
} else {
    jsonResponse(false, 'Failed to update database');
}

