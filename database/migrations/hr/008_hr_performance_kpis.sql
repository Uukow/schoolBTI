-- Performance review enhancements (KPIs, goals)
ALTER TABLE `hr_performance_reviews` ADD COLUMN `kpis` JSON NULL;
ALTER TABLE `hr_performance_reviews` ADD COLUMN `goals` TEXT NULL;
ALTER TABLE `hr_performance_reviews` ADD COLUMN `strengths` TEXT NULL;
ALTER TABLE `hr_performance_reviews` ADD COLUMN `improvements` TEXT NULL;
