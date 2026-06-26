-- COMPLETE FIX: Drop foreign keys, drop old constraint, add new constraint, re-add foreign keys
-- Run this SQL in phpMyAdmin

-- Step 1: Drop foreign keys that reference student_attendance columns
ALTER TABLE `student_attendance` DROP FOREIGN KEY `student_attendance_ibfk_1`;
ALTER TABLE `student_attendance` DROP FOREIGN KEY `student_attendance_ibfk_2`;
ALTER TABLE `student_attendance` DROP FOREIGN KEY `student_attendance_ibfk_3`;

-- Step 2: Drop the old unique constraint
ALTER TABLE `student_attendance` DROP INDEX `student_date_unique`;

-- Step 3: Add the new unique constraint (with subject_id)
ALTER TABLE `student_attendance` 
ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`);

-- Step 4: Re-add foreign keys
ALTER TABLE `student_attendance` 
ADD CONSTRAINT `student_attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

ALTER TABLE `student_attendance` 
ADD CONSTRAINT `student_attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

ALTER TABLE `student_attendance` 
ADD CONSTRAINT `student_attendance_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

-- Step 5: Add foreign key for subject_id (if not already added)
ALTER TABLE `student_attendance` 
ADD CONSTRAINT `student_attendance_ibfk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;



