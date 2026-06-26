-- Public vendor quotation portal fields
ALTER TABLE `hr_quotations` ADD COLUMN `is_public` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `hr_quotations` ADD COLUMN `public_token` VARCHAR(64) NULL;
ALTER TABLE `hr_quotations` ADD COLUMN `public_deadline` DATE NULL;
ALTER TABLE `hr_quotations` ADD COLUMN `published_at` DATETIME NULL;
ALTER TABLE `hr_quotations` ADD UNIQUE KEY `hr_quotations_public_token` (`public_token`);
