ALTER TABLE `lime_participants` CHANGE `firstname` `firstname` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `lime_participants` CHANGE `lastname` `lastname` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `lime_participants` ADD UNIQUE `namedob` (`firstname`,`lastname`,`dob`) USING BTREE;