--
-- Modify access table to add enrollment status
--
ALTER TABLE `access` ADD `enrollment_status` set('AUTOFEED','APPROVED','PENDING','DENIED') NOT NULL DEFAULT 'PENDING';

--
-- Set all current access records to approved
--
UPDATE `access` SET `enrollment_status` = 'APPROVED'

--
-- Set all current classes to PUBLIC enrollment
--
UPDATE `course_instances` SET `enrollment` = 'PUBLIC'


-- ------------------------------------------

-- 
-- Create copyright tables
--

CREATE TABLE `copyright` (
`copyright_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` BIGINT( 20 ) UNSIGNED NULL COMMENT 'foreign key -- `items`',
`status` SET( 'NEW', 'PENDING', 'APPROVED', 'DENIED' ) NOT NULL DEFAULT 'NEW',
`status_basis_id` BIGINT( 20 ) UNSIGNED NULL COMMENT 'foreign key -- `copyright_status_bases`' ,
`contact_id` BIGINT( 20 ) UNSIGNED NULL COMMENT 'foreign key -- `copyright_contacts`' ,
UNIQUE (
`item_id`
)
) TYPE = MYISAM ;


CREATE TABLE `copyright_contacts` (
`contact_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`org_name` VARCHAR( 100 ) NULL ,
`contact_name` VARCHAR( 100 ) NULL ,
`address` VARCHAR( 255 ) NULL ,
`phone` VARCHAR( 100 ) NULL ,
`email` VARCHAR( 100 ) NULL ,
`www` VARCHAR( 255 ) NULL 
) TYPE = MYISAM ;


CREATE TABLE `copyright_status_bases` (
`status_basis_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`status_type` SET( 'APPROVED', 'DENIED' ) NOT NULL DEFAULT 'APPROVED',
`status_basis` VARCHAR( 100 ) NULL
) TYPE = MYISAM ;


CREATE TABLE `copyright_files` (
`copyright_file_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`copyright_id` BIGINT( 20 ) UNSIGNED NULL COMMENT 'foreign key -- `copyright`',
`item_id` BIGINT( 20 ) UNSIGNED NULL COMMENT 'foreign key -- `items`',
INDEX ( `copyright_id` , `item_id` )
) TYPE = MYISAM COMMENT = 'Links supporting copyright files to a copyright record';


CREATE TABLE `copyright_log` (
`copyright_log_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`copyright_id` BIGINT( 20 ) UNSIGNED NULL COMMENT 'foreign key -- `copyright`' ,
`tstamp` DATETIME NOT NULL ,
`user_id` INT( 11 ) UNSIGNED NULL COMMENT 'foreign key -- `users`' ,
`action` VARCHAR( 20 ) NULL ,
`details` VARCHAR( 100 ) NULL ,
INDEX ( `copyright_id` , `user_id` , `action` )
) TYPE = MYISAM ;


--
-- Add copyright lib id to libraries record
--
ALTER TABLE `libraries` ADD `copyright_library_id` INT( 11 ) NULL ;

--
-- Set default copyright library to monograph library
--
UPDATE `libraries` SET `copyright_library_id` = `monograph_library_id`

--
-- Notes updates
--
UPDATE `notes` SET `type` = 'Content' WHERE `type` = 'content'
UPDATE `notes` SET `type` = 'Instructor' WHERE `type` = 'annotation'

--
-- Drop unnecessary fields from elec item audit
--
ALTER TABLE `electronic_item_audit` DROP `reviewed_by` 
ALTER TABLE `electronic_item_audit` DROP `date_reviewed` 
