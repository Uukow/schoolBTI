-- TacliinHub HR Phase 4 Migration
-- Career portal, QR attendance, biometric, performance enhancements

CREATE TABLE IF NOT EXISTS `hr_qr_sessions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `session_token` VARCHAR(64) NOT NULL,
  `branch_id` INT NULL,
  `location_name` VARCHAR(150) NULL,
  `valid_date` DATE NOT NULL,
  `valid_from` TIME NULL,
  `valid_until` TIME NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `valid_date` (`valid_date`),
  CONSTRAINT `hr_qr_sessions_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_biometric_devices` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `device_code` VARCHAR(50) NOT NULL,
  `device_name` VARCHAR(150) NOT NULL,
  `branch_id` INT NULL,
  `api_key` VARCHAR(64) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_sync_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_code` (`device_code`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `branch_id` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_staff_biometric_map` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `staff_id` INT NOT NULL,
  `biometric_id` VARCHAR(50) NOT NULL,
  `device_id` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bio_device` (`biometric_id`, `device_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `hr_staff_biometric_map_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_offer_letters` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `application_id` INT NOT NULL,
  `offered_salary` DECIMAL(15,2) NOT NULL,
  `start_date` DATE NOT NULL,
  `offer_date` DATE NOT NULL,
  `expiry_date` DATE NULL,
  `status` ENUM('Draft','Sent','Accepted','Declined','Expired') DEFAULT 'Draft',
  `letter_path` VARCHAR(500) NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`),
  CONSTRAINT `hr_offer_letters_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `hr_job_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_talent_pool` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `application_id` INT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `cv_path` VARCHAR(500) NULL,
  `skills` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
