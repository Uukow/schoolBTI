-- TacliinHub HR Phase 1 Foundation Migration
-- Attendance rules, holidays, leave balances, staff extensions, HR audit logs

-- =============================================
-- 1. STAFF TABLE EXTENSIONS
-- =============================================
ALTER TABLE `staff`
  ADD COLUMN `reports_to` INT NULL AFTER `branch_id`,
  ADD COLUMN `national_id` VARCHAR(50) NULL AFTER `emergency_phone`,
  ADD COLUMN `probation_end_date` DATE NULL AFTER `joining_date`,
  ADD COLUMN `confirmation_date` DATE NULL AFTER `probation_end_date`;

-- =============================================
-- 2. STAFF ATTENDANCE EXTENSIONS
-- =============================================
ALTER TABLE `staff_attendance`
  ADD COLUMN `late_minutes` INT DEFAULT 0 AFTER `status`,
  ADD COLUMN `early_departure_minutes` INT DEFAULT 0 AFTER `late_minutes`,
  ADD COLUMN `overtime_minutes` INT DEFAULT 0 AFTER `early_departure_minutes`,
  ADD COLUMN `attendance_source` ENUM('Manual','Biometric','QR','Mobile','Import') DEFAULT 'Manual' AFTER `overtime_minutes`,
  ADD COLUMN `is_approved` TINYINT(1) DEFAULT 1 AFTER `attendance_source`,
  ADD COLUMN `approved_by` INT NULL AFTER `is_approved`,
  ADD COLUMN `deleted_at` DATETIME NULL AFTER `created_at`,
  ADD COLUMN `deleted_by` INT NULL AFTER `deleted_at`,
  ADD COLUMN `deletion_reason` TEXT NULL AFTER `deleted_by`;

-- =============================================
-- 3. LEAVE APPLICATION EXTENSIONS (multi-level approval)
-- =============================================
ALTER TABLE `leave_applications`
  ADD COLUMN `approval_stage` ENUM('Pending','Manager_Approved','Approved','Rejected','Cancelled') DEFAULT 'Pending' AFTER `status`,
  ADD COLUMN `manager_approved_by` INT NULL AFTER `approved_by`,
  ADD COLUMN `manager_approval_date` DATETIME NULL AFTER `manager_approved_by`;

-- Sync existing records
UPDATE `leave_applications` SET `approval_stage` = `status` WHERE `approval_stage` = 'Pending' AND `status` != 'Pending';

