-- RFQ line items
CREATE TABLE IF NOT EXISTS `hr_quotation_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quotation_id` INT NOT NULL,
  `line_no` INT NOT NULL DEFAULT 1,
  `item_name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `quantity` DECIMAL(12,2) NOT NULL DEFAULT 1,
  `unit` VARCHAR(50) DEFAULT 'pcs',
  `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `line_total` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  CONSTRAINT `hr_quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `hr_quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
