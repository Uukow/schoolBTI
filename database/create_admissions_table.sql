-- =============================================
-- Admissions Management Table
-- Database Migration Script
-- =============================================

-- Create admissions table
CREATE TABLE IF NOT EXISTS `admissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `application_number` VARCHAR(50) NOT NULL UNIQUE,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `class_applied_for` INT(11) DEFAULT NULL,
  `branch_id` INT(11) NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Enrolled') DEFAULT 'Pending',
  `guardian_name` VARCHAR(200) DEFAULT NULL,
  `guardian_phone` VARCHAR(20) DEFAULT NULL,
  `guardian_email` VARCHAR(100) DEFAULT NULL,
  `guardian_relation` VARCHAR(50) DEFAULT NULL,
  `previous_school` VARCHAR(200) DEFAULT NULL,
  `application_date` DATE NOT NULL DEFAULT (CURDATE()),
  `reviewed_date` DATE DEFAULT NULL,
  `reviewed_by` INT(11) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `application_fee` DECIMAL(10,2) DEFAULT 0.00,
  `payment_status` ENUM('Paid', 'Unpaid', 'Partial') DEFAULT 'Unpaid',
  `documents_submitted` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_application_number` (`application_number`),
  KEY `idx_status` (`status`),
  KEY `idx_application_date` (`application_date`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_class_applied_for` (`class_applied_for`),
  KEY `idx_reviewed_by` (`reviewed_by`),
  CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admissions_ibfk_2` FOREIGN KEY (`class_applied_for`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_name_search ON admissions(first_name, last_name);
CREATE INDEX idx_payment_status ON admissions(payment_status);

-- Insert sample data (optional - for testing)
INSERT INTO `admissions` (
  `application_number`, 
  `first_name`, 
  `last_name`, 
  `gender`, 
  `date_of_birth`, 
  `email`, 
  `phone`, 
  `address`, 
  `class_applied_for`, 
  `branch_id`, 
  `status`, 
  `guardian_name`, 
  `guardian_phone`,
  `application_date`,
  `application_fee`,
  `payment_status`
) VALUES
('APP2024001', 'Mohamed', 'Ahmed', 'Male', '2010-05-15', 'mohamed@example.com', '123456789', 'Mogadishu', 2, 2, 'Pending', 'Ahmed Ali', '987654321', '2024-12-01', 50.00, 'Paid'),
('APP2024002', 'Fatima', 'Hassan', 'Female', '2011-03-20', 'fatima@example.com', '123456790', 'Hargeisa', 2, 2, 'Pending', 'Hassan Omar', '987654322', '2024-12-05', 50.00, 'Paid'),
('APP2024003', 'Omar', 'Ibrahim', 'Male', '2010-08-10', 'omar@example.com', '123456791', 'Berbera', 3, 2, 'Approved', 'Ibrahim Yusuf', '987654323', '2024-11-28', 50.00, 'Paid'),
('APP2024004', 'Amina', 'Ali', 'Female', '2011-01-25', 'amina@example.com', '123456792', 'Borama', 2, 2, 'Approved', 'Ali Mohamed', '987654324', '2024-11-25', 50.00, 'Paid'),
('APP2024005', 'Abdullahi', 'Osman', 'Male', '2010-12-05', 'abdullahi@example.com', '123456793', 'Burco', 3, 2, 'Rejected', 'Osman Abdi', '987654325', '2024-11-20', 50.00, 'Unpaid');

-- Add comment
ALTER TABLE `admissions` 
COMMENT = 'Student admission applications management table';














