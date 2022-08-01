ALTER TABLE `lime_participants` ADD `mrn_id` VARCHAR(100) NULL AFTER `lastname`;
ALTER TABLE `lime_participants` ADD `active` VARCHAR(1) NOT NULL DEFAULT 'Y' AFTER `mrn_id`;