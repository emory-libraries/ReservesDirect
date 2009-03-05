-- add override column to course_aliases table
ALTER TABLE `course_aliases` ADD `override_feed` TINYINT( 1 ) NOT NULL DEFAULT '0';

-- default libary_id in departments table to 1 previously 0
ALTER TABLE `departments` CHANGE `library_id` `library_id` INT( 11 ) NOT NULL DEFAULT '1';

-- new requests status codes
ALTER TABLE `reserves` CHANGE `status` `status` SET('ACTIVE','INACTIVE','IN PROCESS','DENIED','SEARCHING STACKS','UNAVAILABLE','RECALLED','PURCHASING','RESPONSE NEEDED','SCANNING','COPYRIGHT REVIEW') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ACTIVE'