<?php
/**
 * Attendance System Migration Script
 * 
 * Ensures the student_attendance table has subject_id column and proper constraints
 * for the role-based attendance system
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System Migration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Attendance System Migration</h1>
        <p>This script will ensure your database is properly configured for the role-based attendance system.</p>
        
        <?php
        $errors = [];
        $warnings = [];
        $success = [];
        
        try {
            global $conn;
            
            // Step 1: Check if subject_id column exists
            echo "<div class='step'>";
            echo "<h3>Step 1: Checking subject_id column...</h3>";
            
            $checkColumnSql = "SHOW COLUMNS FROM student_attendance LIKE 'subject_id'";
            $columnResult = $conn->query($checkColumnSql);
            $hasSubjectId = ($columnResult->num_rows > 0);
            
            if ($hasSubjectId) {
                echo "<p class='success'>✓ Column 'subject_id' already exists.</p>";
                $success[] = "subject_id column exists";
            } else {
                echo "<p class='info'>Column 'subject_id' not found. Adding it now...</p>";
                
                // Add subject_id column
                $addColumnSql = "ALTER TABLE `student_attendance` 
                                ADD COLUMN `subject_id` int(11) DEFAULT NULL AFTER `section_id`";
                
                if ($conn->query($addColumnSql)) {
                    echo "<p class='success'>✓ Column 'subject_id' added successfully.</p>";
                    $success[] = "subject_id column added";
                } else {
                    $error = "Failed to add subject_id column: " . $conn->error;
                    echo "<p class='error'>✗ $error</p>";
                    $errors[] = $error;
                }
            }
            echo "</div>";
            
            // Step 2: Check if index exists
            echo "<div class='step'>";
            echo "<h3>Step 2: Checking indexes...</h3>";
            
            $checkIndexSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'subject_id'";
            $indexResult = $conn->query($checkIndexSql);
            $hasIndex = ($indexResult->num_rows > 0);
            
            if ($hasIndex) {
                echo "<p class='success'>✓ Index on 'subject_id' already exists.</p>";
                $success[] = "subject_id index exists";
            } else {
                echo "<p class='info'>Index on 'subject_id' not found. Adding it now...</p>";
                
                $addIndexSql = "ALTER TABLE `student_attendance` 
                               ADD KEY `subject_id` (`subject_id`)";
                
                if ($conn->query($addIndexSql)) {
                    echo "<p class='success'>✓ Index on 'subject_id' added successfully.</p>";
                    $success[] = "subject_id index added";
                } else {
                    $error = "Failed to add index: " . $conn->error;
                    echo "<p class='error'>✗ $error</p>";
                    $errors[] = $error;
                }
            }
            echo "</div>";
            
            // Step 3: Check if foreign key exists
            echo "<div class='step'>";
            echo "<h3>Step 3: Checking foreign key constraint...</h3>";
            
            $checkFkSql = "SELECT CONSTRAINT_NAME 
                          FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND TABLE_NAME = 'student_attendance' 
                          AND COLUMN_NAME = 'subject_id' 
                          AND REFERENCED_TABLE_NAME = 'subjects'";
            $fkResult = $conn->query($checkFkSql);
            $hasFk = ($fkResult->num_rows > 0);
            
            if ($hasFk) {
                echo "<p class='success'>✓ Foreign key constraint already exists.</p>";
                $success[] = "Foreign key constraint exists";
            } else {
                echo "<p class='info'>Foreign key constraint not found. Adding it now...</p>";
                
                // First, check if there are any invalid subject_id values
                $checkInvalidSql = "SELECT COUNT(*) as count 
                                   FROM student_attendance sa 
                                   LEFT JOIN subjects s ON sa.subject_id = s.id 
                                   WHERE sa.subject_id IS NOT NULL AND s.id IS NULL";
                $invalidResult = $conn->query($checkInvalidSql);
                $invalidRow = $invalidResult->fetch_assoc();
                
                if ($invalidRow['count'] > 0) {
                    echo "<p class='warning'>⚠ Found {$invalidRow['count']} records with invalid subject_id. These will be set to NULL.</p>";
                    
                    // Set invalid subject_ids to NULL
                    $fixInvalidSql = "UPDATE student_attendance sa 
                                     LEFT JOIN subjects s ON sa.subject_id = s.id 
                                     SET sa.subject_id = NULL 
                                     WHERE sa.subject_id IS NOT NULL AND s.id IS NULL";
                    $conn->query($fixInvalidSql);
                    echo "<p class='info'>✓ Invalid subject_id values have been set to NULL.</p>";
                }
                
                // Add foreign key constraint
                $addFkSql = "ALTER TABLE `student_attendance` 
                            ADD CONSTRAINT `student_attendance_ibfk_subject` 
                            FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE";
                
                if ($conn->query($addFkSql)) {
                    echo "<p class='success'>✓ Foreign key constraint added successfully.</p>";
                    $success[] = "Foreign key constraint added";
                } else {
                    $error = "Failed to add foreign key constraint: " . $conn->error;
                    echo "<p class='error'>✗ $error</p>";
                    $errors[] = $error;
                }
            }
            echo "</div>";
            
            // Step 4: Check and update unique constraint
            echo "<div class='step'>";
            echo "<h3>Step 4: Checking unique constraint...</h3>";
            
            // Check if old constraint exists
            $checkOldConstraintSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_unique'";
            $oldConstraintResult = $conn->query($checkOldConstraintSql);
            $hasOldConstraint = ($oldConstraintResult->num_rows > 0);
            
            // Check if new constraint exists
            $checkNewConstraintSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_subject_unique'";
            $newConstraintResult = $conn->query($checkNewConstraintSql);
            $hasNewConstraint = ($newConstraintResult->num_rows > 0);
            
            if ($hasNewConstraint) {
                echo "<p class='success'>✓ New unique constraint (student_id, attendance_date, subject_id) already exists.</p>";
                $success[] = "New unique constraint exists";
                
                if ($hasOldConstraint) {
                    echo "<p class='warning'>⚠ Old unique constraint (student_date_unique) still exists. Removing it...</p>";
                    
                    // Drop old constraint
                    $dropOldConstraintSql = "ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`";
                    if ($conn->query($dropOldConstraintSql)) {
                        echo "<p class='success'>✓ Old unique constraint removed successfully.</p>";
                        $success[] = "Old unique constraint removed";
                    } else {
                        $error = "Failed to remove old constraint: " . $conn->error;
                        echo "<p class='error'>✗ $error</p>";
                        $warnings[] = $error;
                    }
                }
            } else {
                echo "<p class='info'>New unique constraint not found. Adding it now...</p>";
                
                // First, check for duplicate records that would violate the new constraint
                $checkDuplicatesSql = "SELECT student_id, attendance_date, subject_id, COUNT(*) as count 
                                      FROM student_attendance 
                                      WHERE subject_id IS NOT NULL
                                      GROUP BY student_id, attendance_date, subject_id 
                                      HAVING count > 1";
                $duplicatesResult = $conn->query($checkDuplicatesSql);
                
                if ($duplicatesResult->num_rows > 0) {
                    echo "<p class='warning'>⚠ Found duplicate records. These need to be cleaned up before adding the constraint.</p>";
                    echo "<p class='info'>Please review and clean up duplicate attendance records manually.</p>";
                    $warnings[] = "Duplicate records found - manual cleanup required";
                } else {
                    // Drop old constraint if it exists
                    if ($hasOldConstraint) {
                        echo "<p class='info'>Removing old unique constraint...</p>";
                        $dropOldConstraintSql = "ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`";
                        $conn->query($dropOldConstraintSql);
                    }
                    
                    // Add new unique constraint
                    $addConstraintSql = "ALTER TABLE `student_attendance` 
                                       ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`)";
                    
                    if ($conn->query($addConstraintSql)) {
                        echo "<p class='success'>✓ New unique constraint added successfully.</p>";
                        $success[] = "New unique constraint added";
                    } else {
                        $error = "Failed to add unique constraint: " . $conn->error;
                        echo "<p class='error'>✗ $error</p>";
                        $errors[] = $error;
                    }
                }
            }
            echo "</div>";
            
            // Step 5: Verify migration
            echo "<div class='step'>";
            echo "<h3>Step 5: Verification...</h3>";
            
            // Final verification
            $verifyColumnSql = "SHOW COLUMNS FROM student_attendance LIKE 'subject_id'";
            $verifyColumnResult = $conn->query($verifyColumnSql);
            $columnExists = ($verifyColumnResult->num_rows > 0);
            
            $verifyConstraintSql = "SHOW INDEX FROM student_attendance WHERE Key_name = 'student_date_subject_unique'";
            $verifyConstraintResult = $conn->query($verifyConstraintSql);
            $constraintExists = ($verifyConstraintResult->num_rows > 0);
            
            if ($columnExists && $constraintExists) {
                echo "<p class='success'>✓ Migration completed successfully!</p>";
                echo "<p class='info'>Your database is now ready for the role-based attendance system.</p>";
            } else {
                echo "<p class='warning'>⚠ Migration may be incomplete. Please review the steps above.</p>";
            }
            echo "</div>";
            
            // Summary
            echo "<div class='step'>";
            echo "<h3>Migration Summary</h3>";
            echo "<p><strong>Success:</strong> " . count($success) . " operations completed</p>";
            echo "<p><strong>Warnings:</strong> " . count($warnings) . " issues found</p>";
            echo "<p><strong>Errors:</strong> " . count($errors) . " errors encountered</p>";
            
            if (count($errors) == 0) {
                echo "<p class='success'><strong>✓ Migration successful! Your attendance system is ready to use.</strong></p>";
            } else {
                echo "<p class='error'><strong>✗ Migration completed with errors. Please review and fix the issues above.</strong></p>";
            }
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='step'>";
            echo "<p class='error'>✗ Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
            <a href="<?php echo APP_URL; ?>modules/attendance/dashboard.php" class="btn">Go to Attendance Dashboard</a>
            <a href="<?php echo APP_URL; ?>dashboard.php" class="btn" style="background: #6c757d;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>


