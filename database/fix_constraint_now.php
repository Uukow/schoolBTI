<?php
/**
 * Quick Fix: Drop Old Constraint and Add New One
 * 
 * Run this to fix the constraint issue immediately
 * Access: http://localhost/bti/database/fix_constraint_now.php
 */

// Define ABSPATH before requiring config
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

require_once '../config/config.php';
require_once '../config/database.php';

echo "<!DOCTYPE html><html><head><title>Fix Constraint</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;}";
echo ".info{color:blue;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:5px;margin:10px 0;}";
echo "h2{color:#333;} code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}</style></head><body>";

echo "<h2>🔧 Fix Attendance Constraint</h2>";

try {
    $conn = getDBConnection();
    
    // Step 1: Check if old constraint exists
    echo "<div class='info'><strong>Step 1:</strong> Checking for old constraint...</div>";
    $checkSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_unique'";
    $result = $conn->query($checkSql);
    $oldConstraintExists = ($result->num_rows > 0);
    
    if ($oldConstraintExists) {
        echo "<div class='info'>✓ Found old constraint: <code>student_date_unique</code></div>";
        
        // Step 2: Drop old constraint
        echo "<div class='info'><strong>Step 2:</strong> Dropping old constraint...</div>";
        
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
                }
            }
        }
        
        if (!$dropped) {
            echo "<div class='error'><strong>Failed to drop constraint automatically.</strong><br>";
            echo "Please run this SQL manually in phpMyAdmin:<br>";
            echo "<code>ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;</code></div>";
            echo "</body></html>";
            exit;
        }
    } else {
        echo "<div class='info'>⚠ Old constraint does not exist (already removed)</div>";
    }
    
    // Step 3: Check if new constraint exists
    echo "<div class='info'><strong>Step 3:</strong> Checking for new constraint...</div>";
    $checkNewSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_subject_unique'";
    $newResult = $conn->query($checkNewSql);
    $newConstraintExists = ($newResult->num_rows > 0);
    
    if (!$newConstraintExists) {
        echo "<div class='info'><strong>Step 4:</strong> Adding new constraint...</div>";
        
        // Check if subject_id column exists
        $checkColumnSql = "SHOW COLUMNS FROM student_attendance LIKE 'subject_id'";
        $columnResult = $conn->query($checkColumnSql);
        
        if ($columnResult->num_rows == 0) {
            echo "<div class='error'>✗ Column 'subject_id' does not exist. Please run the full migration first: ";
            echo "<a href='run_attendance_migration.php'>Run Full Migration</a></div>";
            echo "</body></html>";
            exit;
        }
        
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
    
    // Final check
    echo "<div class='info'><strong>Final Check:</strong></div>";
    $finalCheckOld = $conn->query("SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_unique'");
    $finalCheckNew = $conn->query("SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_subject_unique'");
    
    if ($finalCheckOld->num_rows == 0 && $finalCheckNew->num_rows > 0) {
        echo "<div class='success'><h3>✅ SUCCESS! Constraint fixed successfully!</h3>";
        echo "<p>You can now save attendance without errors.</p>";
        echo "<p><a href='" . APP_URL . "modules/teacher/attendance-classes.php'>Go to Attendance Page</a></p></div>";
    } else {
        echo "<div class='error'><h3>⚠ Status:</h3>";
        echo "<ul>";
        echo "<li>Old constraint exists: " . ($finalCheckOld->num_rows > 0 ? "YES ❌" : "NO ✓") . "</li>";
        echo "<li>New constraint exists: " . ($finalCheckNew->num_rows > 0 ? "YES ✓" : "NO ❌") . "</li>";
        echo "</ul></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h3>✗ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection.</p></div>";
}

echo "<hr>";
echo "<p><a href='" . APP_URL . "dashboard.php'>Return to Dashboard</a> | ";
echo "<a href='run_attendance_migration.php'>Run Full Migration</a></p>";
echo "</body></html>";



