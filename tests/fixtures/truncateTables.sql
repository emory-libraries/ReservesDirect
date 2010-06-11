--
--	truncateTables.sql
--	clear all tables
	
--	Created by Jason White (jbwhite@emory.edu)
--	
--	This file is part of ReservesDirect

--	Copyright (c) 2004-2009 Emory University, Atlanta, Georgia.

--	Licensed under the ReservesDirect License, Version 1.0 (the "License");      
--	you may not use this file except in compliance with the License.     
--	You may obtain a copy of the full License at                              
--	http://www.reservesdirect.org/licenses/LICENSE-1.0

--	ReservesDirect is distributed in the hope that it will be useful,
--	but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
--	implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
--	PURPOSE, and without any warranty as to non-infringement of any third
--	party's rights.  See the License for the specific language governing         
--	permissions and limitations under the License.

--	ReservesDirect is located at:
--	http://www.reservesdirect.org/

--	This file contains sql statements to reset the database to a clean state
--	All tables are truncated and the permissions table is loaded.


TRUNCATE access;
TRUNCATE circ_rules;
TRUNCATE courses;
TRUNCATE courses_no_dept;
TRUNCATE course_aliases;
TRUNCATE course_instances;
TRUNCATE departments;
TRUNCATE electronic_item_audit;
TRUNCATE help_articles;
TRUNCATE help_art_tags;
TRUNCATE help_art_to_art;
TRUNCATE help_art_to_role;
TRUNCATE help_categories;
TRUNCATE help_cat_to_role;
TRUNCATE hidden_readings;
TRUNCATE ils_requests;
TRUNCATE instructor_attributes;
TRUNCATE inst_loan_periods;
TRUNCATE inst_loan_periods_libraries;
TRUNCATE items;
TRUNCATE item_upload_log;
TRUNCATE libraries;
TRUNCATE mimetypes;
TRUNCATE news;
TRUNCATE notes;
TRUNCATE not_trained;
TRUNCATE permissions_levels;
TRUNCATE physical_copies;
TRUNCATE proxied_hosts;
TRUNCATE proxies;
TRUNCATE reports;
TRUNCATE reports_cache;
TRUNCATE requests;
TRUNCATE reserves;
TRUNCATE rightsholders;
TRUNCATE skins;
TRUNCATE special_users;
TRUNCATE special_users_audit;
TRUNCATE staff_libraries;
TRUNCATE terms;
TRUNCATE users;
TRUNCATE user_view_log;

INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES
(0, 'student'),
(1, 'custodian'),
(2, 'proxy'),
(3, 'instructor'),
(4, 'staff'),
(5, 'admin');
