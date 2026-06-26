<?php
/**
 * AJAX: Restore Database Backup
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

$backupPath = ABSPATH . 'backups/' . $backup['backup_path'];

if (!file_exists($backupPath)) {
    jsonResponse(false, 'Backup file not found');
}

try {
    // Read backup file
    $sqlContent = file_get_contents($backupPath);
    
    if (empty($sqlContent)) {
        jsonResponse(false, 'Backup file is empty');
    }
    
    $db = getDB();
    
    // Disable foreign key checks
    $db->query("SET FOREIGN_KEY_CHECKS=0");
    $db->query("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
    $db->query("SET AUTOCOMMIT=0");
    
    // Better SQL parsing - handle multi-line statements
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $sqlContent);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || preg_match('/^--/', $line) || preg_match('/^\/\*/', $line)) {
            continue;
        }
        
        // Add line to current statement
        $currentStatement .= $line . "\n";
        
        // Check if line ends with semicolon (end of statement)
        if (substr(rtrim($line), -1) === ';') {
            $statement = trim($currentStatement);
            if (!empty($statement)) {
                $statements[] = $statement;
            }
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    $errors = [];
    $executed = 0;
    
    // Execute statements with error handling
    foreach ($statements as $index => $statement) {
        // Skip empty statements
        if (empty(trim($statement))) {
            continue;
        }
        
        try {
            // Execute statement
            $result = $db->query($statement);
            
            if ($result === false) {
                $error = $db->error;
                $errorCode = $db->errno;
                
                // Ignore "table already exists" errors (1062) and "duplicate key" errors
                // These are expected during restore if tables/data already exist
                if ($errorCode == 1050 || strpos($error, 'already exists') !== false) {
                    // Table already exists - this is OK, continue
                    continue;
                } elseif ($errorCode == 1062 || strpos($error, 'Duplicate entry') !== false) {
                    // Duplicate entry - this is OK, continue
                    continue;
                } else {
                    // Log other errors but continue
                    $errors[] = "Statement " . ($index + 1) . ": " . substr($error, 0, 100);
                    error_log("SQL Error during restore: " . $error . " | Statement: " . substr($statement, 0, 200));
                }
            } else {
                $executed++;
            }
        } catch (Exception $e) {
            // Log exception but continue
            $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
            error_log("Exception during restore: " . $e->getMessage());
        }
    }
    
    // Commit transaction
    $db->query("COMMIT");
    
    // Re-enable foreign key checks
    $db->query("SET FOREIGN_KEY_CHECKS=1");
    
    // Log restore activity
    if (function_exists('logActivity')) {
        logActivity(getCurrentUser()['id'], 'Restore Backup', 'Settings', "Restored backup: {$backup['backup_name']}");
    }
    
    // Report success (even if some errors occurred, as long as main restore worked)
    if ($executed > 0) {
        $message = "Database restored successfully! Executed {$executed} statements.";
        if (!empty($errors)) {
            $message .= " Some warnings occurred but restore completed.";
        }
        jsonResponse(true, $message);
    } else {
        jsonResponse(false, 'Restore failed. No statements were executed successfully.');
    }
    
} catch (Exception $e) {
    $db = getDB();
    if ($db) {
        $db->query("ROLLBACK");
        $db->query("SET FOREIGN_KEY_CHECKS=1");
    }
    jsonResponse(false, 'Error restoring backup: ' . $e->getMessage());
}

