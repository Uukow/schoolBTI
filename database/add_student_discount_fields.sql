-- Add Student Discount Management Fields
-- This migration adds discount_type and discount_value fields to the students table

ALTER TABLE `students` 
ADD COLUMN `discount_type` enum('Fixed','Percentage') DEFAULT NULL AFTER `special_needs`,
ADD COLUMN `discount_value` decimal(10,2) DEFAULT NULL AFTER `discount_type`;

-- Add index for better query performance
ALTER TABLE `students` 
ADD INDEX `idx_discount` (`discount_type`, `discount_value`);

