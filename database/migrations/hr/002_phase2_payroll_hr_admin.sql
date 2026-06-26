-- TacliinHub HR Phase 2 Migration
-- Employee administration, payroll components, advances

-- =============================================
-- 1. EMPLOYEE DOCUMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_employee_documents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `document_type` VARCHAR(100) NOT NULL,
  `document_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `expiry_date` DATE NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `verified_by` INT NULL,
  `verified_at` DATETIME NULL,
  `notes` TEXT NULL,
  `uploaded_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_employee_documents_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. EMPLOYEE CONTRACTS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_contracts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `contract_no` VARCHAR(30) NOT NULL,
  `staff_id` INT NOT NULL,
  `contract_type` ENUM('Permanent','Fixed_Term','Probation','Consultancy') DEFAULT 'Permanent',
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `salary_amount` DECIMAL(15,2) NULL,
  `status` ENUM('Draft','Active','Expired','Terminated','Renewed') DEFAULT 'Active',
  `file_path` VARCHAR(500) NULL,
  `notes` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_no` (`contract_no`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_contracts_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. STAFF MOVEMENTS (promotions, transfers, terminations)
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_staff_movements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `movement_no` VARCHAR(30) NOT NULL,
  `staff_id` INT NOT NULL,
  `movement_type` ENUM('Promotion','Transfer','Termination','Demotion','Confirmation') NOT NULL,
  `effective_date` DATE NOT NULL,
  `old_designation` VARCHAR(100) NULL,
  `new_designation` VARCHAR(100) NULL,
  `old_department` VARCHAR(100) NULL,
  `new_department` VARCHAR(100) NULL,
  `old_branch_id` INT NULL,
  `new_branch_id` INT NULL,
  `reason` TEXT NULL,
  `approved_by` INT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `movement_no` (`movement_no`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_staff_movements_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. SALARY GRADES & COMPONENTS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_salary_grades` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `grade_code` VARCHAR(20) NOT NULL,
  `grade_name` VARCHAR(150) NOT NULL,
  `min_salary` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `max_salary` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `branch_id` INT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grade_code_branch` (`grade_code`, `branch_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `hr_salary_grades_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_salary_components` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `component_code` VARCHAR(30) NOT NULL,
  `component_name` VARCHAR(150) NOT NULL,
  `component_type` ENUM('Earning','Deduction') NOT NULL,
  `is_taxable` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `component_code` (`component_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hr_salary_components` (`component_code`, `component_name`, `component_type`, `display_order`) VALUES
('BASIC', 'Basic Salary', 'Earning', 1),
('HOUSE', 'House Allowance', 'Earning', 2),
('TRANSPORT', 'Transport Allowance', 'Earning', 3),
('MEDICAL', 'Medical Allowance', 'Earning', 4),
('OTHER_ALLOW', 'Other Allowances', 'Earning', 5),
('TAX', 'Tax Deduction', 'Deduction', 10),
('PENSION', 'Pension Deduction', 'Deduction', 11),
('LOAN', 'Loan Deduction', 'Deduction', 12),
('ADVANCE', 'Advance Recovery', 'Deduction', 13),
('OTHER_DEDUCT', 'Other Deductions', 'Deduction', 14)
ON DUPLICATE KEY UPDATE `component_name` = VALUES(`component_name`);

-- =============================================
-- 5. PAYROLL RUNS & SALARY PAYMENT EXTENSIONS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_payroll_runs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `run_no` VARCHAR(30) NOT NULL,
  `payment_month` DATE NOT NULL,
  `branch_id` INT NULL,
  `status` ENUM('Draft','Pending_Approval','Approved','Locked','Cancelled') DEFAULT 'Draft',
  `total_staff` INT DEFAULT 0,
  `total_amount` DECIMAL(15,2) DEFAULT 0,
  `remarks` TEXT NULL,
  `processed_by` INT NULL,
  `approved_by` INT NULL,
  `approved_at` DATETIME NULL,
  `locked_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_no` (`run_no`),
  KEY `payment_month` (`payment_month`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `hr_payroll_runs_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `salary_payments`
  ADD COLUMN `payroll_run_id` INT NULL AFTER `staff_id`,
  ADD COLUMN `payment_status` ENUM('Draft','Pending_Approval','Approved','Paid','Cancelled') DEFAULT 'Draft' AFTER `net_salary`,
  ADD COLUMN `component_breakdown` JSON NULL AFTER `payment_status`,
  ADD COLUMN `approved_by` INT NULL AFTER `processed_by`,
  ADD COLUMN `approved_at` DATETIME NULL AFTER `approved_by`;

-- =============================================
-- 6. ADVANCE SALARY & OTHER CHARGES
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_salary_advances` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `advance_no` VARCHAR(30) NOT NULL,
  `staff_id` INT NOT NULL,
  `requested_amount` DECIMAL(15,2) NOT NULL,
  `approved_amount` DECIMAL(15,2) NULL,
  `reason` TEXT NOT NULL,
  `recovery_months` INT DEFAULT 1,
  `monthly_recovery` DECIMAL(15,2) DEFAULT 0,
  `total_recovered` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('Pending','Approved','Rejected','Disbursed','Fully_Recovered') DEFAULT 'Pending',
  `approved_by` INT NULL,
  `approved_at` DATETIME NULL,
  `disbursed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `advance_no` (`advance_no`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_salary_advances_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_other_charges` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `charge_type` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `charge_month` DATE NOT NULL,
  `is_recurring` TINYINT(1) DEFAULT 0,
  `status` ENUM('Pending','Applied','Waived') DEFAULT 'Pending',
  `description` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `charge_month` (`charge_month`),
  CONSTRAINT `hr_other_charges_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. PERFORMANCE REVIEWS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_performance_reviews` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `reviewer_id` INT NULL,
  `review_period` VARCHAR(50) NOT NULL,
  `rating` DECIMAL(3,1) NULL,
  `comments` TEXT NULL,
  `status` ENUM('Draft','Submitted','Acknowledged','Archived') DEFAULT 'Draft',
  `review_date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_performance_reviews_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 8. PERMISSIONS
-- =============================================
INSERT INTO `modules` (`module_key`, `module_name`, `module_description`, `display_order`) VALUES
('hr_grievances', 'HR Grievances', 'Employee complaints and grievance management', 23),
('hr_items', 'HR Item Requests', 'Employee item requisition workflow', 24),
('hr_quotations', 'HR Quotations', 'Quotation and vendor management', 25),
('hr_ppdp', 'PPDP Programs', 'Professional development programs', 26),
('hr_recruitment', 'Recruitment', 'Job vacancies and hiring', 27)
ON DUPLICATE KEY UPDATE `module_name` = VALUES(`module_name`);

INSERT INTO `role_action_permissions` (`role_id`, `module_id`, `action_id`, `granted`)
SELECT r.id, m.id, a.id, 1 FROM `roles` r CROSS JOIN `modules` m CROSS JOIN `actions` a
WHERE r.role_name = 'Super Admin'
AND m.module_key IN ('hr_grievances','hr_items','hr_quotations','hr_ppdp','hr_recruitment')
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

INSERT INTO `role_action_permissions` (`role_id`, `module_id`, `action_id`, `granted`)
SELECT r.id, m.id, a.id, 1 FROM `roles` r CROSS JOIN `modules` m CROSS JOIN `actions` a
WHERE r.role_name = 'Admin'
AND m.module_key IN ('hr_grievances','hr_items','hr_quotations','hr_ppdp','hr_recruitment')
AND a.action_key IN ('view','create','update','approve','export')
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);
