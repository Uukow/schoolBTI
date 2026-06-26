<?php
/**
 * AJAX: Create Database Backup
 * 
 * @author School ERP Development Team
 */

// Increase execution time and memory limits for backup operations
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

// Start output buffering to prevent any output before JSON
ob_start();

// Set error handler to prevent errors from breaking JSON response
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error in create-backup.php: [$errno] $errstr in $errfile on line $errline");
    return true; // Suppress error output
}, E_ALL);

// Register shutdown function to ensure response is always sent on fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        // Check if headers were already sent
        if (!headers_sent()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        }
    }
});

require_once '../../config/config.php';

// Clear any output that might have been generated
ob_clean();

if (!isLoggedIn()) {
    ob_clean();
    jsonResponse(false, 'Unauthorized');
}
if (!hasRole(['Super Admin'])) {
    ob_clean();
    jsonResponse(false, 'Permission denied');
}

// Create backups directory if it doesn't exist
$backupDir = ABSPATH . 'backups/';
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0755, true)) {
        jsonResponse(false, 'Failed to create backup directory. Please check directory permissions.');
        exit;
    }
}

// Check if directory is writable
if (!is_writable($backupDir)) {
    ob_clean();
    jsonResponse(false, 'Backup directory is not writable. Please check directory permissions.');
}

// Generate backup filename
$backupName = 'backup_' . date('Y-m-d_His') . '.sql';
$backupPath = $backupDir . $backupName;

// Log that we're starting the backup
error_log("Starting backup creation: $backupName");

try {
    // Get database credentials
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $dbname = DB_NAME;
    
    $backupCreated = false;
    $errorMessage = '';
    
    // Use manual backup directly (more reliable on Windows/XAMPP)
    // Skip mysqldump as it often has issues on Windows
    error_log("Creating manual backup...");
    $result = createManualBackupToFile($backupPath);
    if ($result['success']) {
        $backupCreated = true;
        error_log("Manual backup created successfully");
    } else {
        $errorMessage = $result['error'] ?? 'Failed to create backup. Please check database connection and permissions.';
        error_log("Manual backup failed: " . $errorMessage);
    }
    
    if (!$backupCreated || !file_exists($backupPath)) {
        // Log failed backup
        $sql = "INSERT INTO backup_history (backup_name, backup_path, backup_type, status, created_by)
                VALUES (?, ?, 'Full', 'Failed', ?)";
        $stmt = executeQuery($sql, 'ssi', [$backupName, '', getCurrentUser()['id']]);
        
        $errorMsg = !empty($errorMessage) ? $errorMessage : 'Failed to create backup file.';
        ob_clean();
        jsonResponse(false, $errorMsg);
    }
    
    // Get backup size
    $backupSize = filesize($backupPath);
    if ($backupSize === false) {
        $backupSize = 0;
    }
    
    // Log backup
    $sql = "INSERT INTO backup_history (backup_name, backup_path, backup_size, backup_type, status, created_by)
            VALUES (?, ?, ?, 'Full', 'Success', ?)";
    
    $stmt = executeQuery($sql, 'ssii', [
        $backupName, 
        $backupName, 
        $backupSize, 
        getCurrentUser()['id']
    ]);
    
    if ($stmt === false) {
        // If logging fails, still report success but log the error
        error_log("Failed to log backup to database: " . (getDB() ? getDB()->error : 'No DB connection'));
    }
    
    if (function_exists('logActivity')) {
        logActivity(getCurrentUser()['id'], 'Create Backup', 'Settings', "Created backup: $backupName");
    }
    
    // Clear output buffer and send success response
    error_log("Backup completed successfully. Size: " . formatFileSize($backupSize));
    ob_clean();
    jsonResponse(true, 'Backup created successfully! File size: ' . formatFileSize($backupSize));
    
} catch (Exception $e) {
    // Log failed backup
    try {
        $sql = "INSERT INTO backup_history (backup_name, backup_path, backup_type, status, created_by)
                VALUES (?, ?, 'Full', 'Failed', ?)";
        executeQuery($sql, 'ssi', [$backupName, '', getCurrentUser()['id']]);
    } catch (Exception $dbError) {
        error_log("Failed to log backup error: " . $dbError->getMessage());
    }
    
    // Clear output buffer and send error response
    ob_clean();
    jsonResponse(false, 'Error creating backup: ' . $e->getMessage());
} catch (Error $e) {
    // Handle fatal errors
    ob_clean();
    jsonResponse(false, 'Fatal error creating backup: ' . $e->getMessage());
}

