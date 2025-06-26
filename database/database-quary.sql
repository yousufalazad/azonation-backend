-- 26/06/2025 2 column added
ALTER TABLE `assets` ADD `start_date` DATE NULL AFTER `description`, ADD `end_date` DATE NULL AFTER `start_date`;

-- 2 new column added in office documents
ALTER TABLE `office_documents` ADD `date` DATE NULL AFTER `description`;

-- 26/06/2025 3 column added
ALTER TABLE `users` ADD `first_name` VARCHAR(50) NULL AFTER `type`, ADD `last_name` VARCHAR(50) NULL AFTER `first_name`, ADD `org_name` VARCHAR(100) NULL AFTER `last_name`; 