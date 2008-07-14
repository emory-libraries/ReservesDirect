ALTER TABLE `users` ADD `external_user_key` VARCHAR( 50 ) NULL COMMENT 'Can be used to link users to external systems' AFTER `email` ;
ALTER TABLE `users` ADD UNIQUE (`external_user_key`);