-- =============================================
-- COMPREHENSIVE SETTINGS TABLE MIGRATION
-- =============================================
-- Author: AI Assistant
-- Date: 2025-12-20
-- Description: Extended settings table for centralized configuration management
-- =============================================

-- Add new columns to system_settings table
-- Note: Run this migration carefully. If columns already exist, you may need to modify the ALTER statements.
ALTER TABLE `system_settings`
-- System Identity
ADD COLUMN `system_short_name` VARCHAR(50) DEFAULT NULL AFTER `school_name`,
ADD COLUMN IF NOT EXISTS `system_logo` VARCHAR(255) DEFAULT NULL AFTER `system_short_name`,
ADD COLUMN IF NOT EXISTS `system_favicon` VARCHAR(255) DEFAULT NULL AFTER `system_logo`,
ADD COLUMN IF NOT EXISTS `developer_name` VARCHAR(255) DEFAULT 'Uukow Technology Solutions (UTech)' AFTER `school_address`,
ADD COLUMN IF NOT EXISTS `license_text` TEXT DEFAULT NULL AFTER `developer_name`,

-- Academic Settings
ADD COLUMN IF NOT EXISTS `grading_system` ENUM('Percentage','Letter','GPA','Points') DEFAULT 'Percentage' AFTER `current_session`,
ADD COLUMN IF NOT EXISTS `gpa_scale` DECIMAL(3,2) DEFAULT 4.00 AFTER `grading_system`,
ADD COLUMN IF NOT EXISTS `attendance_threshold` INT DEFAULT 75 AFTER `gpa_scale`,
ADD COLUMN IF NOT EXISTS `class_graduation_enabled` TINYINT(1) DEFAULT 1 AFTER `attendance_threshold`,

-- Financial Settings
ADD COLUMN IF NOT EXISTS `tuition_fee_behavior` ENUM('Monthly','Termly','Yearly','Custom') DEFAULT 'Monthly' AFTER `currency_symbol`,
ADD COLUMN IF NOT EXISTS `discount_enabled` TINYINT(1) DEFAULT 1 AFTER `tuition_fee_behavior`,
ADD COLUMN IF NOT EXISTS `penalty_enabled` TINYINT(1) DEFAULT 1 AFTER `discount_enabled`,
ADD COLUMN IF NOT EXISTS `penalty_rate` DECIMAL(5,2) DEFAULT 0.00 AFTER `penalty_enabled`,
ADD COLUMN IF NOT EXISTS `payroll_enabled` TINYINT(1) DEFAULT 1 AFTER `penalty_rate`,
ADD COLUMN IF NOT EXISTS `tax_enabled` TINYINT(1) DEFAULT 0 AFTER `payroll_enabled`,
ADD COLUMN IF NOT EXISTS `tax_rate` DECIMAL(5,2) DEFAULT 0.00 AFTER `tax_enabled`,

-- User & Role Settings
ADD COLUMN IF NOT EXISTS `default_role_id` INT(11) DEFAULT NULL AFTER `two_factor_enabled`,
ADD COLUMN IF NOT EXISTS `role_permissions_enabled` TINYINT(1) DEFAULT 1 AFTER `default_role_id`,

-- Communication Settings
ADD COLUMN IF NOT EXISTS `email_enabled` TINYINT(1) DEFAULT 1 AFTER `smtp_encryption`,
ADD COLUMN IF NOT EXISTS `sms_enabled` TINYINT(1) DEFAULT 0 AFTER `whatsapp_api_key`,
ADD COLUMN IF NOT EXISTS `whatsapp_enabled` TINYINT(1) DEFAULT 0 AFTER `sms_enabled`,
ADD COLUMN IF NOT EXISTS `notification_enabled` TINYINT(1) DEFAULT 1 AFTER `whatsapp_enabled`,
ADD COLUMN IF NOT EXISTS `notification_email` TINYINT(1) DEFAULT 1 AFTER `notification_enabled`,
ADD COLUMN IF NOT EXISTS `notification_sms` TINYINT(1) DEFAULT 0 AFTER `notification_email`,
ADD COLUMN IF NOT EXISTS `notification_whatsapp` TINYINT(1) DEFAULT 0 AFTER `notification_sms`,

