-- Phase 5: Holiday date ranges (start + end date)
ALTER TABLE `hr_holidays` ADD COLUMN `end_date` DATE NULL AFTER `holiday_date`;

UPDATE `hr_holidays` SET `end_date` = `holiday_date` WHERE `end_date` IS NULL;

CREATE INDEX `idx_hr_holidays_date_range` ON `hr_holidays` (`holiday_date`, `end_date`, `branch_id`);