-- =============================================
-- 4. HR ATTENDANCE RULES
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_attendance_rules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(150) NOT NULL,
  `branch_id` INT NULL COMMENT 'NULL = global default',
  `work_start_time` TIME NOT NULL DEFAULT '08:00:00',
  `work_end_time` TIME NOT NULL DEFAULT '17:00:00',
  `break_minutes` INT DEFAULT 60,
  `grace_period_minutes` INT DEFAULT 15,
  `half_day_threshold_hours` DECIMAL(4,2) DEFAULT 4.00,
  `overtime_threshold_minutes` INT DEFAULT 30,
  `weekend_days` VARCHAR(20) DEFAULT '5,6' COMMENT '0=Sun, 6=Sat',
  `auto_mark_absent_after` TIME NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `hr_attendance_rules_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_attendance_rules_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. HR SHIFTS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_shifts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `shift_name` VARCHAR(100) NOT NULL,
  `branch_id` INT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `hr_shifts_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_staff_shifts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `shift_id` INT NOT NULL,
  `effective_from` DATE NOT NULL,
  `effective_to` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `shift_id` (`shift_id`),
  CONSTRAINT `hr_staff_shifts_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_staff_shifts_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `hr_shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. HR HOLIDAYS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_holidays` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `holiday_name` VARCHAR(150) NOT NULL,
  `holiday_date` DATE NOT NULL,
  `branch_id` INT NULL COMMENT 'NULL = all branches',
  `holiday_type` ENUM('Public','Institutional','Optional') DEFAULT 'Public',
  `is_recurring` TINYINT(1) DEFAULT 0,
  `description` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `holiday_date` (`holiday_date`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `hr_holidays_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_holidays_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. HR LEAVE POLICIES & BALANCES
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_leave_policies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `leave_type_id` INT NOT NULL,
  `branch_id` INT NULL,
  `days_per_year` INT NOT NULL DEFAULT 0,
  `carry_forward_max` INT DEFAULT 0,
  `max_consecutive_days` INT NULL,
  `requires_manager_approval` TINYINT(1) DEFAULT 1,
  `requires_hr_approval` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_type_branch` (`leave_type_id`, `branch_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `hr_leave_policies_ibfk_1` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_leave_policies_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_leave_balances` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `leave_type_id` INT NOT NULL,
  `year` YEAR NOT NULL,
  `allocated_days` DECIMAL(5,1) NOT NULL DEFAULT 0,
  `used_days` DECIMAL(5,1) NOT NULL DEFAULT 0,
  `carried_forward` DECIMAL(5,1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_leave_year` (`staff_id`, `leave_type_id`, `year`),
  KEY `leave_type_id` (`leave_type_id`),
  CONSTRAINT `hr_leave_balances_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_leave_balances_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 8. HR ATTENDANCE CORRECTIONS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_attendance_corrections` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `attendance_id` INT NULL,
  `requested_check_in` TIME NULL,
  `requested_check_out` TIME NULL,
  `requested_status` ENUM('Present','Absent','Late','Half Day','Leave') NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('Submitted','Manager_Approved','HR_Approved','Rejected') DEFAULT 'Submitted',
  `submitted_by` INT NOT NULL,
  `manager_approved_by` INT NULL,
  `manager_approved_at` DATETIME NULL,
  `hr_approved_by` INT NULL,
  `hr_approved_at` DATETIME NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `status` (`status`),
  CONSTRAINT `hr_attendance_corrections_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 9. HR AUDIT LOGS
-- =============================================
CREATE TABLE IF NOT EXISTS `hr_audit_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entity_type_id` (`entity_type`, `entity_id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `hr_audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 10. HR GRANULAR MODULE PERMISSIONS
-- =============================================
INSERT INTO `modules` (`module_key`, `module_name`, `module_description`, `display_order`) VALUES
('hr_attendance', 'HR Attendance', 'Staff attendance rules, shifts, and corrections', 19),
('hr_payroll', 'HR Payroll', 'Payroll processing and salary management', 20),
('hr_leave', 'HR Leave', 'Leave policies, balances, and approvals', 21),
('hr_reports', 'HR Reports', 'HR analytics and report exports', 22)
ON DUPLICATE KEY UPDATE `module_name` = VALUES(`module_name`);

-- Grant Super Admin all new module permissions
INSERT INTO `role_action_permissions` (`role_id`, `module_id`, `action_id`, `granted`)
SELECT r.id, m.id, a.id, 1
FROM `roles` r
CROSS JOIN `modules` m
CROSS JOIN `actions` a
WHERE r.role_name = 'Super Admin'
AND m.module_key IN ('hr_attendance', 'hr_payroll', 'hr_leave', 'hr_reports')
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

-- Grant Admin role view/create/update/approve on HR sub-modules
INSERT INTO `role_action_permissions` (`role_id`, `module_id`, `action_id`, `granted`)
SELECT r.id, m.id, a.id, 1
FROM `roles` r
CROSS JOIN `modules` m
CROSS JOIN `actions` a
WHERE r.role_name = 'Admin'
AND m.module_key IN ('hr_attendance', 'hr_payroll', 'hr_leave', 'hr_reports')
AND a.action_key IN ('view', 'create', 'update', 'approve', 'export', 'print')
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

-- =============================================
-- 11. SEED DEFAULT ATTENDANCE RULE
-- =============================================
INSERT INTO `hr_attendance_rules` (`rule_name`, `branch_id`, `work_start_time`, `work_end_time`, `grace_period_minutes`, `is_active`)
SELECT 'Default Working Hours', NULL, '08:00:00', '17:00:00', 15, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `hr_attendance_rules` WHERE `rule_name` = 'Default Working Hours' AND `branch_id` IS NULL);

-- Seed leave policies from existing leave_types
INSERT INTO `hr_leave_policies` (`leave_type_id`, `branch_id`, `days_per_year`, `carry_forward_max`, `requires_manager_approval`, `requires_hr_approval`)
SELECT lt.id, NULL, COALESCE(lt.days_allowed, 0), 0, 1, 1
FROM `leave_types` lt
WHERE NOT EXISTS (
  SELECT 1 FROM `hr_leave_policies` p WHERE p.leave_type_id = lt.id AND p.branch_id IS NULL
);

-- =============================================
-- 12. INDEXES
-- =============================================
CREATE INDEX IF NOT EXISTS `idx_staff_attendance_date` ON `staff_attendance` (`attendance_date`, `staff_id`);
CREATE INDEX IF NOT EXISTS `idx_hr_holidays_date_branch` ON `hr_holidays` (`holiday_date`, `branch_id`);
CREATE INDEX IF NOT EXISTS `idx_hr_leave_balances_staff` ON `hr_leave_balances` (`staff_id`, `year`);
