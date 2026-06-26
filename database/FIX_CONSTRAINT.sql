-- FIX: Remove old constraint and add new one
-- Run this SQL directly in phpMyAdmin if the migration script fails

-- Step 1: Check if old constraint exists and drop it
-- Try method 1
ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;

-- If above fails, try method 2
-- DROP INDEX `student_date_unique` ON `student_attendance`;

-- Step 2: Add new unique constraint (if not exists)
-- This allows one attendance record per student per date per subject
ALTER TABLE `student_attendance` 
ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`);



