--
-- create_db.sql
-- Creates table structures and inserts seed data for initial database
-- creation.
--
-- Created by Chris Roddy (croddy@emory.edu)
--
-- This file is part of ReservesDirect
--
-- Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.
-- 
-- ReservesDirect is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
-- 
-- ReservesDirect is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
-- 
-- You should have received a copy of the GNU General Public License
-- along with ReservesDirect; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
-- 
-- ReservesDirect is located at:
-- http://www.reservesdirect.org
-- 
-- -------------------------------------------------------

-- 
-- Host: localhost
-- Generation Time: Jan 06, 2006 at 10:51 AM
-- Server version: 4.1.16
-- PHP Version: 5.1.1
-- 
-- Database: `reserves`
-- 
-- --------------------------------------------------------

-- 
-- Table structure for table `access`
-- 

CREATE TABLE `access` (
  `access_id` int(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `alias_id` int(20) NOT NULL default '0',
  `permission_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`access_id`),
  KEY `users_id` (`user_id`),
  KEY `alias_id` (`alias_id`),
  KEY `permission_level` (`permission_level`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `circ_rules`
-- 

CREATE TABLE `circ_rules` (
  `id` int(11) NOT NULL auto_increment,
  `circ_rule` varchar(50) NOT NULL default '',
  `alt_circ_rule` varchar(50) NOT NULL default '',
  `default_selected` set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `course_aliases`
-- 

CREATE TABLE `course_aliases` (
  `course_alias_id` bigint(20) NOT NULL auto_increment,
  `course_id` int(11) NOT NULL default '0',
  `course_instance_id` bigint(20) NOT NULL default '0',
  `section` varchar(8) default NULL,
  PRIMARY KEY  (`course_alias_id`),
  KEY `course_id` (`course_id`),
  KEY `course_instance_id` (`course_instance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `course_instances`
-- 

CREATE TABLE `course_instances` (
  `course_instance_id` bigint(20) NOT NULL auto_increment,
  `primary_course_alias_id` bigint(20) NOT NULL default '0',
  `term` varchar(12) NOT NULL default '',
  `year` int(11) NOT NULL default '0',
  `activation_date` date NOT NULL default '0000-00-00',
  `expiration_date` date NOT NULL default '0000-00-00',
  `status` set('ACTIVE','INACTIVE','IN PROGRESS') NOT NULL default '',
  `enrollment` varchar(12) NOT NULL default '',
  PRIMARY KEY  (`course_instance_id`),
  KEY `primary_course_alias_id` (`primary_course_alias_id`),
  KEY `term_year_idx` (`term`,`year`),
  KEY `status` (`status`),
  KEY `enrollment` (`enrollment`),
  KEY `ci_date_range_idx` (`activation_date`,`expiration_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `courses`
-- 

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL auto_increment,
  `department_id` int(11) NOT NULL default '0',
  `course_number` varchar(10) default NULL,
  `course_name` text,
  `uniform_title` enum('t','f') NOT NULL default 't',
  `old_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`course_id`),
  KEY `department_id` (`department_id`),
  KEY `uniform_title` (`uniform_title`),
  KEY `old_id` (`old_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `courses_no_dept`
-- 

CREATE TABLE `courses_no_dept` (
  `course_id` int(11) NOT NULL auto_increment,
  `department_id` int(11) NOT NULL default '0',
  `course_number` varchar(10) default NULL,
  `course_name` text,
  `uniform_title` enum('t','f') NOT NULL default 't',
  `old_id` int(11) NOT NULL default '0',
  `dept_abv` varchar(50) NOT NULL default '',
  `old_course_num` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`course_id`),
  KEY `department_id` (`department_id`),
  KEY `uniform_title` (`uniform_title`),
  KEY `old_id` (`old_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `departments`
-- 

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL auto_increment,
  `abbreviation` varchar(8) default NULL,
  `name` text,
  `library_id` int(11) NOT NULL default '0',
  `status` int(5) default NULL,
  KEY `library_id` (`library_id`),
  KEY `deptid` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `electronic_item_audit`
-- 

CREATE TABLE `electronic_item_audit` (
  `audit_id` int(20) NOT NULL auto_increment,
  `item_id` bigint(20) NOT NULL default '0',
  `date_added` date NOT NULL default '0000-00-00',
  `added_by` int(11) NOT NULL default '0',
  `date_reviewed` date default NULL,
  `reviewed_by` int(11) default NULL,
  PRIMARY KEY  (`audit_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `hidden_readings`
-- 

CREATE TABLE `hidden_readings` (
  `hidden_id` int(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `reserve_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`hidden_id`),
  UNIQUE KEY `unique_constraint` (`user_id`,`reserve_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `inst_loan_periods`
-- 

CREATE TABLE `inst_loan_periods` (
  `loan_period_id` bigint(20) NOT NULL auto_increment,
  `loan_period` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`loan_period_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `inst_loan_periods_libraries`
-- 

CREATE TABLE `inst_loan_periods_libraries` (
  `id` bigint(20) NOT NULL auto_increment,
  `library_id` bigint(20) NOT NULL default '0',
  `loan_period_id` bigint(20) NOT NULL default '0',
  `default` set('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `instructor_attributes`
-- 

CREATE TABLE `instructor_attributes` (
  `instructor_attribute_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `ils_user_id` varchar(50) default NULL,
  `ils_name` varchar(75) default NULL,
  `organizational_status` varchar(25) default NULL,
  PRIMARY KEY  (`instructor_attribute_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `item_upload_log`
-- 

CREATE TABLE `item_upload_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `course_instance_id` bigint(20) NOT NULL default '0',
  `item_id` bigint(20) NOT NULL default '0',
  `timestamp_uploaded` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `filesize` varchar(10) NOT NULL default '',
  `ipaddr` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `items`
-- 

CREATE TABLE `items` (
  `item_id` bigint(20) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `author` varchar(255) default NULL,
  `source` varchar(255) default NULL,
  `volume_title` varchar(255) default NULL,
  `content_notes` varchar(255) default NULL,
  `volume_edition` varchar(255) default NULL,
  `pages_times` varchar(255) default NULL,
  `performer` varchar(255) default NULL,
  `local_control_key` varchar(30) default NULL,
  `creation_date` date NOT NULL default '0000-00-00',
  `last_modified` date NOT NULL default '0000-00-00',
  `url` text,
  `mimetype` varchar(100) default 'text/html',
  `home_library` int(11) NOT NULL default '0',
  `private_user_id` int(11) default NULL,
  `item_group` varchar(25) NOT NULL default '0',
  `item_type` set('ITEM','HEADING') NOT NULL default 'ITEM',
  `item_icon` varchar(255) default NULL,
  `old_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`item_id`),
  KEY `private_user_id` (`private_user_id`),
  KEY `home_library` (`home_library`),
  KEY `mimetype` (`mimetype`),
  KEY `item_group` (`item_group`),
  KEY `old_id` (`old_id`),
  KEY `controlKey` (`local_control_key`),
  FULLTEXT KEY `fulltext_title` (`title`),
  FULLTEXT KEY `fulltext_source` (`source`),
  FULLTEXT KEY `fulltext_content_notes` (`content_notes`),
  FULLTEXT KEY `fulltext_volume_edition` (`volume_edition`),
  FULLTEXT KEY `fulltext_pages_times` (`pages_times`),
  FULLTEXT KEY `fulltext_performer` (`performer`),
  FULLTEXT KEY `fulltext_url` (`url`),
  FULLTEXT KEY `fulltext_author` (`author`),
  FULLTEXT KEY `fulltext_volume_title` (`volume_title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `libraries`
-- 

CREATE TABLE `libraries` (
  `library_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `nickname` varchar(15) NOT NULL default '',
  `ils_prefix` varchar(10) NOT NULL default '',
  `reserve_desk` varchar(50) NOT NULL default '',
  `url` text,
  `contact_email` varchar(255) default NULL,
  `monograph_library_id` int(11) NOT NULL default '0',
  `multimedia_library_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`library_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `mimetypes`
-- 

CREATE TABLE `mimetypes` (
  `mimetype_id` int(11) NOT NULL auto_increment,
  `mimetype` varchar(100) NOT NULL default '',
  `helper_app_url` text,
  `helper_app_name` text,
  `helper_app_icon` text,
  `file_extentions` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`mimetype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `not_trained`
-- 

CREATE TABLE `not_trained` (
  `user_id` int(11) NOT NULL default '0',
  `permission_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `notes`
-- 

CREATE TABLE `notes` (
  `note_id` bigint(20) NOT NULL auto_increment,
  `type` varchar(25) NOT NULL default '',
  `target_id` bigint(20) NOT NULL default '0',
  `note` text NOT NULL,
  `target_table` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`note_id`),
  KEY `type` (`type`),
  KEY `target` (`target_table`,`target_id`),
  FULLTEXT KEY `fulltext_note` (`note`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `permissions_levels`
-- 

CREATE TABLE `permissions_levels` (
  `permission_id` int(11) NOT NULL default '0',
  `label` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `physical_copies`
-- 

CREATE TABLE `physical_copies` (
  `physical_copy_id` int(11) NOT NULL auto_increment,
  `reserve_id` bigint(20) NOT NULL default '0',
  `item_id` bigint(20) NOT NULL default '0',
  `status` varchar(30) NOT NULL default '',
  `call_number` text,
  `barcode` varchar(15) default NULL,
  `owning_library` varchar(15) NOT NULL default '0',
  `item_type` varchar(30) default NULL,
  `owner_user_id` int(11) default NULL,
  PRIMARY KEY  (`physical_copy_id`),
  KEY `reserves_id` (`reserve_id`),
  KEY `item_id` (`item_id`),
  KEY `status` (`status`),
  KEY `barcode` (`barcode`),
  KEY `item_type` (`item_type`),
  KEY `owner_user_id` (`owner_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `reports`
-- 

CREATE TABLE `reports` (
  `report_id` bigint(20) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `param_group` set('term','department','class','term_lib') default NULL,
  `sql` text NOT NULL,
  `parameters` varchar(255) default NULL,
  `min_permissions` int(11) NOT NULL default '4',
  `sort_order` int(11) NOT NULL default '0',
  `cached` tinyint(1) NOT NULL default '1' comment 'boolean: 1 of 0',
  `cache_refresh_delay` int(4) NOT NULL default '6' comment 'measured in hours',
  PRIMARY KEY  (`report_id`),
  KEY `min_permissions` (`min_permissions`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `reports_cache`
-- 

CREATE TABLE `reports_cache` (
  `report_cache_id` bigint(20) NOT NULL auto_increment,
  `report_id` bigint(20) default NULL comment 'foreign key -- `reports`',
  `params_cache` text,
  `report_cache` text,
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`report_cache_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `requests`
-- 

CREATE TABLE `requests` (
  `request_id` bigint(20) NOT NULL auto_increment,
  `reserve_id` int(11) NOT NULL default '0',
  `item_id` bigint(20) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date_requested` date NOT NULL default '0000-00-00',
  `date_processed` date default NULL,
  `date_desired` date default NULL,
  `priority` int(11) default NULL,
  `course_instance_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`request_id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  KEY `date_requested` (`date_requested`),
  KEY `date_desired` (`date_desired`),
  KEY `priority` (`priority`),
  KEY `course_instance_id` (`course_instance_id`),
  KEY `reserve_id` (`reserve_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `reserves`
-- 

CREATE TABLE `reserves` (
  `reserve_id` bigint(20) NOT NULL auto_increment,
  `course_instance_id` bigint(20) NOT NULL default '0',
  `item_id` bigint(20) NOT NULL default '0',
  `activation_date` date NOT NULL default '0000-00-00',
  `expiration` date NOT NULL default '0000-00-00',
  `status` set('ACTIVE','INACTIVE','IN PROCESS') NOT NULL default 'ACTIVE',
  `sort_order` int(11) NOT NULL default '0',
  `date_created` date NOT NULL default '0000-00-00',
  `last_modified` date NOT NULL default '0000-00-00',
  `requested_loan_period` varchar(255) default NULL,
  `parent_id` bigint(20) default NULL,
  PRIMARY KEY  (`reserve_id`),
  UNIQUE KEY `unique_constraint` (`course_instance_id`,`item_id`),
  KEY `reserves_sort_ci_idx` (`course_instance_id`,`sort_order`),
  KEY `item_id` (`item_id`),
  KEY `reserves_date_range_idx` (`activation_date`,`expiration`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `reserves_old`
-- 

CREATE TABLE `reserves_old` (
  `reserve_id` bigint(20) NOT NULL auto_increment,
  `course_instance_id` bigint(20) NOT NULL default '0',
  `item_id` bigint(20) NOT NULL default '0',
  `activation_date` date NOT NULL default '0000-00-00',
  `expiration` date NOT NULL default '0000-00-00',
  `status` set('ACTIVE','INACTIVE','IN PROCESS') NOT NULL default 'ACTIVE',
  `sort_order` int(11) NOT NULL default '0',
  `date_created` date NOT NULL default '0000-00-00',
  `last_modified` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`reserve_id`),
  KEY `reserves_sort_ci_idx` (`course_instance_id`,`sort_order`),
  KEY `item_id` (`item_id`),
  KEY `reserves_date_range_idx` (`activation_date`,`expiration`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `skins`
-- 

CREATE TABLE `skins` (
  `id` int(11) NOT NULL auto_increment,
  `skin_name` varchar(20) NOT NULL default '',
  `skin_stylesheet` text NOT NULL,
  `default_selected` set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `skin_name` (`skin_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `special_users`
-- 

CREATE TABLE `special_users` (
  `user_id` int(11) NOT NULL default '0',
  `password` varchar(75) NOT NULL default '',
  `expiration` date default NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `special_users_audit`
-- 

CREATE TABLE `special_users_audit` (
  `user_id` bigint(20) NOT NULL default '0',
  `creator_user_id` bigint(20) NOT NULL default '0',
  `date_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `email_sent_to` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `staff_libraries`
-- 

CREATE TABLE `staff_libraries` (
  `staff_library_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `library_id` int(11) NOT NULL default '0',
  `permission_level_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`staff_library_id`),
  KEY `user_id` (`user_id`),
  KEY `library_id` (`library_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `terms`
-- 

CREATE TABLE `terms` (
  `term_id` int(11) NOT NULL auto_increment,
  `sort_order` int(11) NOT NULL default '0',
  `term_name` varchar(100) NOT NULL default '',
  `term_year` varchar(4) NOT NULL default '',
  `begin_date` date NOT NULL default '0000-00-00',
  `end_date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`term_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_view_log`
-- 

CREATE TABLE `user_view_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `reserve_id` bigint(20) NOT NULL default '0',
  `timestamp_viewed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `reserve_id` (`reserve_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `first_name` varchar(50) default NULL,
  `last_name` varchar(50) default NULL,
  `email` varchar(75) default NULL,
  `dflt_permission_level` int(11) NOT NULL default '0',
  `last_login` date NOT NULL default '0000-00-00',
  `old_id` int(11) default NULL,
  `old_user_id` int(11) default NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `max_permission_level` (`dflt_permission_level`),
  KEY `old_id` (`old_id`),
  KEY `old_user_id` (`old_user_id`),
  FULLTEXT KEY `last_name` (`last_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Seed data for table `users`: admin
--

INSERT INTO users (username, first_name, last_name, dflt_permission_level) VALUES ('admin', 'ReservesDirect', 'Administrator', 5);

--
-- Seed data for table `special_users`: admin
--

INSERT INTO special_users VALUES (1, '21232f297a57a5a743894a0e4a801fc3', NULL);

--
-- Seed data for table `skins`
--

INSERT INTO `skins` VALUES (1, 'general', 'css/ReservesStyles.css', 'yes');

-- 
-- Dumping data for table `mimetypes`
-- 

INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('application/pdf', 'http://www.adobe.com/products/acrobat/readstep2.html', 'Adobe Acrobat Reader', 'images/doc_type_icons/doctype-pdf.gif ', 'pdf');
INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('audio/x-pn-realaudio', 'http://www.real.com/', 'RealPlayer', 'images/doc_type_icons/doctype-sound.gif', 'ram');
INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('video/quicktime', 'http://www.apple.com/quicktime/', 'Quicktime Player', 'images/doc_type_icons/doctype-movie.gif', 'mov');
INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('application/msword', 'http://office.microsoft.com/Assistance/9798/viewerscvt.aspx', 'Microsoft Word', 'images/doc_type_icons/doctype-text.gif', 'doc');
INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('application/vnd.ms-excel', 'http://office.microsoft.com/Assistance/9798/viewerscvt.aspx', 'Microsoft Excel', 'images/doc_type_icons/doctype-text.gif', 'xcl');
INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('application/vnd.ms-powerpoint', 'http://office.microsoft.com/Assistance/9798/viewerscvt.aspx', 'Microsoft Powerpoint', 'images/doc_type_icons/doctype-text.gif', 'ppt');
INSERT INTO `mimetypes` (`mimetype`, `helper_app_url`, `helper_app_name`, `helper_app_icon`, `file_extentions`) VALUES ('text/html', NULL, NULL, 'images/doc_type_icons/doctype-clear.gif', '');

-- 
-- Dumping data for table `permissions_levels`
-- 

INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES (0, 'student');
INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES (1, 'custodian');
INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES (2, 'proxy');
INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES (3, 'instructor');
INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES (4, 'staff');
INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES (5, 'admin');

-- 
-- Dumping data for table `terms`
-- 

INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (1, 'FALL', '2004', '2004-08-26', '2004-12-18');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (2, 'SPRING', '2005', '2005-01-01', '2005-05-31');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (3, 'SUMMER', '2005', '2005-05-15', '2005-08-16');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (4, 'FALL', '2005', '2005-08-15', '2005-12-31');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (5, 'SPRING', '2006', '2006-01-01', '2006-05-16');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (6, 'SUMMER', '2006', '2006-05-17', '2006-08-16');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (7, 'FALL', '2006', '2006-08-17', '2006-12-31');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (8, 'SPRING', '2007', '2007-01-01', '2007-05-16');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (9, 'SUMMER', '2007', '2007-05-17', '2007-08-16');
INSERT INTO `terms` (`sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES (10, 'FALL', '2007', '2007-08-17', '2007-12-31');

-- 
-- Dumping data for table `reports`
-- 

INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Items Added by Role', 'term', 'SELECT concat( t.term_name, '' '', t.term_year ) AS ''Term'', pl.label AS ''Role'', count( distinct eia.item_id ) AS ''Items Added''\r\nFROM electronic_item_audit AS eia\r\nJOIN reserves AS r ON r.item_id = eia.item_id\r\nJOIN course_instances AS ci ON ci.course_instance_id = r.course_instance_id\r\nJOIN terms AS t ON ci.term = t.term_name\r\nAND ci.year = t.term_year\r\nJOIN users AS u ON eia.added_by = u.user_id\r\nJOIN permissions_levels AS pl ON u.dflt_permission_level = pl.permission_id\r\nWHERE t.term_id IN (!)\r\nGROUP BY Term, Role', 'term_id', 4, 2, 0, 0);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Totals: Items And Reserves', NULL, 'SELECT \r\n	i.item_group AS ''Item Type'',\r\n	COUNT(DISTINCT i.item_id) AS ''Total Items'',\r\n	COUNT(DISTINCT r.reserve_id) AS ''Total Reserves''\r\nFROM reserves AS r\r\n	JOIN items AS i ON i.item_id = r.item_id\r\nGROUP BY i.item_group', NULL, 4, 0, 1, 6);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Totals: Courses', NULL, 'SELECT\r\n	COUNT(DISTINCT primary_course_alias_id) AS ''Total Number of Courses''\r\nFROM course_instances', NULL, 4, 0, 1, 6);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Totals: Users', NULL, 'SELECT\r\n	pl.label AS ''User Role'',\r\n	COUNT(DISTINCT u.user_id) AS ''User Count''\r\nFROM users AS u \r\n	JOIN permissions_levels AS pl ON pl.permission_id = u.dflt_permission_level\r\nGROUP BY pl.label', NULL, 4, 0, 1, 6);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Item View Log for a class', 'class', 'SELECT\r\n	i.title as ''Title'',\r\n	COUNT(uvl.user_id) as ''Total Hits'',\r\n	COUNT(DISTINCT uvl.user_id) as ''Unique Hits''\r\nFROM course_instances AS ci\r\n	JOIN reserves AS r ON r.course_instance_id = ci.course_instance_id\r\n	JOIN items AS i ON i.item_id = r.item_id\r\n	LEFT JOIN user_view_log AS uvl ON uvl.reserve_id = r.reserve_id\r\nWHERE ci.course_instance_id = !\r\nGROUP BY r.reserve_id\r\nORDER BY Title', 'ci', 3, 2, 0, 0);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Global: Courses And Users', 'term_lib', 'SELECT\r\n	CONCAT(t.term_name, '' '', t.term_year) as Term,\r\n	l.name AS Library,\r\n	d.name AS Department,\r\n	COUNT(DISTINCT ci.course_instance_id) AS Courses,\r\n	COUNT(DISTINCT a_i.user_id) AS Instructors,\r\n	COUNT(DISTINCT a_p.user_id) AS Proxies,\r\n	COUNT(DISTINCT a_s.user_id) AS Students\r\nFROM terms AS t\r\n	JOIN course_instances AS ci ON (ci.term = t.term_name AND ci.year = t.term_year)\r\n	JOIN course_aliases AS ca ON ca.course_alias_id = ci.primary_course_alias_id\r\n	LEFT JOIN access AS a_i ON a_i.alias_id = ca.course_alias_id AND a_i.permission_level = 3\r\n	LEFT JOIN access AS a_p ON a_p.alias_id = ca.course_alias_id AND a_p.permission_level = 2\r\n	LEFT JOIN access AS a_s ON a_s.alias_id = ca.course_alias_id AND a_s.permission_level = 0\r\n	JOIN courses AS c ON ca.course_id = c.course_id\r\n	JOIN departments AS d ON d.department_id = c.department_id\r\n	JOIN libraries AS l ON l.library_id = d.library_id\r\nWHERE t.term_id IN (!)\r\n	AND l.library_id IN (!)\r\n	AND d.status IS NULL	\r\nGROUP BY\r\n	t.term_year,\r\n	t.term_name,\r\n	l.library_id,\r\n	d.department_id\r\nORDER BY\r\n	t.term_year,\r\n	t.term_name,\r\n	l.name,\r\n	d.name	', 'term_id,library_id', 4, 1, 1, 6);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Global: Items And Reserves', 'term_lib', 'SELECT\r\n	CONCAT(t.term_name, '' '', t.term_year) as Term,\r\n	l.name AS Library,\r\n	d.name AS Department,\r\n	i.item_group AS ''Item Type'',\r\n	COUNT(DISTINCT r.item_id) AS ''Utilized Items'',\r\n	COUNT(DISTINCT r.reserve_id) AS ''Available Reserves'',\r\n	COUNT(DISTINCT uvl.reserve_id) AS ''Opened Reserves''\r\nFROM terms AS t\r\n	JOIN course_instances AS ci ON (ci.term = t.term_name AND ci.year = t.term_year)\r\n	JOIN reserves AS r ON r.course_instance_id = ci.course_instance_id\r\n	JOIN items AS i ON i.item_id = r.item_id\r\n	LEFT JOIN user_view_log AS uvl ON uvl.reserve_id = r.reserve_id\r\n	\r\n	JOIN course_aliases AS ca ON ca.course_alias_id = ci.primary_course_alias_id\r\n	JOIN courses AS c ON c.course_id = ca.course_id\r\n	JOIN departments AS d ON d.department_id = c.department_id\r\n	JOIN libraries AS l ON l.library_id = d.library_id\r\nWHERE t.term_id IN (!)\r\n	AND l.library_id IN (!)\r\n	AND d.status IS NULL\r\nGROUP BY\r\n	t.term_year,\r\n	t.term_name,\r\n	l.library_id,\r\n	d.department_id,\r\n	i.item_group\r\nORDER BY\r\n	t.term_year,\r\n	t.term_name,\r\n	l.name,\r\n	d.name,\r\n	i.item_group', 'term_id,library_id', 4, 1, 1, 6);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Global: Upload Activity', 'term', 'SELECT\r\n	CONCAT(t.term_name, '' '', t.term_year) as Term,\r\n	CONCAT(u.last_name, '', '', u.first_name) AS User,\r\n	pl.label AS Role,		\r\n	COUNT(DISTINCT aud.item_id) AS ''Items Added''\r\nFROM terms AS t\r\n	JOIN electronic_item_audit AS aud ON (aud.date_added BETWEEN t.begin_date AND t.end_date)\r\n	JOIN users AS u ON u.user_id = aud.added_by\r\n	JOIN permissions_levels AS pl ON pl.permission_id = u.dflt_permission_level\r\nWHERE t.term_id IN (!)\r\nGROUP BY\r\n	t.term_year,\r\n	t.term_name,\r\n	u.user_id\r\nORDER BY\r\n	t.term_year,\r\n	t.term_name,\r\n	''Items Added'' DESC', 'term_id', 4, 1, 0, 0);
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES ('Item View Log for a class by date', 'class', 'SELECT\r\n	i.title as ''Title'',\r\n	DATE(uvl.timestamp_viewed) AS ''Date'',\r\n	COUNT(uvl.user_id) as ''Total Hits'',\r\n	COUNT(DISTINCT uvl.user_id) as ''Unique Hits''\r\nFROM course_instances AS ci\r\n	JOIN reserves AS r ON r.course_instance_id = ci.course_instance_id\r\n	JOIN items AS i ON i.item_id = r.item_id\r\n	JOIN user_view_log AS uvl ON uvl.reserve_id = r.reserve_id\r\nWHERE ci.course_instance_id = !\r\nGROUP BY r.reserve_id, ''Date''\r\nORDER BY Title, ''Date''', 'ci', 5, 2, 0, 0);