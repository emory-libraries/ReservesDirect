--
-- Modify course_aliases table
-- add classnbr field, 
-- remove unique key on registrar_key, 
-- add unique key combo on registrar_key and classnbr
-- These changes were done to accommodate for the new GROUP.CSV format.
--
ALTER TABLE `course_aliases` ADD `classnbr` INTEGER NULL COMMENT 'Class Number unique within term' AFTER `override_feed` ;
ALTER TABLE `course_aliases` DROP KEY `registrar_key`;
ALTER TABLE `course_aliases` ADD UNIQUE `unique_course_instance` ( `registrar_key`, `classnbr` );

