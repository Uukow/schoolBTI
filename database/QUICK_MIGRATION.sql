-- QUICK MIGRATION: Add subject_id to student_attendance
-- Copy and paste this entire script into phpMyAdmin SQL tab and execute

-- Step 1: Add subject_id column
ALTER TABLE `student_attendance` 
ADD COLUMN `subject_id` int(11) DEFAULT NULL AFTER `section_id`;

-- Step 2: Add index
ALTER TABLE `student_attendance` 
ADD KEY `subject_id` (`subject_id`);

-- Step 3: Add foreign key constraint
ALTER TABLE `student_attendance` 
ADD CONSTRAINT `student_attendance_ibfk_subject` 
FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

-- Step 4: Drop old unique constraint
ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;

-- Step 5: Add new unique constraint
ALTER TABLE `student_attendance` 
ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`);



