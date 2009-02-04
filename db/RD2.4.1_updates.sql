-- add override column to course_aliases table
ALTER TABLE `course_aliases` ADD `override_feed` TINYINT( 1 ) NOT NULL DEFAULT '0';