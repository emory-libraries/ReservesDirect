--
-- Add external_user_key to users
--
ALTER TABLE `users` ADD `external_user_key` VARCHAR( 50 ) NULL COMMENT 'Can be used to link users to external systems' AFTER `email` ;
ALTER TABLE `users` ADD UNIQUE (`external_user_key`);


--
-- Add max_enrollment to requests
--
ALTER TABLE `requests` ADD `max_enrollment` INT NULL COMMENT 'max enrollment as specified by instructor' AFTER `course_instance_id`;
ALTER TABLE `requests` ADD `type` SET( 'PHYSICAL', 'SCAN' ) NOT NULL DEFAULT 'PHYSICAL';

--
-- update reserves and items status to enable grainular copyright control
--
ALTER TABLE `reserves` CHANGE `status` `status` SET( 'ACTIVE', 'INACTIVE', 'IN PROCESS', 'DENIED' )
  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ACTIVE';
  
ALTER TABLE `items` ADD `status` SET( 'ACTIVE', 'DENIED' ) NOT NULL DEFAULT 'ACTIVE' AFTER `OCLC` ;  


--
-- add new status to reserves to allow tracking of request processing
--
ALTER TABLE `reserves` CHANGE `status` `status` SET( 'ACTIVE', 'INACTIVE', 'IN PROCESS', 'DENIED', 'RUSH', 'PULLED', 'CHECKED OUT',  'RECALLED', 'PURCHASING', 'REQUESTED', 'AWAITING REVIEW')
  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ACTIVE';