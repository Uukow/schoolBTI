-- Migration: Add subject_id to student_attendance table
-- This allows attendance to be tracked per subject, not just per class
-- Date: 2025-01-XX

-- Add subject_id column to student_attendance table
ALTER TABLE `student_attendance` 
ADD COLUMN `subject_id` int(11) DEFAULT NULL AFTER `section_id`,
ADD KEY `subject_id` (`subject_id`),
ADD CONSTRAINT `student_attendance_ibfk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

-- Update unique constraint to include subject_id
-- First, drop the old unique constraint
ALTER TABLE `student_attendance` 
DROP INDEX `student_date_unique`;

-- Add new unique constraint: one attendance record per student per date per subject
ALTER TABLE `student_attendance` 
ADD UNIQUE KEY `student_date_subject_unique` (`student_id`, `attendance_date`, `subject_id`);

-- Note: For existing records without subject_id, they will remain NULL
-- Teachers will need to mark attendance again with subject selection going forward



