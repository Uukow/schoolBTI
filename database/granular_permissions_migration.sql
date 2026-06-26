-- =============================================
-- GRANULAR PERMISSIONS SYSTEM MIGRATION
-- =============================================
-- Author: School ERP Development Team
-- Date: 2025-12-XX
-- Description: Implements action-based granular permissions system
-- =============================================

-- =============================================
-- 1. MODULES TABLE
-- =============================================
-- Stores all system modules (Students, Fees, Exams, etc.)
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_key` varchar(50) NOT NULL COMMENT 'Unique identifier (e.g., students, fees, exams)',
  `module_name` varchar(100) NOT NULL COMMENT 'Display name (e.g., Students Management)',
  `module_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0 COMMENT 'Order for display in admin interface',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_key` (`module_key`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. ACTIONS TABLE
-- =============================================
-- Stores all possible actions (Create, View, Update, Delete, Approve, Export, etc.)
CREATE TABLE IF NOT EXISTS `actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_key` varchar(50) NOT NULL COMMENT 'Unique identifier (e.g., create, view, update, delete)',
  `action_name` varchar(100) NOT NULL COMMENT 'Display name (e.g., Create, View, Update)',
  `action_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0 COMMENT 'Order for display in admin interface',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_key` (`action_key`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. ROLE_ACTION_PERMISSIONS TABLE
-- =============================================
-- Many-to-Many relationship: Roles can have multiple module-action permissions
-- This is the core table for role-based permissions
CREATE TABLE IF NOT EXISTS `role_action_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `granted` tinyint(1) DEFAULT 1 COMMENT '1 = granted, 0 = denied (for explicit denial)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_module_action_unique` (`role_id`, `module_id`, `action_id`),
  KEY `role_id` (`role_id`),
  KEY `module_id` (`module_id`),
  KEY `action_id` (`action_id`),
  CONSTRAINT `role_action_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_action_permissions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_action_permissions_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. USER_ACTION_OVERRIDES TABLE
-- =============================================
-- Allows individual users to have custom permission overrides
-- This enables fine-grained control: same role, different permissions per user
CREATE TABLE IF NOT EXISTS `user_action_overrides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `granted` tinyint(1) DEFAULT 1 COMMENT '1 = granted, 0 = denied',
  `override_type` enum('grant','deny') DEFAULT 'grant' COMMENT 'Type of override',
  `created_by` int(11) DEFAULT NULL COMMENT 'Admin who created this override',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_module_action_unique` (`user_id`, `module_id`, `action_id`),
  KEY `user_id` (`user_id`),
  KEY `module_id` (`module_id`),
  KEY `action_id` (`action_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `user_action_overrides_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_action_overrides_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_action_overrides_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_action_overrides_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. PERMISSION_AUDIT_LOG TABLE
-- =============================================
-- Comprehensive audit trail for all permission changes
CREATE TABLE IF NOT EXISTS `permission_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who made the change',
  `target_type` enum('role','user') NOT NULL COMMENT 'What was changed: role or user',
  `target_id` int(11) NOT NULL COMMENT 'ID of role or user that was modified',
  `module_id` int(11) DEFAULT NULL,
  `action_id` int(11) DEFAULT NULL,
  `change_type` enum('grant','revoke','override_grant','override_revoke','override_remove') NOT NULL,
  `old_value` text DEFAULT NULL COMMENT 'Previous permission state (JSON)',
  `new_value` text DEFAULT NULL COMMENT 'New permission state (JSON)',
  `description` text DEFAULT NULL COMMENT 'Human-readable description of change',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `target_type_target_id` (`target_type`, `target_id`),
  KEY `module_id` (`module_id`),
  KEY `action_id` (`action_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `permission_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permission_audit_log_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permission_audit_log_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. INSERT DEFAULT MODULES
-- =============================================
INSERT INTO `modules` (`module_key`, `module_name`, `module_description`, `display_order`) VALUES
('dashboard', 'Dashboard', 'Main dashboard and analytics', 1),
('students', 'Students', 'Student information management', 2),
('admissions', 'Admissions', 'Admission and enrollment management', 3),
('academics', 'Academics', 'Classes, subjects, timetable, and academic management', 4),
('attendance', 'Attendance', 'Student and staff attendance tracking', 5),
('exams', 'Examinations', 'Exam scheduling, marks entry, and results', 6),
('fees', 'Fees & Finance', 'Fee management, payments, and financial operations', 7),
('library', 'Library', 'Library and book management', 8),
('transport', 'Transport', 'Transport and vehicle management', 9),
('hostel', 'Hostel', 'Hostel and accommodation management', 10),
('hr', 'HR & Payroll', 'Staff management, payroll, and HR operations', 11),
('lms', 'Learning Management', 'Study materials, assignments, and quizzes', 12),
('communication', 'Communication', 'SMS, email, announcements, and messaging', 13),
('events', 'Events & Calendar', 'Events, calendar, and academic calendar management', 14),
('certificates', 'Certificates', 'Certificate and document generation', 15),
('reports', 'Reports & Analytics', 'Reports, analytics, and data exports', 16),
('settings', 'Settings', 'System settings and configuration', 17),
('branches', 'Branches', 'Multi-branch management', 18)
ON DUPLICATE KEY UPDATE `module_name` = VALUES(`module_name`);

-- =============================================
-- 7. INSERT DEFAULT ACTIONS
-- =============================================
INSERT INTO `actions` (`action_key`, `action_name`, `action_description`, `display_order`) VALUES
('create', 'Create', 'Create new records', 1),
('view', 'View', 'View and read records', 2),
('update', 'Update', 'Edit and modify existing records', 3),
('delete', 'Delete', 'Delete records', 4),
('approve', 'Approve', 'Approve requests and applications', 5),
('reject', 'Reject', 'Reject requests and applications', 6),
('export', 'Export', 'Export data to files (PDF, Excel, CSV)', 7),
('print', 'Print', 'Print documents and reports', 8),
('import', 'Import', 'Import data from files', 9),
('manage', 'Manage', 'Full management access (all actions)', 10)
ON DUPLICATE KEY UPDATE `action_name` = VALUES(`action_name`);

-- =============================================
-- 8. GRANT DEFAULT PERMISSIONS TO SUPER ADMIN
-- =============================================
-- Super Admin gets all permissions for all modules
INSERT INTO `role_action_permissions` (`role_id`, `module_id`, `action_id`, `granted`)
SELECT 
    r.id as role_id,
    m.id as module_id,
    a.id as action_id,
    1 as granted
FROM `roles` r
CROSS JOIN `modules` m
CROSS JOIN `actions` a
WHERE r.role_name = 'Super Admin'
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

-- =============================================
-- 9. CREATE INDEXES FOR PERFORMANCE
-- =============================================
-- Additional indexes for common query patterns
CREATE INDEX IF NOT EXISTS `idx_role_permissions_lookup` ON `role_action_permissions` (`role_id`, `module_id`, `action_id`, `granted`);
CREATE INDEX IF NOT EXISTS `idx_user_overrides_lookup` ON `user_action_overrides` (`user_id`, `module_id`, `action_id`, `granted`);
CREATE INDEX IF NOT EXISTS `idx_audit_log_search` ON `permission_audit_log` (`target_type`, `target_id`, `created_at`);

-- =============================================
-- END OF MIGRATION
-- =============================================

