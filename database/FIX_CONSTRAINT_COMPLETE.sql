-- COMPLETE FIX: Drop foreign key, then drop old constraint, then add new one
-- Run this SQL in phpMyAdmin step by step

-- Step 1: Find and drop any foreign keys that reference student_attendance
-- Check what foreign keys exist first:
-- SHOW CREATE TABLE `student_attendance`;

-- Step 2: Drop the foreign key constraint (if it exists)
-- Note: Replace 'fk_name' with the actual foreign key name from Step 1
-- ALTER TABLE `student_attendance` DROP FOREIGN KEY `fk_name`;

-- Step 3: Drop the old unique constraint
ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;

-- Step 4: Add the new unique constraint
ALTER TABLE `student_attendance` 
ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`);

-- Step 5: Re-add foreign key if needed (check your schema)
-- ALTER TABLE `student_attendance` 
-- ADD CONSTRAINT `student_attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;



