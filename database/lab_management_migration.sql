-- ============================================================
-- LAB MANAGEMENT SYSTEM - Database Migration
-- TacliinHub Education Management Platform
-- Version: 1.0.0
-- ============================================================

-- New Laboratory Roles
INSERT IGNORE INTO `roles` (`role_name`, `role_description`, `is_system_role`) VALUES
('Lab Director',    'Laboratory Director with full lab management access', 0),
('Lab Manager',     'Laboratory Manager for day-to-day operations',         0),
('Lab Technician',  'Laboratory Technician for equipment and inventory',    0),
('Safety Officer',  'Safety Officer for lab safety management',             0),
('Procurement Officer', 'Procurement Officer for purchasing',               0);

-- ============================================================
-- 1. Laboratory Sections
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_sections` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_name`   VARCHAR(150) NOT NULL,
    `section_code`   VARCHAR(50)  NOT NULL UNIQUE,
    `description`    TEXT,
    `supervisor_id`  INT UNSIGNED DEFAULT NULL,
    `capacity`       INT UNSIGNED DEFAULT 30,
    `location`       VARCHAR(200) DEFAULT NULL,
    `status`         ENUM('active','inactive','under_maintenance') NOT NULL DEFAULT 'active',
    `branch_id`      INT UNSIGNED DEFAULT NULL,
    `created_by`     INT UNSIGNED DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_branch` (`branch_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default laboratory sections
INSERT IGNORE INTO `lab_sections` (`section_name`, `section_code`, `description`, `capacity`) VALUES
('Electrical Laboratory',        'LAB-ELEC',  'Electrical systems and circuit experiments',           30),
('Mechanical Laboratory',        'LAB-MECH',  'Mechanical engineering and fabrication',               25),
('Plumbing Laboratory',          'LAB-PLMB',  'Plumbing and pipework practical training',             20),
('Civil Engineering Laboratory', 'LAB-CIVIL', 'Materials testing and structural analysis',            25),
('Electronics Laboratory',       'LAB-ELNT',  'Electronics and microelectronics experiments',         30),
('Computer Laboratory',          'LAB-COMP',  'Computing and software development',                   40),
('Networking Laboratory',        'LAB-NET',   'Network configuration and administration',             30),
('Renewable Energy Laboratory',  'LAB-RNEW',  'Solar, wind, and alternative energy systems',         25),
('Welding Laboratory',           'LAB-WELD',  'Welding and metal joining techniques',                 20),
('Automotive Laboratory',        'LAB-AUTO',  'Vehicle mechanics and diagnostics',                    20),
('Science Laboratory',           'LAB-SCI',   'General science experiments',                          35),
('Chemistry Laboratory',         'LAB-CHEM',  'Chemical analysis and synthesis experiments',          30),
('Physics Laboratory',           'LAB-PHYS',  'Physics and mechanics experiments',                    30),
('Biology Laboratory',           'LAB-BIO',   'Biological and life science experiments',              30);

