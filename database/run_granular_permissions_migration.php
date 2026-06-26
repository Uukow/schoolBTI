<?php
/**
 * Run Granular Permissions Migration
 * 
 * This script runs the database migration for the granular permissions system
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

// Define constants
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

// Include configuration
require_once ABSPATH . 'config/config.php';

// Check if system is installed
if (!SYSTEM_INSTALLED) {
    die("Error: System is not installed. Please run setup.php first.\n");
}

echo "========================================\n";
echo "Granular Permissions System Migration\n";
echo "========================================\n\n";

try {
    // Read migration SQL file
    $migrationFile = ABSPATH . 'database/granular_permissions_migration.sql';
    
    if (!file_exists($migrationFile)) {
        die("Error: Migration file not found: {$migrationFile}\n");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if (empty($sql)) {
        die("Error: Migration file is empty.\n");
    }
    
    // Split SQL into individual statements
    // Remove comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql);
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strlen(trim($stmt)) > 0;
        }
    );
    
    echo "Found " . count($statements) . " SQL statements to execute.\n\n";
    
    // Get database connection
    $conn = getDBConnection();
    
    if (!$conn) {
        die("Error: Could not connect to database.\n");
    }
    
    echo "Connected to database successfully.\n\n";
    
    // Execute each statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        if (empty($statement)) {
            continue;
        }
        
        // Skip CREATE INDEX IF NOT EXISTS (MySQL doesn't support it directly)
        if (stripos($statement, 'CREATE INDEX IF NOT EXISTS') !== false) {
            // Extract index name and table
            if (preg_match('/CREATE INDEX IF NOT EXISTS `?(\w+)`? ON `?(\w+)`?/i', $statement, $matches)) {
                $indexName = $matches[1];
                $tableName = $matches[2];
                
                // Check if index exists
                $checkSql = "SELECT COUNT(*) as count 
                            FROM information_schema.statistics 
                            WHERE table_schema = DATABASE() 
                            AND table_name = ? 
                            AND index_name = ?";
                
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param('ss', $tableName, $indexName);
                $checkStmt->execute();
                $result = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();
                
                if ($result['count'] > 0) {
                    echo "  [SKIP] Index {$indexName} already exists on {$tableName}\n";
                    $successCount++;
                    continue;
                }
                
                // Create index without IF NOT EXISTS
                $statement = str_ireplace('CREATE INDEX IF NOT EXISTS', 'CREATE INDEX', $statement);
            }
        }
        
        try {
            if ($conn->query($statement)) {
                $successCount++;
                echo "  [OK] Statement " . ($index + 1) . " executed successfully\n";
            } else {
                $errorCount++;
                echo "  [ERROR] Statement " . ($index + 1) . " failed: " . $conn->error . "\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "  [ERROR] Statement " . ($index + 1) . " failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n========================================\n";
    echo "Migration Summary\n";
    echo "========================================\n";
    echo "Successful: {$successCount}\n";
    echo "Errors: {$errorCount}\n";
    
    if ($errorCount === 0) {
        echo "\n✓ Migration completed successfully!\n";
        echo "\nNext steps:\n";
        echo "1. Access the permissions management page: " . APP_URL . "modules/settings/permissions.php\n";
        echo "2. Configure permissions for each role\n";
        echo "3. Test the permission system\n";
    } else {
        echo "\n⚠ Migration completed with errors. Please review the errors above.\n";
    }
    
} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n";