-- Security Settings
ADD COLUMN IF NOT EXISTS `session_timeout` INT DEFAULT 3600 AFTER `two_factor_enabled`,
ADD COLUMN IF NOT EXISTS `password_min_length` INT DEFAULT 8 AFTER `session_timeout`,
ADD COLUMN IF NOT EXISTS `password_require_uppercase` TINYINT(1) DEFAULT 0 AFTER `password_min_length`,
ADD COLUMN IF NOT EXISTS `password_require_lowercase` TINYINT(1) DEFAULT 1 AFTER `password_require_uppercase`,
ADD COLUMN IF NOT EXISTS `password_require_number` TINYINT(1) DEFAULT 1 AFTER `password_require_lowercase`,
ADD COLUMN IF NOT EXISTS `password_require_special` TINYINT(1) DEFAULT 0 AFTER `password_require_number`,
ADD COLUMN IF NOT EXISTS `max_login_attempts` INT DEFAULT 5 AFTER `password_require_special`,
ADD COLUMN IF NOT EXISTS `account_lockout_time` INT DEFAULT 1800 AFTER `max_login_attempts`,
ADD COLUMN IF NOT EXISTS `audit_logging_enabled` TINYINT(1) DEFAULT 1 AFTER `account_lockout_time`,

-- UI/UX Settings
ADD COLUMN IF NOT EXISTS `theme` VARCHAR(50) DEFAULT 'default' AFTER `language`,
ADD COLUMN IF NOT EXISTS `time_format` VARCHAR(20) DEFAULT 'H:i:s' AFTER `date_format`,
ADD COLUMN IF NOT EXISTS `datetime_format` VARCHAR(30) DEFAULT 'd-m-Y H:i:s' AFTER `time_format`,
ADD COLUMN IF NOT EXISTS `pagination_limit` INT DEFAULT 25 AFTER `datetime_format`,
ADD COLUMN IF NOT EXISTS `records_per_page` INT DEFAULT 25 AFTER `pagination_limit`,

-- Integration Settings
ADD COLUMN IF NOT EXISTS `api_enabled` TINYINT(1) DEFAULT 0 AFTER `payment_api_key`,
ADD COLUMN IF NOT EXISTS `api_key` VARCHAR(255) DEFAULT NULL AFTER `api_enabled`,
ADD COLUMN IF NOT EXISTS `webhook_enabled` TINYINT(1) DEFAULT 0 AFTER `api_key`,
ADD COLUMN IF NOT EXISTS `webhook_url` VARCHAR(500) DEFAULT NULL AFTER `webhook_enabled`,
ADD COLUMN IF NOT EXISTS `license_verification_enabled` TINYINT(1) DEFAULT 0 AFTER `webhook_url`,
ADD COLUMN IF NOT EXISTS `license_verification_endpoint` VARCHAR(500) DEFAULT NULL AFTER `license_verification_enabled`,
ADD COLUMN IF NOT EXISTS `license_key` VARCHAR(255) DEFAULT NULL AFTER `license_verification_endpoint`,

-- Feature Toggles
ADD COLUMN IF NOT EXISTS `feature_lms` TINYINT(1) DEFAULT 1 AFTER `license_key`,
ADD COLUMN IF NOT EXISTS `feature_library` TINYINT(1) DEFAULT 1 AFTER `feature_lms`,
ADD COLUMN IF NOT EXISTS `feature_transport` TINYINT(1) DEFAULT 1 AFTER `feature_library`,
ADD COLUMN IF NOT EXISTS `feature_hostel` TINYINT(1) DEFAULT 0 AFTER `feature_transport`,
ADD COLUMN IF NOT EXISTS `feature_certificates` TINYINT(1) DEFAULT 1 AFTER `feature_hostel`,
ADD COLUMN IF NOT EXISTS `feature_events` TINYINT(1) DEFAULT 1 AFTER `feature_certificates`,

-- Metadata
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
ADD COLUMN IF NOT EXISTS `updated_by` INT(11) DEFAULT NULL AFTER `updated_at`;

-- Create settings_audit_log table for tracking changes
CREATE TABLE IF NOT EXISTS `settings_audit_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `changed_by` INT(11) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `setting_key` (`setting_key`),
  KEY `changed_by` (`changed_by`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `settings_audit_log_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create settings_cache table for performance
CREATE TABLE IF NOT EXISTS `settings_cache` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cache_key` VARCHAR(100) NOT NULL UNIQUE,
  `cache_value` LONGTEXT NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `cache_key` (`cache_key`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