-- ============================================================
-- 2. Lab Inventory Categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_inventory_categories` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL,
    `category_code` VARCHAR(50)  NOT NULL UNIQUE,
    `description`   TEXT,
    `branch_id`     INT UNSIGNED DEFAULT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `lab_inventory_categories` (`category_name`, `category_code`, `description`) VALUES
('Equipment',        'CAT-EQUIP',  'Laboratory equipment and instruments'),
('Tools',            'CAT-TOOLS',  'Hand tools and measuring instruments'),
('Machines',         'CAT-MACH',   'Heavy machinery and powered equipment'),
('Consumables',      'CAT-CONS',   'Materials consumed during experiments'),
('Safety Materials', 'CAT-SAFE',   'PPE and safety equipment'),
('Spare Parts',      'CAT-SPARE',  'Replacement parts and components'),
('Chemicals',        'CAT-CHEM',   'Chemical reagents and solutions'),
('Glassware',        'CAT-GLASS',  'Beakers, flasks, and other glassware'),
('Electronics',      'CAT-ELEC',   'Electronic components and modules'),
('Software',         'CAT-SOFT',   'Licensed software and digital tools');

-- ============================================================
-- 3. Lab Inventory Items
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_inventory_items` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `item_code`        VARCHAR(100) NOT NULL UNIQUE,
    `barcode`          VARCHAR(200) DEFAULT NULL,
    `item_title`       VARCHAR(255) NOT NULL,
    `category_id`      INT UNSIGNED DEFAULT NULL,
    `section_id`       INT UNSIGNED DEFAULT NULL,
    `description`      TEXT,
    `brand`            VARCHAR(100) DEFAULT NULL,
    `model_number`     VARCHAR(100) DEFAULT NULL,
    `supplier`         VARCHAR(200) DEFAULT NULL,
    `purchase_date`    DATE         DEFAULT NULL,
    `warranty_expiry`  DATE         DEFAULT NULL,
    `warranty_info`    TEXT,
    `quantity`         INT UNSIGNED NOT NULL DEFAULT 1,
    `available_qty`    INT UNSIGNED NOT NULL DEFAULT 1,
    `issued_qty`       INT UNSIGNED NOT NULL DEFAULT 0,
    `damaged_qty`      INT UNSIGNED NOT NULL DEFAULT 0,
    `unit_cost`        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_cost`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status`           ENUM('available','issued','damaged','repaired','lost','expired','under_maintenance') NOT NULL DEFAULT 'available',
    `condition`        ENUM('new','good','fair','poor','condemned') NOT NULL DEFAULT 'new',
    `location`         VARCHAR(200) DEFAULT NULL,
    `notes`            TEXT,
    `branch_id`        INT UNSIGNED DEFAULT NULL,
    `created_by`       INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category`  (`category_id`),
    INDEX `idx_section`   (`section_id`),
    INDEX `idx_status`    (`status`),
    INDEX `idx_branch`    (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Material Requests
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_material_requests` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `request_number`   VARCHAR(50)  NOT NULL UNIQUE,
    `requester_id`     INT UNSIGNED DEFAULT NULL,
    `requester_name`   VARCHAR(200) DEFAULT NULL,
    `requester_type`   ENUM('student','instructor','technician','department','other') NOT NULL DEFAULT 'student',
    `section_id`       INT UNSIGNED DEFAULT NULL,
    `experiment_id`    INT UNSIGNED DEFAULT NULL,
    `purpose`          TEXT,
    `request_date`     DATE         NOT NULL,
    `required_date`    DATE         DEFAULT NULL,
    `status`           ENUM('pending','approved','rejected','issued','returned','closed') NOT NULL DEFAULT 'pending',
    `approved_by`      INT UNSIGNED DEFAULT NULL,
    `approved_at`      DATETIME     DEFAULT NULL,
    `rejection_reason` TEXT,
    `issued_by`        INT UNSIGNED DEFAULT NULL,
    `issued_at`        DATETIME     DEFAULT NULL,
    `returned_at`      DATETIME     DEFAULT NULL,
    `notes`            TEXT,
    `branch_id`        INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_requester` (`requester_id`),
    INDEX `idx_status`    (`status`),
    INDEX `idx_branch`    (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. Material Request Items
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_request_items` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `request_id`         INT UNSIGNED NOT NULL,
    `item_id`            INT UNSIGNED DEFAULT NULL,
    `item_name`          VARCHAR(255) NOT NULL,
    `quantity_requested` INT UNSIGNED NOT NULL DEFAULT 1,
    `quantity_issued`    INT UNSIGNED DEFAULT 0,
    `quantity_returned`  INT UNSIGNED DEFAULT 0,
    `notes`              TEXT,
    INDEX `idx_request` (`request_id`),
    INDEX `idx_item`    (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. Lab Experiments
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_experiments` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `experiment_code`     VARCHAR(100) NOT NULL UNIQUE,
    `experiment_title`    VARCHAR(255) NOT NULL,
    `category`            VARCHAR(100) DEFAULT NULL,
    `section_id`          INT UNSIGNED DEFAULT NULL,
    `description`         TEXT,
    `objectives`          TEXT,
    `instructions`        LONGTEXT,
    `safety_guidelines`   TEXT,
    `required_materials`  TEXT,
    `required_equipment`  TEXT,
    `duration_hours`      DECIMAL(5,2) DEFAULT NULL,
    `difficulty_level`    ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    `instructor_id`       INT UNSIGNED DEFAULT NULL,
    `status`              ENUM('draft','active','inactive') NOT NULL DEFAULT 'draft',
    `branch_id`           INT UNSIGNED DEFAULT NULL,
    `created_by`          INT UNSIGNED DEFAULT NULL,
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_section`   (`section_id`),
    INDEX `idx_branch`    (`branch_id`),
    INDEX `idx_status`    (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. Experiment Sessions (Scheduling)
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_experiment_sessions` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `experiment_id`   INT UNSIGNED NOT NULL,
    `section_id`      INT UNSIGNED DEFAULT NULL,
    `session_date`    DATE         NOT NULL,
    `start_time`      TIME         NOT NULL,
    `end_time`        TIME         NOT NULL,
    `class_group`     VARCHAR(200) DEFAULT NULL,
    `student_count`   INT UNSIGNED DEFAULT 0,
    `instructor_id`   INT UNSIGNED DEFAULT NULL,
    `status`          ENUM('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    `notes`           TEXT,
    `evaluation_notes` TEXT,
    `branch_id`       INT UNSIGNED DEFAULT NULL,
    `created_by`      INT UNSIGNED DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_experiment` (`experiment_id`),
    INDEX `idx_date`       (`session_date`),
    INDEX `idx_branch`     (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. Maintenance Records
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_maintenance_records` (
    `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `maintenance_number`   VARCHAR(50)  NOT NULL UNIQUE,
    `item_id`              INT UNSIGNED DEFAULT NULL,
    `section_id`           INT UNSIGNED DEFAULT NULL,
    `maintenance_type`     ENUM('repair','preventive','inspection','calibration','replacement') NOT NULL DEFAULT 'repair',
    `damage_category`      VARCHAR(100) DEFAULT NULL,
    `severity`             ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `description`          TEXT,
    `responsible_user`     VARCHAR(200) DEFAULT NULL,
    `investigation_notes`  TEXT,
    `assigned_technician`  INT UNSIGNED DEFAULT NULL,
    `service_provider`     VARCHAR(200) DEFAULT NULL,
    `cost`                 DECIMAL(15,2) DEFAULT 0.00,
    `scheduled_date`       DATE         DEFAULT NULL,
    `completed_date`       DATE         DEFAULT NULL,
    `status`               ENUM('reported','assigned','in_progress','completed','closed','cancelled') NOT NULL DEFAULT 'reported',
    `resolution_notes`     TEXT,
    `photos`               TEXT,
    `reported_by`          INT UNSIGNED DEFAULT NULL,
    `branch_id`            INT UNSIGNED DEFAULT NULL,
    `created_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_item`       (`item_id`),
    INDEX `idx_status`     (`status`),
    INDEX `idx_branch`     (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. Issue Types
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_issue_types` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type_name`      VARCHAR(150) NOT NULL,
    `type_code`      VARCHAR(50)  NOT NULL UNIQUE,
    `description`    TEXT,
    `priority_level` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `branch_id`      INT UNSIGNED DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `lab_issue_types` (`type_name`, `type_code`, `description`, `priority_level`) VALUES
('Equipment Failure',   'ISS-EQUIP-FAIL',  'Equipment stopped working or malfunctioning',         'high'),
('Missing Equipment',   'ISS-EQUIP-MISS',  'Equipment cannot be found or is unaccounted for',     'high'),
('Damaged Equipment',   'ISS-EQUIP-DMG',   'Equipment has physical damage',                       'medium'),
('Electrical Fault',    'ISS-ELEC-FAULT',  'Electrical wiring or power supply problem',           'critical'),
('Safety Incident',     'ISS-SAFETY',      'Safety hazard or accident occurred',                  'critical'),
('Material Shortage',   'ISS-MAT-SHORT',   'Consumables or materials running low',                'medium'),
('Calibration Issue',   'ISS-CALIB',       'Equipment needs calibration or adjustment',           'medium'),
('Maintenance Request', 'ISS-MAINT',       'Scheduled or unscheduled maintenance needed',         'low'),
('Network Issue',       'ISS-NETWORK',     'Network connectivity problem in lab',                 'medium'),
('Software Issue',      'ISS-SOFTWARE',    'Software crash, license, or configuration issue',     'medium'),
('Other Issue',         'ISS-OTHER',       'Any other issue not covered above',                   'low');

-- ============================================================
-- 10. Lab Issues
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_issues` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `issue_number`     VARCHAR(50)  NOT NULL UNIQUE,
    `issue_type_id`    INT UNSIGNED DEFAULT NULL,
    `title`            VARCHAR(255) NOT NULL,
    `description`      TEXT,
    `priority`         ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `section_id`       INT UNSIGNED DEFAULT NULL,
    `item_id`          INT UNSIGNED DEFAULT NULL,
    `reported_by`      INT UNSIGNED NOT NULL,
    `assigned_to`      INT UNSIGNED DEFAULT NULL,
    `status`           ENUM('open','in_progress','escalated','resolved','closed') NOT NULL DEFAULT 'open',
    `resolution_notes` TEXT,
    `resolved_at`      DATETIME     DEFAULT NULL,
    `photos`           TEXT,
    `branch_id`        INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type`   (`issue_type_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. Lab Visitors / Guest Log
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_visitors` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `visitor_name`          VARCHAR(200) NOT NULL,
    `visitor_id_number`     VARCHAR(100) DEFAULT NULL,
    `organization`          VARCHAR(200) DEFAULT NULL,
    `contact_number`        VARCHAR(50)  DEFAULT NULL,
    `purpose`               TEXT,
    `host_id`               INT UNSIGNED DEFAULT NULL,
    `host_name`             VARCHAR(200) DEFAULT NULL,
    `section_id`            INT UNSIGNED DEFAULT NULL,
    `visitor_pass`          VARCHAR(100) DEFAULT NULL,
    `entry_time`            DATETIME     DEFAULT NULL,
    `exit_time`             DATETIME     DEFAULT NULL,
    `status`                ENUM('checked_in','checked_out','expected') NOT NULL DEFAULT 'expected',
    `security_approved_by`  INT UNSIGNED DEFAULT NULL,
    `notes`                 TEXT,
    `created_by`            INT UNSIGNED DEFAULT NULL,
    `branch_id`             INT UNSIGNED DEFAULT NULL,
    `created_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status`  (`status`),
    INDEX `idx_branch`  (`branch_id`),
    INDEX `idx_date`    (`entry_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. Safety Incidents
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_safety_incidents` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `incident_number`  VARCHAR(50)  NOT NULL UNIQUE,
    `incident_type`    ENUM('accident','near_miss','safety_inspection','hazard_report') NOT NULL DEFAULT 'accident',
    `incident_date`    DATE         NOT NULL,
    `incident_time`    TIME         DEFAULT NULL,
    `description`      TEXT,
    `section_id`       INT UNSIGNED DEFAULT NULL,
    `location`         VARCHAR(200) DEFAULT NULL,
    `reported_by`      INT UNSIGNED NOT NULL,
    `injured_person`   VARCHAR(200) DEFAULT NULL,
    `severity`         ENUM('minor','moderate','serious','critical') NOT NULL DEFAULT 'minor',
    `treatment_given`  TEXT,
    `corrective_action` TEXT,
    `status`           ENUM('reported','under_investigation','resolved','closed') NOT NULL DEFAULT 'reported',
    `photos`           TEXT,
    `branch_id`        INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type`   (`incident_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. Safety Checklists
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_safety_checklists` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `checklist_name` VARCHAR(200) NOT NULL,
    `section_id`     INT UNSIGNED DEFAULT NULL,
    `inspection_date` DATE        NOT NULL,
    `inspector_id`   INT UNSIGNED DEFAULT NULL,
    `items_checked`  LONGTEXT,
    `overall_status` ENUM('passed','failed','needs_attention') NOT NULL DEFAULT 'passed',
    `remarks`        TEXT,
    `branch_id`      INT UNSIGNED DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_section` (`section_id`),
    INDEX `idx_date`    (`inspection_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. Procurement / Recently Purchased Items
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_procurement` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `purchase_number`  VARCHAR(50)  NOT NULL UNIQUE,
    `supplier_name`    VARCHAR(200) NOT NULL,
    `supplier_contact` VARCHAR(200) DEFAULT NULL,
    `supplier_email`   VARCHAR(200) DEFAULT NULL,
    `item_description` TEXT         NOT NULL,
    `category_id`      INT UNSIGNED DEFAULT NULL,
    `section_id`       INT UNSIGNED DEFAULT NULL,
    `quantity`         INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_price`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `purchase_date`    DATE          NOT NULL,
    `expected_delivery` DATE         DEFAULT NULL,
    `actual_delivery`  DATE          DEFAULT NULL,
    `warranty_period`  INT UNSIGNED DEFAULT NULL COMMENT 'in months',
    `warranty_expiry`  DATE         DEFAULT NULL,
    `invoice_number`   VARCHAR(100) DEFAULT NULL,
    `status`           ENUM('pending','approved','ordered','received','rejected','cancelled') NOT NULL DEFAULT 'pending',
    `approved_by`      INT UNSIGNED DEFAULT NULL,
    `approved_at`      DATETIME     DEFAULT NULL,
    `notes`            TEXT,
    `branch_id`        INT UNSIGNED DEFAULT NULL,
    `created_by`       INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status`  (`status`),
    INDEX `idx_branch`  (`branch_id`),
    INDEX `idx_date`    (`purchase_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. Lab Bookings & Scheduling
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_bookings` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `booking_number`   VARCHAR(50)  NOT NULL UNIQUE,
    `section_id`       INT UNSIGNED NOT NULL,
    `requester_id`     INT UNSIGNED NOT NULL,
    `requester_name`   VARCHAR(200) DEFAULT NULL,
    `purpose`          TEXT,
    `booking_date`     DATE         NOT NULL,
    `start_time`       TIME         NOT NULL,
    `end_time`         TIME         NOT NULL,
    `attendees_count`  INT UNSIGNED DEFAULT 1,
    `experiment_id`    INT UNSIGNED DEFAULT NULL,
    `equipment_needed` TEXT,
    `status`           ENUM('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
    `approved_by`      INT UNSIGNED DEFAULT NULL,
    `approved_at`      DATETIME     DEFAULT NULL,
    `rejection_reason` TEXT,
    `notes`            TEXT,
    `branch_id`        INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_section` (`section_id`),
    INDEX `idx_date`    (`booking_date`),
    INDEX `idx_status`  (`status`),
    INDEX `idx_branch`  (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Permissions for Lab Module
-- ============================================================
INSERT IGNORE INTO `permissions` (`permission_name`, `permission_key`, `module`, `description`) VALUES
('View Laboratory',           'laboratory.view',              'laboratory', 'View laboratory module'),
('Manage Lab Sections',       'laboratory.manage_sections',   'laboratory', 'Create, edit, delete laboratory sections'),
('Manage Lab Inventory',      'laboratory.manage_inventory',  'laboratory', 'Manage laboratory inventory items'),
('Manage Material Requests',  'laboratory.manage_requests',   'laboratory', 'Manage material requests'),
('Approve Material Requests', 'laboratory.approve_requests',  'laboratory', 'Approve or reject material requests'),
('Manage Experiments',        'laboratory.manage_experiments','laboratory', 'Manage practical experiments'),
('Manage Maintenance',        'laboratory.manage_maintenance','laboratory', 'Log and manage maintenance records'),
('Manage Issues',             'laboratory.manage_issues',     'laboratory', 'Report and manage laboratory issues'),
('Manage Visitors',           'laboratory.manage_visitors',   'laboratory', 'Register and manage laboratory visitors'),
('Manage Safety',             'laboratory.manage_safety',     'laboratory', 'Manage safety incidents and checklists'),
('Manage Procurement',        'laboratory.manage_procurement','laboratory', 'Manage procurement and purchases'),
('Manage Bookings',           'laboratory.manage_bookings',   'laboratory', 'Create and approve lab bookings'),
('View Lab Reports',          'laboratory.view_reports',      'laboratory', 'View laboratory reports'),
('Export Lab Reports',        'laboratory.export_reports',    'laboratory', 'Export laboratory reports');
