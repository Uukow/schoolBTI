-- Store explicit requester name on material requests (students/staff without user accounts)
ALTER TABLE `lab_material_requests`
    MODIFY `requester_id` INT UNSIGNED NULL,
    ADD COLUMN `requester_name` VARCHAR(200) NULL AFTER `requester_id`;

-- Backfill from linked user accounts
UPDATE `lab_material_requests` r
LEFT JOIN `users` u ON r.requester_id = u.id
SET r.requester_name = u.username
WHERE r.requester_name IS NULL AND u.id IS NOT NULL;
