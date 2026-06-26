<?php
/**
 * AJAX: Upload System Logo
 * 
 * Handle logo and favicon file uploads
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

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'No file uploaded or upload error occurred');
}

$file = $_FILES['file'];

// Validate file type
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    jsonResponse(false, 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
}

// Validate file size (max 2MB)
$maxSize = 2 * 1024 * 1024; // 2MB
if ($file['size'] > $maxSize) {
    jsonResponse(false, 'File size exceeds 2MB limit');
}

// Create upload directory if it doesn't exist
$uploadDir = ABSPATH . 'uploads/system/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$fileName = $fileType . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
$filePath = $uploadDir . $fileName;
$relativePath = 'uploads/system/' . $fileName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    jsonResponse(false, 'Failed to save file');
}

// Get current settings record
$sql = "SELECT id FROM system_settings LIMIT 1";
$stmt = executeQuery($sql);
$settingsRecord = fetchOne($stmt);

if (!$settingsRecord) {
    // Create initial settings record
    $createSql = "INSERT INTO system_settings (school_name, created_at) VALUES (?, NOW())";
    $createStmt = executeQuery($createSql, 's', ['School']);
    if ($createStmt) {
        $conn = getDBConnection();
        $settingsRecord = ['id' => mysqli_insert_id($conn)];
    } else {
        // Delete uploaded file
        @unlink($filePath);
        jsonResponse(false, 'Failed to initialize settings record');
    }
}

// Determine column name
$columnName = $fileType === 'logo' ? 'system_logo' : 'system_favicon';

// Check if column exists
$checkSql = "SHOW COLUMNS FROM system_settings WHERE Field = ?";
$checkStmt = executeQuery($checkSql, 's', [$columnName]);
$columnExists = fetchOne($checkStmt);

if (!$columnExists) {
    // Column doesn't exist - try to add it
    try {
        $alterSql = "ALTER TABLE system_settings ADD COLUMN `{$columnName}` VARCHAR(255) DEFAULT NULL";
        executeQuery($alterSql);
    } catch (Exception $e) {
        // Column might already exist or error occurred
        // Continue anyway
    }
}

// Get old file path to delete later
$oldFileSql = "SELECT `{$columnName}` FROM system_settings WHERE id = ?";
$oldFileStmt = executeQuery($oldFileSql, 'i', [$settingsRecord['id']]);
$oldFile = fetchOne($oldFileStmt);
$oldFilePath = $oldFile[$columnName] ?? null;

// Update database
$updateSql = "UPDATE system_settings SET `{$columnName}` = ?, `updated_at` = NOW(), `updated_by` = ? WHERE id = ?";
$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? null;

$updateStmt = executeQuery($updateSql, 'sii', [$relativePath, $userId, $settingsRecord['id']]);

if ($updateStmt) {
    // Delete old file if it exists
    if ($oldFilePath && file_exists(ABSPATH . $oldFilePath)) {
        @unlink(ABSPATH . $oldFilePath);
    }
    
    // Log activity
    logActivity($userId, 'Upload ' . ucfirst($fileType), 'Settings', "Uploaded new {$fileType}");
    
    jsonResponse(true, ucfirst($fileType) . ' uploaded successfully!', [
        'file_path' => APP_URL . $relativePath,
        'file_name' => $fileName
    ]);
} else {
    // Delete uploaded file if database update failed
    @unlink($filePath);
    jsonResponse(false, 'Failed to update database');
}

