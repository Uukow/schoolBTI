<?php
/**
 * AJAX: Get Backup List
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

// Check if user has required role (only Super Admin for backups)
if (!$currentUser || ($currentUser['role_name'] ?? '') !== 'Super Admin') {
    error_log("Backup access denied for user: " . ($currentUser['username'] ?? 'unknown') . " with role: " . ($currentUser['role_name'] ?? 'unknown'));
    jsonResponse(false, 'Permission denied. Only Super Admin can access backup and restore.');
}

try {
    $backupDir = ABSPATH . 'backups/';
    $backups = [];
    
    if (is_dir($backupDir)) {
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                $filePath = $backupDir . $file;
                $backups[] = [
                    'file_name' => $file,
                    'file_path' => $filePath,
                    'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                    'file_size' => filesize($filePath),
                ];
            }
        }
    }
    
    // Sort by creation date descending
    usort($backups, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    jsonResponse(true, 'Backups loaded', $backups);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load backups: ' . $e->getMessage());
}

