--
-- Modify access table to add enrollment status
--
ALTER TABLE `access` ADD `enrollment_status` set('AUTOFEED','APPROVED','PENDING','DENIED') NOT NULL DEFAULT 'PENDING'

--
-- Set all current access records to approved
--
UPDATE `access` SET `enrollment_status` = 'APPROVED'

--
-- Modify enrollment field in course instances
--
ALTER TABLE `course_instances` CHANGE `enrollment` `enrollment` SET('OPEN', 'MODERATED', 'CLOSED') NOT NULL DEFAULT 'OPEN'

--
-- Set all current classes to PUBLIC enrollment
--
UPDATE `course_instances` SET `enrollment` = 'OPEN'


-- ------------------------------------------

-- 
-- Create copyright tables
--

CREATE TABLE `copyright` (
  `copyright_id` bigint(20) unsigned NOT NULL auto_increment,
  `item_id` bigint(20) unsigned default NULL,
  `status` set('NEW','PENDING','APPROVED','DENIED') NOT NULL default 'NEW',
  `status_basis_id` bigint(20) unsigned default NULL,
  `contact_id` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`copyright_id`),
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `copyright_contacts` (
  `contact_id` bigint(20) unsigned NOT NULL auto_increment,
  `org_name` varchar(100) default NULL,
  `contact_name` varchar(100) default NULL,
  `address` varchar(255) default NULL,
  `phone` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `www` varchar(255) default NULL,
  PRIMARY KEY  (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `copyright_files` (
  `copyright_file_id` bigint(20) unsigned NOT NULL auto_increment,
  `copyright_id` bigint(20) unsigned default NULL,
  `item_id` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`copyright_file_id`),
  KEY `copyright_id` (`copyright_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Links supporting copyright files to a copyright record';


CREATE TABLE `copyright_log` (
  `copyright_log_id` bigint(20) unsigned NOT NULL auto_increment,
  `copyright_id` bigint(20) unsigned default NULL,
  `tstamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(11) unsigned default NULL,
  `action` varchar(20) default NULL,
  `details` varchar(100) default NULL,
  PRIMARY KEY  (`copyright_log_id`),
  KEY `user_id` (`user_id`,`action`),
  KEY `copyright_id` (`copyright_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `copyright_status_bases` (
  `status_basis_id` bigint(20) unsigned NOT NULL auto_increment,
  `status_type` set('APPROVED','DENIED') NOT NULL default 'APPROVED',
  `status_basis` varchar(100) default NULL,
  PRIMARY KEY  (`status_basis_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- -------------------------------------------------------

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