/**
 * Create manual backup using PHP - write directly to file to save memory
 */
function createManualBackupToFile($filePath) {
    try {
        $db = getDB();
        if (!$db) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }
        
        // Open file for writing
        $file = fopen($filePath, 'w');
        if (!$file) {
            return ['success' => false, 'error' => 'Failed to open backup file for writing'];
        }
        
        // Write header
        fwrite($file, "-- Database Backup\n");
        fwrite($file, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($file, "-- Database: " . DB_NAME . "\n\n");
        fwrite($file, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($file, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($file, "SET AUTOCOMMIT=0;\n");
        fwrite($file, "START TRANSACTION;\n\n");
        
        // Get table list
        $tables = [];
        $result = $db->query("SHOW TABLES");
        
        if (!$result) {
            fclose($file);
            @unlink($filePath);
            return ['success' => false, 'error' => 'Failed to get table list: ' . $db->error];
        }
        
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        if (empty($tables)) {
            fclose($file);
            @unlink($filePath);
            return ['success' => false, 'error' => 'No tables found in database'];
        }
        
        // Process each table
        foreach ($tables as $table) {
            // Check if we're running out of time
            if (connection_status() !== CONNECTION_NORMAL) {
                fclose($file);
                @unlink($filePath);
                return ['success' => false, 'error' => 'Script execution timeout'];
            }
            
            fwrite($file, "-- Table: $table\n");
            fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
            
            $createTable = $db->query("SHOW CREATE TABLE `$table`");
            if (!$createTable) {
                error_log("Failed to get CREATE TABLE for $table: " . $db->error);
                continue;
            }
            
            $row = $createTable->fetch_row();
            if (!$row || !isset($row[1])) {
                error_log("Invalid CREATE TABLE result for $table");
                continue;
            }
            
            fwrite($file, $row[1] . ";\n\n");
            
            fwrite($file, "LOCK TABLES `$table` WRITE;\n");
            fwrite($file, "/*!40000 ALTER TABLE `$table` DISABLE KEYS */;\n");
            
            // Process data in chunks to avoid memory issues
            $data = $db->query("SELECT * FROM `$table`");
            if ($data) {
                $rowCount = 0;
                $startTime = time();
                while ($row = $data->fetch_assoc()) {
                    // Check for timeout (don't exceed 4.5 minutes to leave time for response)
                    if (time() - $startTime > 270) {
                        error_log("Backup timeout for table: $table");
                        break;
                    }
                    
                    $insert = "INSERT INTO `$table` VALUES (";
                    $values = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            // Properly escape the value
                            $escaped = $db->real_escape_string($value);
                            $values[] = "'" . $escaped . "'";
                        }
                    }
                    $insert .= implode(',', $values) . ");\n";
                    fwrite($file, $insert);
                    $rowCount++;
                    
                    // Flush every 100 rows to keep memory usage low
                    if ($rowCount % 100 == 0) {
                        fflush($file);
                    }
                    
                    // Check connection status periodically
                    if ($rowCount % 1000 == 0 && connection_status() !== CONNECTION_NORMAL) {
                        error_log("Connection lost during backup at table: $table, row: $rowCount");
                        break;
                    }
                }
                fwrite($file, "-- Inserted $rowCount rows\n");
            }
            
            fwrite($file, "/*!40000 ALTER TABLE `$table` ENABLE KEYS */;\n");
            fwrite($file, "UNLOCK TABLES;\n\n");
        }
        
        fwrite($file, "COMMIT;\n");
        fwrite($file, "SET FOREIGN_KEY_CHECKS=1;\n");
        
        fclose($file);
        
        // Verify file was created and has content
        if (file_exists($filePath) && filesize($filePath) > 0) {
            return ['success' => true];
        } else {
            @unlink($filePath);
            return ['success' => false, 'error' => 'Backup file is empty'];
        }
        
    } catch (Exception $e) {
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }
        @unlink($filePath);
        error_log("Error in createManualBackupToFile: " . $e->getMessage());
        return ['success' => false, 'error' => 'Error creating backup: ' . $e->getMessage()];
    }
}


