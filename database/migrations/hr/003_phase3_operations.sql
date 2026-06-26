-- TacliinHub HR Phase 3 Migration
-- Grievances, item requests, quotations, PPDP, recruitment

-- =============================================
-- 1. GRIEVANCES
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_grievances` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `grievance_no` VARCHAR(30) NOT NULL,
  `staff_id` INT NULL,
  `is_anonymous` TINYINT(1) DEFAULT 0,
  `category` ENUM('Harassment','Discrimination','Working_Conditions','Payroll','Other') DEFAULT 'Other',
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status` ENUM('Submitted','Under_Review','Investigating','Resolved','Closed','Escalated') DEFAULT 'Submitted',
  `assigned_to` INT NULL,
  `branch_id` INT NULL,
  `resolution` TEXT NULL,
  `resolved_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grievance_no` (`grievance_no`),
  KEY `staff_id` (`staff_id`),
  KEY `status` (`status`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `hr_grievances_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hr_grievances_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_grievance_actions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `grievance_id` INT NOT NULL,
  `action_by` INT NULL,
  `action_type` VARCHAR(50) NOT NULL,
  `comment` TEXT NULL,
  `is_internal` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `grievance_id` (`grievance_id`),
  CONSTRAINT `hr_grievance_actions_ibfk_1` FOREIGN KEY (`grievance_id`) REFERENCES `hr_grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. ITEM REQUESTS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_item_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `request_no` VARCHAR(30) NOT NULL,
  `staff_id` INT NOT NULL,
  `branch_id` INT NULL,
  `purpose` TEXT NOT NULL,
  `priority` ENUM('Normal','Urgent') DEFAULT 'Normal',
  `status` ENUM('Draft','Submitted','L1_Approved','L2_Approved','Fulfilled','Rejected','Cancelled') DEFAULT 'Submitted',
  `approved_by` INT NULL,
  `approved_at` DATETIME NULL,
  `fulfilled_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_no` (`request_no`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_item_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_item_request_lines` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `request_id` INT NOT NULL,
  `inventory_item_id` INT NULL,
  `item_description` VARCHAR(255) NOT NULL,
  `quantity_requested` INT NOT NULL DEFAULT 1,
  `quantity_approved` INT NULL,
  `quantity_issued` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `inventory_item_id` (`inventory_item_id`),
  CONSTRAINT `hr_item_request_lines_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `hr_item_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_item_request_lines_ibfk_2` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `inventory_item_id` INT NOT NULL,
  `transaction_type` ENUM('Issue','Receive','Adjustment','Return') NOT NULL,
  `quantity` INT NOT NULL,
  `reference_type` VARCHAR(50) NULL,
  `reference_id` INT NULL,
  `notes` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_item_id` (`inventory_item_id`),
  CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. QUOTATIONS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_quotations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quotation_no` VARCHAR(30) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `requested_by` INT NULL,
  `branch_id` INT NULL,
  `required_by_date` DATE NULL,
  `status` ENUM('Draft','Pending_Approval','Approved','Rejected','Closed') DEFAULT 'Draft',
  `total_estimated` DECIMAL(15,2) DEFAULT 0,
  `approved_by` INT NULL,
  `approved_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_no` (`quotation_no`),
  KEY `branch_id` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_quotation_vendors` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quotation_id` INT NOT NULL,
  `vendor_name` VARCHAR(255) NOT NULL,
  `vendor_contact` VARCHAR(255) NULL,
  `quoted_amount` DECIMAL(15,2) NOT NULL,
  `delivery_days` INT NULL,
  `attachment_path` VARCHAR(500) NULL,
  `is_selected` TINYINT(1) DEFAULT 0,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  CONSTRAINT `hr_quotation_vendors_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `hr_quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. PPDP PROGRAMS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_ppdp_programs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `program_code` VARCHAR(30) NOT NULL,
  `program_name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `capacity` INT DEFAULT 30,
  `branch_id` INT NULL,
  `facilitator_id` INT NULL,
  `status` ENUM('Planned','Open','In_Progress','Completed','Cancelled') DEFAULT 'Planned',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_code` (`program_code`),
  KEY `branch_id` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_ppdp_participants` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `program_id` INT NOT NULL,
  `staff_id` INT NOT NULL,
  `registration_date` DATE NOT NULL,
  `status` ENUM('Registered','Attending','Completed','Dropped','Failed') DEFAULT 'Registered',
  `progress_percent` INT DEFAULT 0,
  `assessment_score` DECIMAL(5,2) NULL,
  `certificate_no` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_staff` (`program_id`, `staff_id`),
  CONSTRAINT `hr_ppdp_participants_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `hr_ppdp_programs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_ppdp_participants_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. RECRUITMENT
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_job_vacancies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vacancy_no` VARCHAR(30) NOT NULL,
  `job_title` VARCHAR(255) NOT NULL,
  `department` VARCHAR(100) NULL,
  `branch_id` INT NULL,
  `employment_type` VARCHAR(50) DEFAULT 'Full Time',
  `description` TEXT NULL,
  `requirements` TEXT NULL,
  `salary_range_min` DECIMAL(15,2) NULL,
  `salary_range_max` DECIMAL(15,2) NULL,
  `openings` INT DEFAULT 1,
  `application_deadline` DATE NULL,
  `status` ENUM('Draft','Published','Closed','Filled','Cancelled') DEFAULT 'Draft',
  `published_at` DATETIME NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vacancy_no` (`vacancy_no`),
  KEY `branch_id` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_job_applications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `application_no` VARCHAR(30) NOT NULL,
  `vacancy_id` INT NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `cv_path` VARCHAR(500) NULL,
  `cover_letter` TEXT NULL,
  `status` ENUM('Applied','Screening','Shortlisted','Interview','Offer','Hired','Rejected') DEFAULT 'Applied',
  `screening_score` DECIMAL(5,2) NULL,
  `rank` INT NULL,
  `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_no` (`application_no`),
  KEY `vacancy_id` (`vacancy_id`),
  CONSTRAINT `hr_job_applications_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `hr_job_vacancies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_interviews` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `application_id` INT NOT NULL,
  `interview_date` DATETIME NOT NULL,
  `interview_type` ENUM('Phone','Video','In_Person','Panel') DEFAULT 'In_Person',
  `location_or_link` VARCHAR(500) NULL,
  `status` ENUM('Scheduled','Completed','Cancelled','No_Show') DEFAULT 'Scheduled',
  `overall_rating` DECIMAL(3,1) NULL,
  `recommendation` VARCHAR(50) NULL,
  `comments` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`),
  CONSTRAINT `hr_interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `hr_job_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
