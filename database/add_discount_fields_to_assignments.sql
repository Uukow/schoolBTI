-- Add Discount Fields to Monthly Fee Assignments
-- This migration adds discount tracking fields to monthly_fee_assignments table

ALTER TABLE `monthly_fee_assignments` 
ADD COLUMN `original_amount` decimal(10,2) DEFAULT NULL COMMENT 'Original fee amount before discount' AFTER `amount`,
ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Discount amount applied' AFTER `original_amount`,
ADD COLUMN `discount_type` enum('Fixed','Percentage') DEFAULT NULL COMMENT 'Type of discount applied' AFTER `discount_amount`;

-- Update existing records: set original_amount = assigned_amount if original_amount is NULL
UPDATE `monthly_fee_assignments` 
SET `original_amount` = `assigned_amount` 
WHERE `original_amount` IS NULL;

