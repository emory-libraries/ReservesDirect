--
-- create_db.sql
-- Creates table structures and inserts seed data for initial database
-- creation.
--
-- Created by Chris Roddy (croddy@emory.edu)
--
-- This file is part of ReservesDirect 2
--
-- Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.
-- 
-- ReservesDirect 2.1 is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
-- 
-- ReservesDirect 2.1 is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
-- 
-- You should have received a copy of the GNU General Public License
-- along with ReservesDirect 2.1; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
-- 
-- ReservesDirect 2 is located at:
-- http://www.reservesdirect.org
-- 

--
-- Table structure for table `access`
--

CREATE TABLE access (
  access_id int(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  alias_id int(20) NOT NULL default '0',
  permission_level int(11) NOT NULL default '0',
  PRIMARY KEY  (access_id),
  KEY users_id (user_id),
  KEY alias_id (alias_id),
  KEY permission_level (permission_level)
) TYPE=MyISAM;

--
-- Table structure for table `circ_rules`
--

CREATE TABLE circ_rules (
  id int(11) NOT NULL auto_increment,
  circ_rule varchar(50) NOT NULL default '',
  alt_circ_rule varchar(50) NOT NULL default '',
  default_selected set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Table structure for table `course_aliases`
--

CREATE TABLE course_aliases (
  course_alias_id bigint(20) NOT NULL auto_increment,
  course_id int(11) NOT NULL default '0',
  course_instance_id bigint(20) NOT NULL default '0',
  section varchar(8) default NULL,
  PRIMARY KEY  (course_alias_id),
  KEY course_id (course_id),
  KEY course_instance_id (course_instance_id)
) TYPE=MyISAM;

--
-- Table structure for table `course_instances`
--

CREATE TABLE course_instances (
  course_instance_id bigint(20) NOT NULL auto_increment,
  primary_course_alias_id bigint(20) NOT NULL default '0',
  term varchar(12) NOT NULL default '',
  year int(11) NOT NULL default '0',
  activation_date date NOT NULL default '0000-00-00',
  expiration_date date NOT NULL default '0000-00-00',
  status set('ACTIVE','INACTIVE','IN PROGRESS') NOT NULL default '',
  enrollment varchar(12) NOT NULL default '',
  PRIMARY KEY  (course_instance_id),
  KEY primary_course_alias_id (primary_course_alias_id),
  KEY term_year_idx (term,year),
  KEY status (status),
  KEY enrollment (enrollment),
  KEY ci_date_range_idx (activation_date,expiration_date)
) TYPE=MyISAM;

--
-- Table structure for table `courses`
--

CREATE TABLE courses (
  course_id int(11) NOT NULL auto_increment,
  department_id int(11) NOT NULL default '0',
  course_number varchar(10) default NULL,
  course_name text,
  uniform_title enum('t','f') NOT NULL default 't',
  old_id int(11) NOT NULL default '0',
  PRIMARY KEY  (course_id),
  KEY department_id (department_id),
  KEY uniform_title (uniform_title),
  KEY old_id (old_id)
) TYPE=MyISAM;

--
-- Table structure for table `courses_no_dept`
--

CREATE TABLE courses_no_dept (
  course_id int(11) NOT NULL auto_increment,
  department_id int(11) NOT NULL default '0',
  course_number varchar(10) default NULL,
  course_name text,
  uniform_title enum('t','f') NOT NULL default 't',
  old_id int(11) NOT NULL default '0',
  dept_abv varchar(50) NOT NULL default '',
  old_course_num varchar(50) NOT NULL default '',
  PRIMARY KEY  (course_id),
  KEY department_id (department_id),
  KEY uniform_title (uniform_title),
  KEY old_id (old_id)
) TYPE=MyISAM;

--
-- Table structure for table `departments`
--

CREATE TABLE departments (
  department_id int(11) NOT NULL auto_increment,
  abbreviation varchar(8) default NULL,
  name text,
  library_id int(11) NOT NULL default '0',
  status int(5) default NULL,
  KEY library_id (library_id),
  KEY deptid (department_id)
) TYPE=MyISAM;

--
-- Table structure for table `electronic_item_audit`
--

CREATE TABLE electronic_item_audit (
  audit_id int(20) NOT NULL auto_increment,
  item_id bigint(20) NOT NULL default '0',
  date_added date NOT NULL default '0000-00-00',
  added_by int(11) NOT NULL default '0',
  date_reviewed date default NULL,
  reviewed_by int(11) default NULL,
  PRIMARY KEY  (audit_id),
  KEY item_id (item_id)
) TYPE=MyISAM;

--
-- Table structure for table `hidden_readings`
--

CREATE TABLE hidden_readings (
  hidden_id int(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  reserve_id bigint(20) NOT NULL default '0',
  PRIMARY KEY  (hidden_id),
  UNIQUE KEY unique_constraint (user_id,reserve_id)
) TYPE=MyISAM;

--
-- Table structure for table `inst_loan_periods`
--

CREATE TABLE inst_loan_periods (
  loan_period_id bigint(20) NOT NULL auto_increment,
  loan_period varchar(255) NOT NULL default '',
  PRIMARY KEY  (loan_period_id)
) TYPE=MyISAM;

--
-- Table structure for table `inst_loan_periods_libraries`
--

CREATE TABLE inst_loan_periods_libraries (
  id bigint(20) NOT NULL auto_increment,
  library_id bigint(20) NOT NULL default '0',
  loan_period_id bigint(20) NOT NULL default '0',
  `default` set('true','false') NOT NULL default 'false',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Table structure for table `instructor_attributes`
--

CREATE TABLE instructor_attributes (
  instructor_attribute_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  ils_user_id varchar(50) default NULL,
  ils_name varchar(75) default NULL,
  organizational_status varchar(25) default NULL,
  PRIMARY KEY  (instructor_attribute_id),
  KEY user_id (user_id)
) TYPE=MyISAM;

--
-- Table structure for table `item_upload_log`
--

CREATE TABLE item_upload_log (
  id bigint(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  course_instance_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  timestamp_uploaded timestamp(14) NOT NULL,
  filesize varchar(10) NOT NULL default '',
  ipaddr varchar(15) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Table structure for table `items`
--

CREATE TABLE items (
  item_id bigint(20) NOT NULL auto_increment,
  title varchar(255) default NULL,
  author varchar(255) default NULL,
  source varchar(255) default NULL,
  volume_title varchar(255) default NULL,
  content_notes varchar(255) default NULL,
  volume_edition varchar(255) default NULL,
  pages_times varchar(255) default NULL,
  performer varchar(255) default NULL,
  local_control_key varchar(30) default NULL,
  creation_date date NOT NULL default '0000-00-00',
  last_modified date NOT NULL default '0000-00-00',
  url varchar(255) default NULL,
  mimetype varchar(100) default 'text/html',
  home_library int(11) NOT NULL default '0',
  private_user_id int(11) default NULL,
  item_group varchar(25) NOT NULL default '0',
  item_type set('ITEM','HEADING') NOT NULL default 'ITEM',
  item_icon varchar(255) default NULL,
  old_id int(11) NOT NULL default '0',
  PRIMARY KEY  (item_id),
  KEY private_user_id (private_user_id),
  KEY home_library (home_library),
  KEY mimetype (mimetype),
  KEY item_group (item_group),
  KEY old_id (old_id),
  KEY controlKey (local_control_key),
  FULLTEXT KEY fulltext_title (title),
  FULLTEXT KEY fulltext_source (source),
  FULLTEXT KEY fulltext_content_notes (content_notes),
  FULLTEXT KEY fulltext_volume_edition (volume_edition),
  FULLTEXT KEY fulltext_pages_times (pages_times),
  FULLTEXT KEY fulltext_performer (performer),
  FULLTEXT KEY fulltext_url (url),
  FULLTEXT KEY fulltext_author (author),
  FULLTEXT KEY fulltext_volume_title (volume_title)
) TYPE=MyISAM;

--
-- Table structure for table `libraries`
--

CREATE TABLE libraries (
  library_id int(11) NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  nickname varchar(15) NOT NULL default '',
  ils_prefix varchar(10) NOT NULL default '',
  reserve_desk varchar(50) NOT NULL default '',
  url text,
  contact_email varchar(255) default NULL,
  monograph_library_id int(11) NOT NULL default '0',
  multimedia_library_id int(11) NOT NULL default '0',
  PRIMARY KEY  (library_id)
) TYPE=MyISAM;

--
-- Table structure for table `mimetypes`
--

CREATE TABLE mimetypes (
  mimetype_id int(11) NOT NULL auto_increment,
  mimetype varchar(100) NOT NULL default '',
  helper_app_url text,
  helper_app_name text,
  helper_app_icon text,
  file_extentions varchar(255) NOT NULL default '',
  PRIMARY KEY  (mimetype_id)
) TYPE=MyISAM;

--
-- Table structure for table `not_trained`
--

CREATE TABLE not_trained (
  user_id int(11) NOT NULL default '0',
  permission_level int(11) NOT NULL default '0',
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

--
-- Table structure for table `notes`
--

CREATE TABLE notes (
  note_id bigint(20) NOT NULL auto_increment,
  type varchar(25) NOT NULL default '',
  target_id bigint(20) NOT NULL default '0',
  note text NOT NULL,
  target_table varchar(50) NOT NULL default '',
  PRIMARY KEY  (note_id),
  KEY type (type),
  KEY target (target_table,target_id),
  FULLTEXT KEY fulltext_note (note)
) TYPE=MyISAM;

--
-- Table structure for table `permissions_levels`
--

CREATE TABLE permissions_levels (
  permission_id int(11) NOT NULL default '0',
  label varchar(25) NOT NULL default '',
  PRIMARY KEY  (permission_id)
) TYPE=MyISAM;

--
-- Table structure for table `physical_copies`
--

CREATE TABLE physical_copies (
  physical_copy_id int(11) NOT NULL auto_increment,
  reserve_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  status varchar(30) NOT NULL default '',
  call_number text,
  barcode varchar(15) default NULL,
  owning_library varchar(15) NOT NULL default '0',
  item_type varchar(30) default NULL,
  owner_user_id int(11) default NULL,
  PRIMARY KEY  (physical_copy_id),
  KEY reserves_id (reserve_id),
  KEY item_id (item_id),
  KEY status (status),
  KEY barcode (barcode),
  KEY item_type (item_type),
  KEY owner_user_id (owner_user_id)
) TYPE=MyISAM;

--
-- Table structure for table `requests`
--

CREATE TABLE requests (
  request_id bigint(20) NOT NULL auto_increment,
  reserve_id int(11) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  date_requested date NOT NULL default '0000-00-00',
  date_processed date default NULL,
  date_desired date default NULL,
  priority int(11) default NULL,
  course_instance_id bigint(20) NOT NULL default '0',
  PRIMARY KEY  (request_id),
  KEY item_id (item_id),
  KEY user_id (user_id),
  KEY date_requested (date_requested),
  KEY date_desired (date_desired),
  KEY priority (priority),
  KEY course_instance_id (course_instance_id),
  KEY reserve_id (reserve_id)
) TYPE=MyISAM;

--
-- Table structure for table `reserves`
--

CREATE TABLE reserves (
  reserve_id bigint(20) NOT NULL auto_increment,
  course_instance_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  activation_date date NOT NULL default '0000-00-00',
  expiration date NOT NULL default '0000-00-00',
  status set('ACTIVE','INACTIVE','IN PROCESS') NOT NULL default 'ACTIVE',
  sort_order int(11) NOT NULL default '0',
  date_created date NOT NULL default '0000-00-00',
  last_modified date NOT NULL default '0000-00-00',
  requested_loan_period varchar(255) default NULL,
  PRIMARY KEY  (reserve_id),
  UNIQUE KEY unique_constraint (course_instance_id,item_id),
  KEY reserves_sort_ci_idx (course_instance_id,sort_order),
  KEY item_id (item_id),
  KEY reserves_date_range_idx (activation_date,expiration),
  KEY status (status)
) TYPE=MyISAM;

--
-- Table structure for table `reserves_old`
--

CREATE TABLE reserves_old (
  reserve_id bigint(20) NOT NULL auto_increment,
  course_instance_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  activation_date date NOT NULL default '0000-00-00',
  expiration date NOT NULL default '0000-00-00',
  status set('ACTIVE','INACTIVE','IN PROCESS') NOT NULL default 'ACTIVE',
  sort_order int(11) NOT NULL default '0',
  date_created date NOT NULL default '0000-00-00',
  last_modified date NOT NULL default '0000-00-00',
  PRIMARY KEY  (reserve_id),
  KEY reserves_sort_ci_idx (course_instance_id,sort_order),
  KEY item_id (item_id),
  KEY reserves_date_range_idx (activation_date,expiration),
  KEY status (status)
) TYPE=MyISAM;

--
-- Table structure for table `skins`
--

CREATE TABLE skins (
  id int(11) NOT NULL auto_increment,
  skin_name varchar(20) NOT NULL default '',
  skin_stylesheet text NOT NULL,
  default_selected set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (id),
  UNIQUE KEY skin_name (skin_name)
) TYPE=MyISAM;

--
-- Table structure for table `special_users`
--

CREATE TABLE special_users (
  user_id int(11) NOT NULL default '0',
  password varchar(75) NOT NULL default '',
  expiration date default NULL,
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

--
-- Table structure for table `special_users_audit`
--

CREATE TABLE special_users_audit (
  user_id bigint(20) NOT NULL default '0',
  creator_user_id bigint(20) NOT NULL default '0',
  date_created timestamp(14) NOT NULL,
  email_sent_to varchar(255) default NULL
) TYPE=MyISAM;

--
-- Table structure for table `staff_libraries`
--

CREATE TABLE staff_libraries (
  staff_library_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  library_id int(11) NOT NULL default '0',
  permission_level_id int(11) NOT NULL default '0',
  PRIMARY KEY  (staff_library_id),
  KEY user_id (user_id),
  KEY library_id (library_id)
) TYPE=MyISAM;

--
-- Table structure for table `terms`
--

CREATE TABLE terms (
  term_id int(11) NOT NULL auto_increment,
  sort_order int(11) NOT NULL default '0',
  term_name varchar(100) NOT NULL default '',
  term_year varchar(4) NOT NULL default '',
  begin_date date NOT NULL default '0000-00-00',
  end_date date NOT NULL default '0000-00-00',
  PRIMARY KEY  (term_id),
  KEY sort_order (sort_order)
) TYPE=MyISAM;

--
-- Table structure for table `user_view_log`
--

CREATE TABLE user_view_log (
  id bigint(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  reserve_id bigint(20) NOT NULL default '0',
  timestamp_viewed timestamp(14) NOT NULL,
  PRIMARY KEY  (id),
  KEY user_id (user_id),
  KEY reserve_id (reserve_id)
) TYPE=MyISAM;

--
-- Table structure for table `users`
--

CREATE TABLE users (
  user_id int(11) NOT NULL auto_increment,
  username varchar(50) NOT NULL default '',
  first_name varchar(50) default NULL,
  last_name varchar(50) default NULL,
  email varchar(75) default NULL,
  dflt_permission_level int(11) NOT NULL default '0',
  last_login date NOT NULL default '0000-00-00',
  old_id int(11) default NULL,
  old_user_id int(11) default NULL,
  PRIMARY KEY  (user_id),
  UNIQUE KEY username (username),
  KEY max_permission_level (dflt_permission_level),
  KEY old_id (old_id),
  KEY old_user_id (old_user_id)
) TYPE=MyISAM;

-- MySQL dump 9.11
--
-- Host: localhost    Database: reserves
-- ------------------------------------------------------
-- Server version	4.0.21-standard-log

--
-- Dumping data for table `mimetypes`
--

INSERT INTO mimetypes VALUES (1,'application/pdf','http://www.adobe.com/products/acrobat/readstep2.html','Adobe Acrobat Reader','images/doc_type_icons/doctype-pdf.gif ','pdf');
INSERT INTO mimetypes VALUES (2,'audio/x-pn-realaudio','http://www.real.com/','RealPlayer','images/doc_type_icons/doctype-sound.gif','ram');
INSERT INTO mimetypes VALUES (3,'video/quicktime','http://www.apple.com/quicktime/','Quicktime Player','images/doc_type_icons/doctype-movie.gif','mov');
INSERT INTO mimetypes VALUES (4,'application/msword','http://office.microsoft.com/Assistance/9798/viewerscvt.aspx','Microsoft Word','images/doc_type_icons/doctype-text.gif','doc');
INSERT INTO mimetypes VALUES (5,'application/vnd.ms-excel','http://office.microsoft.com/Assistance/9798/viewerscvt.aspx','Microsoft Excel','images/doc_type_icons/doctype-text.gif','xcl');
INSERT INTO mimetypes VALUES (6,'application/vnd.ms-powerpoint','http://office.microsoft.com/Assistance/9798/viewerscvt.aspx','Microsoft Powerpoint','images/doc_type_icons/doctype-text.gif','ppt');
INSERT INTO mimetypes VALUES (7,'text/html',NULL,NULL,'images/doc_type_icons/doctype-clear.gif','');

--
-- Dumping data for table `permissions_levels`
--

INSERT INTO permissions_levels VALUES (0,'student');
INSERT INTO permissions_levels VALUES (1,'custodian');
INSERT INTO permissions_levels VALUES (2,'proxy');
INSERT INTO permissions_levels VALUES (3,'instructor');
INSERT INTO permissions_levels VALUES (4,'staff');
INSERT INTO permissions_levels VALUES (5,'admin');

--
-- Dumping data for table `terms`
--

INSERT INTO terms VALUES (1,1,'FALL','2004','2004-08-26','2004-12-18');
INSERT INTO terms VALUES (2,2,'SPRING','2005','2005-01-01','2005-05-31');
INSERT INTO terms VALUES (3,3,'SUMMER','2005','2005-05-15','2005-08-16');
INSERT INTO terms VALUES (4,4,'FALL','2005','2005-08-15','2005-12-31');
INSERT INTO terms VALUES (6,5,'SPRING','2006','2006-01-01','2006-05-16');
INSERT INTO terms VALUES (7,7,'SUMMER','2006','2006-05-17','2006-08-16');

--
-- Seed data for table `users`: admin
--

INSERT INTO users (username, first_name, last_name, dflt_permission_level) VALUES ('admin', 'ReservesDirect', 'Administrator', 5);

--
-- Seed data for table `special_users`: admin
--

INSERT INTO special_users VALUES (1, '21232f297a57a5a743894a0e4a801fc3', NULL);


