<?php
/**
 * Run Attendance Migration Script
 * 
 * This script adds subject_id column to student_attendance table
 * Run this once before using subject-based attendance
 * 
 * Access: http://localhost/bti/database/run_attendance_migration.php
 */

// Define ABSPATH before requiring config
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is admin (optional - comment out for direct access)
// if (!isLoggedIn() || !hasRole(['Super Admin'])) {
//     die('Unauthorized. Only Super Admin can run migrations.');
// }

echo "<h2>Attendance Migration Script</h2>";
echo "<p>Adding subject_id column to student_attendance table...</p>";

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Check if column already exists
    $checkSql = "SHOW COLUMNS FROM student_attendance LIKE 'subject_id'";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Column 'subject_id' already exists. Migration already completed.</p>";
    } else {
        echo "<p>Column 'subject_id' not found. Running migration...</p>";
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Step 1: Add subject_id column
            echo "<p>Step 1: Adding subject_id column...</p>";
            $sql1 = "ALTER TABLE `student_attendance` 
                     ADD COLUMN `subject_id` int(11) DEFAULT NULL AFTER `section_id`";
            $conn->query($sql1);
            echo "<p style='color: green;'>✓ Column added successfully</p>";
            
            // Step 2: Add index
            echo "<p>Step 2: Adding index on subject_id...</p>";
            $sql2 = "ALTER TABLE `student_attendance` 
                     ADD KEY `subject_id` (`subject_id`)";
            $conn->query($sql2);
            echo "<p style='color: green;'>✓ Index added successfully</p>";
            
            // Step 3: Add foreign key constraint
            echo "<p>Step 3: Adding foreign key constraint...</p>";
            $sql3 = "ALTER TABLE `student_attendance` 
                     ADD CONSTRAINT `student_attendance_ibfk_subject` 
                     FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE";
            $conn->query($sql3);
            echo "<p style='color: green;'>✓ Foreign key constraint added successfully</p>";
            
            // Step 4: Drop old unique constraint (if it exists)
            echo "<p>Step 4: Dropping old unique constraint...</p>";
            
            // First check if constraint exists
            $checkConstraint = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_unique'";
            $constraintCheck = $conn->query($checkConstraint);
            
            if ($constraintCheck->num_rows > 0) {
                // Constraint exists, try to drop it
                try {
                    $sql4 = "ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`";
                    $conn->query($sql4);
                    echo "<p style='color: green;'>✓ Old constraint dropped successfully</p>";
                } catch (Exception $e1) {
                    // Try alternative method
                    try {
                        $sql4b = "DROP INDEX `student_date_unique` ON `student_attendance`";
                        $conn->query($sql4b);
                        echo "<p style='color: green;'>✓ Old constraint dropped successfully (using alternative method)</p>";
                    } catch (Exception $e2) {
                        // Last resort: use direct query
                        try {
                            $sql4c = "ALTER TABLE `student_attendance` DROP KEY `student_date_unique`";
                            $conn->query($sql4c);
                            echo "<p style='color: green;'>✓ Old constraint dropped successfully (using DROP KEY)</p>";
                        } catch (Exception $e3) {
                            echo "<p style='color: red;'>✗ Failed to drop old constraint: " . htmlspecialchars($e3->getMessage()) . "</p>";
                            echo "<p style='color: orange;'>⚠ Please run this SQL manually: ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;</p>";
                            throw new Exception("Failed to drop old constraint. Please run SQL manually.");
                        }
                    }
                }
            } else {
                echo "<p style='color: orange;'>⚠ Old constraint does not exist (already removed or never existed)</p>";
            }
            
            // Step 5: Add new unique constraint
            echo "<p>Step 5: Adding new unique constraint (student_id, attendance_date, subject_id)...</p>";
            $sql5 = "ALTER TABLE `student_attendance` 
                     ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`)";
            $conn->query($sql5);
            echo "<p style='color: green;'>✓ New constraint added successfully</p>";
            
            // Commit transaction
            $conn->commit();
            
            echo "<h3 style='color: green;'>✓ Migration completed successfully!</h3>";
            echo "<p><strong>Next steps:</strong></p>";
            echo "<ul>";
            echo "<li>Teachers can now mark attendance per subject</li>";
            echo "<li>Students will see per-subject attendance percentages</li>";
            echo "<li>Old attendance records will have subject_id = NULL (they will still work)</li>";
            echo "</ul>";
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}

echo "<hr>";
echo "<p><a href='" . APP_URL . "dashboard.php'>Return to Dashboard</a></p>";

