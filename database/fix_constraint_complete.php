<?php
/**
 * Complete Fix: Handle Foreign Key Constraint Issue
 * 
 * This script will:
 * 1. Find foreign keys using the old index
 * 2. Drop foreign keys
 * 3. Drop old constraint
 * 4. Add new constraint
 * 5. Re-add foreign keys if needed
 * 
 * Access: http://localhost/bti/database/fix_constraint_complete.php
 */

defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

require_once '../config/config.php';
require_once '../config/database.php';

echo "<!DOCTYPE html><html><head><title>Complete Constraint Fix</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:900px;margin:50px auto;padding:20px;}";
echo ".success{color:green;padding:15px;background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;}";
echo ".error{color:red;padding:15px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;}";
echo ".info{color:blue;padding:15px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:5px;margin:10px 0;}";
echo ".warning{color:orange;padding:15px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:5px;margin:10px 0;}";
echo "h2{color:#333;} code{background:#f4f4f4;padding:4px 8px;border-radius:3px;display:block;margin:5px 0;}";
echo "pre{background:#f4f4f4;padding:10px;border-radius:5px;overflow-x:auto;}</style></head><body>";

echo "<h2>🔧 Complete Constraint Fix</h2>";

try {
    $conn = getDBConnection();
    
    // Step 1: Get table structure to find foreign keys
    echo "<div class='info'><strong>Step 1:</strong> Checking table structure...</div>";
    $createTableSql = "SHOW CREATE TABLE `student_attendance`";
    $result = $conn->query($createTableSql);
    $tableInfo = $result->fetch_assoc();
    $createTable = $tableInfo['Create Table'] ?? '';
    
    // Extract foreign keys
    preg_match_all("/CONSTRAINT `([^`]+)` FOREIGN KEY/", $createTable, $fkMatches);
    $foreignKeys = $fkMatches[1] ?? [];
    
    echo "<div class='info'>Found " . count($foreignKeys) . " foreign key(s)</div>";
    
    // Step 2: Drop foreign keys that might be using the index
    if (!empty($foreignKeys)) {
        echo "<div class='info'><strong>Step 2:</strong> Dropping foreign keys...</div>";
        foreach ($foreignKeys as $fkName) {
            try {
                $dropFkSql = "ALTER TABLE `student_attendance` DROP FOREIGN KEY `$fkName`";
                $conn->query($dropFkSql);
                echo "<div class='success'>✓ Dropped foreign key: <code>$fkName</code></div>";
            } catch (Exception $e) {
                echo "<div class='warning'>⚠ Could not drop foreign key $fkName: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    
    // Step 3: Drop the old unique constraint
    echo "<div class='info'><strong>Step 3:</strong> Dropping old unique constraint...</div>";
    $dropMethods = [
        "ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`",
        "DROP INDEX `student_date_unique` ON `student_attendance`",
        "ALTER TABLE `student_attendance` DROP KEY `student_date_unique`"
    ];
    
    $dropped = false;
    foreach ($dropMethods as $index => $sql) {
        try {
            $conn->query($sql);
            echo "<div class='success'>✓ Old constraint dropped successfully (method " . ($index + 1) . ")</div>";
            $dropped = true;
            break;
        } catch (Exception $e) {
            if ($index === count($dropMethods) - 1) {
                echo "<div class='error'>✗ Failed to drop constraint: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<div class='info'><strong>Manual SQL needed:</strong><br>";
                echo "<code>ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;</code></div>";
            }
        }
    }
    
    if (!$dropped) {
        echo "<div class='error'><strong>⚠ Could not drop constraint automatically.</strong><br>";
        echo "Please check if there are any other constraints or indexes referencing it.</div>";
    }
    
    // Step 4: Check if subject_id column exists
    echo "<div class='info'><strong>Step 4:</strong> Checking subject_id column...</div>";
    $checkColumnSql = "SHOW COLUMNS FROM student_attendance LIKE 'subject_id'";
    $columnResult = $conn->query($checkColumnSql);
    
    if ($columnResult->num_rows == 0) {
        echo "<div class='error'>✗ Column 'subject_id' does not exist. Please run the full migration first.</div>";
        echo "<div class='info'><a href='run_attendance_migration.php'>Run Full Migration</a></div>";
    } else {
        echo "<div class='success'>✓ Column 'subject_id' exists</div>";
    }
    
    // Step 5: Add new unique constraint
    if ($dropped || $columnResult->num_rows > 0) {
        echo "<div class='info'><strong>Step 5:</strong> Adding new unique constraint...</div>";
        
        // Check if new constraint already exists
        $checkNewSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_subject_unique'";
        $newResult = $conn->query($checkNewSql);
        
        if ($newResult->num_rows == 0) {
            try {
                $addConstraintSql = "ALTER TABLE `student_attendance` 
                                    ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`)";
                $conn->query($addConstraintSql);
                echo "<div class='success'>✓ New constraint added successfully</div>";
            } catch (Exception $e) {
                echo "<div class='error'>✗ Failed to add new constraint: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div class='info'>✓ New constraint already exists</div>";
        }
    }
    
    // Step 6: Re-add foreign keys (if they were dropped)
    if (!empty($foreignKeys) && $dropped) {
        echo "<div class='warning'><strong>Step 6:</strong> Foreign keys were dropped. You may need to re-add them.</strong><br>";
        echo "Check your database schema to see which foreign keys need to be re-added.</div>";
    }
    
    // Final verification
    echo "<div class='info'><strong>Final Verification:</strong></div>";
    $finalCheckOld = $conn->query("SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_unique'");
    $finalCheckNew = $conn->query("SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_subject_unique'");
    
    if ($finalCheckOld->num_rows == 0 && $finalCheckNew->num_rows > 0) {
        echo "<div class='success'><h3>✅ SUCCESS! Constraint fixed successfully!</h3>";
        echo "<p>You can now save attendance without errors.</p>";
        echo "<p><a href='" . APP_URL . "modules/teacher/attendance-classes.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Go to Attendance Page</a></p></div>";
    } else {
        echo "<div class='error'><h3>⚠ Status Check:</h3>";
        echo "<ul>";
        echo "<li>Old constraint exists: " . ($finalCheckOld->num_rows > 0 ? "YES ❌" : "NO ✓") . "</li>";
        echo "<li>New constraint exists: " . ($finalCheckNew->num_rows > 0 ? "YES ✓" : "NO ❌") . "</li>";
        echo "</ul>";
        echo "<p>If old constraint still exists, you may need to manually drop it in phpMyAdmin.</p></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h3>✗ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
}

echo "<hr>";
echo "<p><a href='" . APP_URL . "dashboard.php'>Return to Dashboard</a> | ";
echo "<a href='run_attendance_migration.php'>Run Full Migration</a></p>";
echo "</body></html>";



