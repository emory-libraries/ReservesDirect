-- MySQL dump 10.9
--
-- Host: localhost    Database: reserves
-- ------------------------------------------------------
-- Server version	4.1.16-standard-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access`
--

DROP TABLE IF EXISTS `access`;
CREATE TABLE `access` (
  `access_id` int(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `alias_id` int(20) NOT NULL default '0',
  `permission_level` int(11) NOT NULL default '0',
  `enrollment_status` set('AUTOFEED','APPROVED','PENDING','DENIED') NOT NULL default 'PENDING',
  `autofeed_run_indicator` varchar(20) default NULL,
  PRIMARY KEY  (`access_id`),
  UNIQUE KEY `user_ca` (`user_id`,`alias_id`),
  KEY `alias_id` (`alias_id`),
  KEY `permission_level` (`permission_level`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `circ_rules`
--

DROP TABLE IF EXISTS `circ_rules`;
CREATE TABLE `circ_rules` (
  `id` int(11) NOT NULL auto_increment,
  `circ_rule` varchar(50) NOT NULL default '',
  `alt_circ_rule` varchar(50) NOT NULL default '',
  `default_selected` set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `course_aliases`
--

DROP TABLE IF EXISTS `course_aliases`;
CREATE TABLE `course_aliases` (
  `course_alias_id` bigint(20) NOT NULL auto_increment,
  `course_id` int(11) default NULL,
  `course_instance_id` bigint(20) default NULL,
  `course_name` text,
  `section` varchar(8) default NULL,
  `registrar_key` varchar(255) default NULL,
  PRIMARY KEY  (`course_alias_id`),
  UNIQUE KEY `registrar_key` (`registrar_key`),
  KEY `course_id` (`course_id`),
  KEY `course_instance_id` (`course_instance_id`),
  KEY `course_name` (`course_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `course_instances`
--

