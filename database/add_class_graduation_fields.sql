-- =============================================
-- Class Graduation & Academic Closure Feature
-- Database Migration Script
-- =============================================
-- 
-- This migration adds graduation status tracking to the classes table
-- and creates an audit log table for graduation actions.
--
-- Author: School ERP Development Team
-- Version: 1.0.0
-- Date: 2024
-- =============================================

-- Add graduation fields to classes table
ALTER TABLE `classes` 
ADD COLUMN `graduation_status` ENUM('Active', 'Graduated') DEFAULT 'Active' AFTER `is_active`,
ADD COLUMN `graduated_at` DATETIME DEFAULT NULL AFTER `graduation_status`,
ADD COLUMN `graduated_by` INT(11) DEFAULT NULL AFTER `graduated_at`,
ADD COLUMN `graduation_remarks` TEXT DEFAULT NULL AFTER `graduated_by`,
ADD INDEX `idx_graduation_status` (`graduation_status`),
ADD INDEX `idx_graduated_at` (`graduated_at`),
ADD CONSTRAINT `classes_graduated_by_fk` FOREIGN KEY (`graduated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Create class graduation audit log table
CREATE TABLE IF NOT EXISTS `class_graduation_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `class_id` INT(11) NOT NULL,
  `action` ENUM('Graduated', 'Reopened') NOT NULL,
  `students_affected` INT(11) DEFAULT 0,
  `performed_by` INT(11) NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `performed_by` (`performed_by`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `class_graduation_logs_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_graduation_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to classes table
ALTER TABLE `classes` 
COMMENT = 'Classes table with graduation status tracking';

-- Add comment to graduation logs table
ALTER TABLE `class_graduation_logs` 
COMMENT = 'Audit log for class graduation actions';

