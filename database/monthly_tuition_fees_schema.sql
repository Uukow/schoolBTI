-- Monthly Tuition Fee Management Schema
-- This schema extends the existing fee system to support monthly tuition fees
-- with flexible payments, advance credits, and comprehensive ledger tracking

-- Monthly Fee Assignments
-- Tracks monthly fee assignments for each student
CREATE TABLE IF NOT EXISTS `monthly_fee_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `month` varchar(20) NOT NULL COMMENT 'Format: YYYY-MM',
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Assigned','Paid','Partially Paid','Overdue','Waived') DEFAULT 'Assigned',
  `assigned_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL COMMENT 'Reference to fee_invoices if invoice generated',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`student_id`, `fee_type_id`, `month`, `session_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `fee_type_id` (`fee_type_id`),
  KEY `session_id` (`session_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `month` (`month`),
  KEY `status` (`status`),
  CONSTRAINT `monthly_fee_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `monthly_fee_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `monthly_fee_assignments_ibfk_3` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  CONSTRAINT `monthly_fee_assignments_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  CONSTRAINT `monthly_fee_assignments_ibfk_5` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `monthly_fee_assignments_ibfk_6` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Fee Ledger
-- Comprehensive ledger tracking all fee transactions for each student
CREATE TABLE IF NOT EXISTS `student_fee_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `transaction_type` enum('Assignment','Payment','Advance Credit','Adjustment','Refund') NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID of related record (assignment_id, payment_id, etc.)',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'Type: monthly_assignment, payment, advance, etc.',
  `month` varchar(20) DEFAULT NULL COMMENT 'For monthly fees: YYYY-MM',
  `debit_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount charged/assigned',
  `credit_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount paid/credited',
  `balance` decimal(10,2) NOT NULL COMMENT 'Running balance after this transaction',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `session_id` (`session_id`),
  KEY `month` (`month`),
  KEY `transaction_type` (`transaction_type`),
  KEY `reference` (`reference_id`, `reference_type`),
  CONSTRAINT `student_fee_ledger_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fee_ledger_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Advance Credits
-- Tracks advance payments that can be applied to future months
CREATE TABLE IF NOT EXISTS `student_advance_credits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL COMMENT 'Reference to fee_payments if from payment',
  `amount` decimal(10,2) NOT NULL,
  `allocated_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount already allocated to fees',
  `available_amount` decimal(10,2) NOT NULL COMMENT 'Amount still available for allocation',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `session_id` (`session_id`),
  KEY `payment_id` (`payment_id`),
  CONSTRAINT `student_advance_credits_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_advance_credits_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  CONSTRAINT `student_advance_credits_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Allocations
-- Tracks how payments are allocated across multiple fee assignments
CREATE TABLE IF NOT EXISTS `payment_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `allocation_type` enum('Monthly Assignment','Advance Credit','Invoice') NOT NULL,
  `reference_id` int(11) NOT NULL COMMENT 'ID of monthly_assignment, advance_credit, or invoice',
  `amount` decimal(10,2) NOT NULL,
  `allocated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  KEY `reference` (`allocation_type`, `reference_id`),
  CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Fee Balance Summary
-- Quick reference table for current balances (updated via triggers or application logic)
CREATE TABLE IF NOT EXISTS `student_fee_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `total_assigned` decimal(10,2) DEFAULT 0.00,
  `total_paid` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT 0.00,
  `advance_credit` decimal(10,2) DEFAULT 0.00,
  `overdue_amount` decimal(10,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_balance` (`student_id`, `session_id`),
  KEY `student_id` (`student_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `student_fee_balance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fee_balance_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