DROP TABLE IF EXISTS `course_instances`;
CREATE TABLE `course_instances` (
  `course_instance_id` bigint(20) NOT NULL auto_increment,
  `primary_course_alias_id` bigint(20) default NULL,
  `term` varchar(12) NOT NULL default '',
  `year` int(11) NOT NULL default '0',
  `activation_date` date NOT NULL default '0000-00-00',
  `expiration_date` date NOT NULL default '0000-00-00',
  `status` set('ACTIVE','INACTIVE','IN PROGRESS','AUTOFEED','CANCELED') NOT NULL default '',
  `enrollment` set('OPEN','MODERATED','CLOSED') NOT NULL default 'OPEN',
  PRIMARY KEY  (`course_instance_id`),
  KEY `primary_course_alias_id` (`primary_course_alias_id`),
  KEY `term_year_idx` (`term`,`year`),
  KEY `status` (`status`),
  KEY `enrollment` (`enrollment`),
  KEY `ci_date_range_idx` (`activation_date`,`expiration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL auto_increment,
  `department_id` int(11) NOT NULL default '0',
  `course_number` varchar(10) default NULL,
  `uniform_title` text NOT NULL,
  `old_id` int(11) default NULL,
  PRIMARY KEY  (`course_id`),
  KEY `department_id` (`department_id`),
  KEY `old_id` (`old_id`),
  KEY `course_number` (`course_number`),
  KEY `uniform_title` (`uniform_title`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `courses_no_dept`
--

DROP TABLE IF EXISTS `courses_no_dept`;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL auto_increment,
  `abbreviation` varchar(8) default NULL,
  `name` text,
  `library_id` int(11) NOT NULL default '0',
  `status` int(5) default NULL,
  PRIMARY KEY  (`department_id`),
  KEY `library_id` (`library_id`),
  KEY `abbr_index` (`abbreviation`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `electronic_item_audit`
--

DROP TABLE IF EXISTS `electronic_item_audit`;
CREATE TABLE `electronic_item_audit` (
  `audit_id` int(20) NOT NULL auto_increment,
  `item_id` bigint(20) NOT NULL default '0',
  `date_added` date NOT NULL default '0000-00-00',
  `added_by` int(11) NOT NULL default '0',
  `date_reviewed` date default NULL,
  `reviewed_by` int(11) default NULL,
  PRIMARY KEY  (`audit_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `hidden_readings`
--

DROP TABLE IF EXISTS `hidden_readings`;
CREATE TABLE `hidden_readings` (
  `hidden_id` int(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `reserve_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`hidden_id`),
  UNIQUE KEY `unique_constraint` (`user_id`,`reserve_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `inst_loan_periods`
--

DROP TABLE IF EXISTS `inst_loan_periods`;
CREATE TABLE `inst_loan_periods` (
  `loan_period_id` bigint(20) NOT NULL auto_increment,
  `loan_period` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`loan_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `inst_loan_periods_libraries`
--

DROP TABLE IF EXISTS `inst_loan_periods_libraries`;
CREATE TABLE `inst_loan_periods_libraries` (
  `id` bigint(20) NOT NULL auto_increment,
  `library_id` bigint(20) NOT NULL default '0',
  `loan_period_id` bigint(20) NOT NULL default '0',
  `default` set('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `instructor_attributes`
--

DROP TABLE IF EXISTS `instructor_attributes`;
CREATE TABLE `instructor_attributes` (
  `instructor_attribute_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `ils_user_id` varchar(50) default NULL,
  `ils_name` varchar(75) default NULL,
  `organizational_status` varchar(25) default NULL,
  PRIMARY KEY  (`instructor_attribute_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `item_upload_log`
--

DROP TABLE IF EXISTS `item_upload_log`;
CREATE TABLE `item_upload_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `course_instance_id` bigint(20) NOT NULL default '0',
  `item_id` bigint(20) NOT NULL default '0',
  `timestamp_uploaded` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `filesize` varchar(10) NOT NULL default '',
  `ipaddr` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
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
  KEY `ndx_title` (`title`),
  KEY `ndx_source` (`source`),
  KEY `ndx_content_notes` (`content_notes`),
  KEY `ndx_volume_edition` (`volume_edition`),
  KEY `ndx_pages_times` (`pages_times`),
  KEY `ndx_performer` (`performer`),
  KEY `ndx_url` (`url`(255)),
  KEY `ndx_author` (`author`),
  KEY `ndx_volume_title` (`volume_title`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `libraries`
--

DROP TABLE IF EXISTS `libraries`;
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
  `copyright_library_id` int(11) default NULL,
  PRIMARY KEY  (`library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `mimetypes`
--

DROP TABLE IF EXISTS `mimetypes`;
CREATE TABLE `mimetypes` (
  `mimetype_id` int(11) NOT NULL auto_increment,
  `mimetype` varchar(100) NOT NULL default '',
  `helper_app_url` text,
  `helper_app_name` text,
  `helper_app_icon` text,
  `file_extentions` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`mimetype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `news_id` bigint(20) NOT NULL auto_increment,
  `news_text` text NOT NULL COMMENT 'Text which will be displayed on all pages',
  `font_class` varchar(50) NOT NULL default '' COMMENT 'css class of text',
  `permission_level` set('0','1','2','3','4','5') default '',
  `begin_time` datetime default NULL,
  `end_time` datetime default NULL,
  `sort_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`news_id`),
  KEY `permission_level` (`permission_level`),
  KEY `begin_time` (`begin_time`),
  KEY `end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `not_trained`
--

DROP TABLE IF EXISTS `not_trained`;
CREATE TABLE `not_trained` (
  `user_id` int(11) NOT NULL default '0',
  `permission_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `note_id` bigint(20) NOT NULL auto_increment,
  `type` varchar(25) NOT NULL default '',
  `target_id` bigint(20) NOT NULL default '0',
  `note` text NOT NULL,
  `target_table` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`note_id`),
  KEY `type` (`type`),
  KEY `target` (`target_table`,`target_id`),
  KEY `ndx_note` (`note`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `permissions_levels`
--

DROP TABLE IF EXISTS `permissions_levels`;
CREATE TABLE `permissions_levels` (
  `permission_id` int(11) NOT NULL default '0',
  `label` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `physical_copies`
--

DROP TABLE IF EXISTS `physical_copies`;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `report_id` bigint(20) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `param_group` set('term','department','class','term_lib') default NULL,
  `sql` text NOT NULL,
  `parameters` varchar(255) default NULL,
  `min_permissions` int(11) NOT NULL default '4',
  `sort_order` int(11) NOT NULL default '0',
  `cached` tinyint(1) NOT NULL default '1' COMMENT 'boolean: 1 of 0',
  `cache_refresh_delay` int(4) NOT NULL default '6' COMMENT 'measured in hours',
  PRIMARY KEY  (`report_id`),
  KEY `min_permissions` (`min_permissions`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `reports_cache`
--

DROP TABLE IF EXISTS `reports_cache`;
CREATE TABLE `reports_cache` (
  `report_cache_id` bigint(20) NOT NULL auto_increment,
  `report_id` bigint(20) default NULL COMMENT 'foreign key -- `reports`',
  `params_cache` text,
  `report_cache` text,
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`report_cache_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `reserves`
--

DROP TABLE IF EXISTS `reserves`;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `skins`
--

DROP TABLE IF EXISTS `skins`;
CREATE TABLE `skins` (
  `id` int(11) NOT NULL auto_increment,
  `skin_name` varchar(20) NOT NULL default '',
  `skin_stylesheet` text NOT NULL,
  `default_selected` set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `skin_name` (`skin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `special_users`
--

DROP TABLE IF EXISTS `special_users`;
CREATE TABLE `special_users` (
  `user_id` int(11) NOT NULL default '0',
  `password` varchar(75) NOT NULL default '',
  `expiration` date default NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `special_users_audit`
--

DROP TABLE IF EXISTS `special_users_audit`;
CREATE TABLE `special_users_audit` (
  `user_id` bigint(20) NOT NULL default '0',
  `creator_user_id` bigint(20) NOT NULL default '0',
  `date_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `email_sent_to` varchar(255) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `staff_libraries`
--

DROP TABLE IF EXISTS `staff_libraries`;
CREATE TABLE `staff_libraries` (
  `staff_library_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `library_id` int(11) NOT NULL default '0',
  `permission_level_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`staff_library_id`),
  KEY `user_id` (`user_id`),
  KEY `library_id` (`library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
CREATE TABLE `terms` (
  `term_id` int(11) NOT NULL auto_increment,
  `sort_order` int(11) NOT NULL default '0',
  `term_name` varchar(100) NOT NULL default '',
  `term_year` varchar(4) NOT NULL default '',
  `begin_date` date NOT NULL default '0000-00-00',
  `end_date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`term_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_view_log`
--

DROP TABLE IF EXISTS `user_view_log`;
CREATE TABLE `user_view_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `reserve_id` bigint(20) NOT NULL default '0',
  `timestamp_viewed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `reserve_id` (`reserve_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
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
  KEY `last_name` (`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

