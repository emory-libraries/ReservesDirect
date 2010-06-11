--
--	requests.sql
--	basic request test set
	
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

--	This file contains datasets to build sample classes, reserves, requests and instructors

INSERT INTO `requests` (`request_id`, `reserve_id`, `item_id`, `user_id`, `date_requested`, `date_processed`, `date_desired`, `priority`, `course_instance_id`, `max_enrollment`, `type`) VALUES
(1800, 202864, 63031, 1183, '2005-08-03', NULL, NULL, NULL, 11496, NULL, 'PHYSICAL'),
(5776, 252126, 105260, 111, '2006-08-10', NULL, NULL, NULL, 16130, NULL, 'PHYSICAL'),
(5777, 252127, 105261, 111, '2006-08-10', NULL, NULL, NULL, 16130, NULL, 'PHYSICAL'),
(6247, 256519, 96261, 124, '2006-08-28', NULL, NULL, NULL, 14214, NULL, 'PHYSICAL'),
(7130, 272740, 19762, 1245, '2006-12-15', NULL, NULL, NULL, 19707, NULL, 'PHYSICAL');

INSERT INTO `items` (`item_id`, `title`, `author`, `source`, `volume_title`, `content_notes`, `volume_edition`, `pages_times`, `pages_times_range`, `pages_times_used`, `pages_times_total`, `performer`, `local_control_key`, `creation_date`, `last_modified`, `url`, `mimetype`, `home_library`, `private_user_id`, `item_group`, `item_type`, `item_icon`, `ISBN`, `ISSN`, `OCLC`, `status`, `material_type`) VALUES
(19762, 'Anthropology as cultural critique : an experimental moment in the human sciences / George E. Marcus and Michael M.J. Fischer.', 'Marcus, George E.', 'Chicago :', '', '', '', '1-10/100', '1-50', '50', '100', '', 'ocm12614452', '2004-11-22', '2005-09-06', NULL, NULL, 1, NULL, 'MONOGRAPH', 'ITEM', NULL, 'FAKE ISBN', NULL, NULL, 'ACTIVE', 'BOOK_PORTION'),
(63031, 'Mistaking Africa : curiosities and inventions of the American mind', 'Keim, Curtis A.', 'Boulder, Colo. : Westview Press, c1999.', '', NULL, '', '1-10/100', '1-10', '10', '100', '', 'ocm41017415', '2004-01-16', '2006-01-31', NULL, NULL, 1, NULL, 'MONOGRAPH', 'ITEM', NULL, NULL, NULL, NULL, 'ACTIVE', 'BOOK_PORTION'),
(96261, 'Ancient Egypt and the Old Testament / John D. Currid ; foreword by Kenneth A. Kitchen.', 'Currid, John D.,1951-', 'Grand Rapids, Mich. : Baker Books, c1997.', '', NULL, '', '1-50/100', '1-50', '50', '100', '', 'ocm37179727', '2006-01-05', '2006-08-28', NULL, 'text/html', 1, NULL, 'MONOGRAPH', 'ITEM', NULL, 'ANOTHER ISBN', NULL, NULL, 'ACTIVE', 'BOOK_PORTION'),
(105260, 'Oxford Bible atlas / edited by Herbert G. May ; with the assistance of G.N.S. Hunt ; in consultation with R.W. Hamilton.', '', 'New York : Oxford University Press, 1984.', '', NULL, '', '1-50/100', '1-50', '50', '100', '', 'ocm10778645', '2006-08-10', '2006-08-10', NULL, 'text/html', 6, NULL, 'MONOGRAPH', 'ITEM', NULL, 'FAKE ISBN', NULL, NULL, 'ACTIVE', 'BOOK_PORTION'),
(105261, 'Oxford Bible atlas / edited by Herbert G. May ; with the assistance of G.N.S. Hunt ; in consultation with R.W. Hamilton.', '', 'New York : Oxford University Press, 1984.', '', NULL, '', '1-50/100', '1-50', '50', '100', '', 'ocm10778645', '2006-08-10', '2006-08-10', NULL, 'text/html', 6, NULL, 'MONOGRAPH', 'ITEM', NULL, 'FAKE ISBN', NULL, NULL, 'ACTIVE', 'BOOK_PORTION');

INSERT INTO `reserves` (`reserve_id`, `course_instance_id`, `item_id`, `activation_date`, `expiration`, `status`, `sort_order`, `date_created`, `last_modified`, `requested_loan_period`, `parent_id`) VALUES
(202864, 11496, 63031, '2005-08-15', '2005-12-31', 'PULLED', 0, '2005-08-03', '2005-08-03', NULL, NULL),
(252126, 16130, 105260, '2006-08-30', '2006-12-23', 'RECALLED', 1, '2006-08-10', '2006-08-10', NULL, NULL),
(252127, 16130, 105261, '2006-08-30', '2006-12-23', 'PULLED', 2, '2006-08-10', '2006-08-10', NULL, NULL),
(256519, 14214, 96261, '2006-08-25', '2006-12-23', 'IN PROCESS', 1, '2006-08-28', '2006-08-28', NULL, NULL),
(272740, 19707, 19762, '2007-01-01', '2007-05-16', 'IN PROCESS', 70, '2006-12-15', '2007-03-15', '2 Hours', NULL);

INSERT INTO `course_instances` (`course_instance_id`, `primary_course_alias_id`, `term`, `year`, `activation_date`, `expiration_date`, `status`, `enrollment`, `reviewed_date`, `reviewed_by`) VALUES
(11496, 11943, 'FALL', 2005, '2005-08-15', '2005-12-31', 'ACTIVE', 'OPEN', NULL, NULL),
(14214, 14979, 'FALL', 2006, '2006-08-25', '2006-12-23', 'ACTIVE', 'OPEN', NULL, NULL),
(16130, 16985, 'FALL', 2006, '2006-08-25', '2006-12-23', 'ACTIVE', 'OPEN', NULL, NULL),
(19707, 20782, 'SPRING', 2007, '2007-01-01', '2007-05-16', 'CANCELED', 'OPEN', NULL, NULL);

INSERT INTO `course_aliases` (`course_alias_id`, `course_id`, `course_instance_id`, `course_name`, `section`, `registrar_key`, `override_feed`) VALUES
(11943, 7782, 11496, 'Introduction to African Studies', '', NULL, 0),
(14979, 9555, 14214, 'Spec.Topics in Biblic.Inter.', '000', '5069_EMORY_BI_698_SEC000', 0),
(16985, 10533, 16130, 'Archaeology And The Bible', '000', '5069_EMORY_MESAS_250S_SEC000', 0),
(16987, 10535, 16130, 'Intro To Biblical Archaeology Archaeology and the Bible', '000', '5069_EMORY_JS_250S_SEC000', 0),
(19645, 11747, 14214, 'The Bible and the Ancient Near East', 'R', NULL, 0),
(20782, 11065, 19707, 'Spec Tops: Anthropology', '00P', '5071_EMORY_ANT_585_SEC00P', 0);

INSERT INTO `courses` (`course_id`, `department_id`, `course_number`, `uniform_title`, `old_id`) VALUES
(7782, 218, '263', 'Introduction to African Studies', NULL),
(9555, 271, '698', 'Spec.Topics in Biblic.Inter.', NULL),
(10533, 292, '250S', 'Archaeology And The Bible', NULL),
(10535, 62, '250S', 'Intro To Biblical Archaeology Archaeology and the Bible', NULL),
(11065, 6, '585', 'Spec Tops: Anthropology', NULL),
(11747, 292, '370', '', NULL);

INSERT INTO `departments` (`department_id`, `abbreviation`, `name`, `library_id`, `status`) VALUES
(6, 'ANT', 'Anthropology', 1, NULL),
(62, 'JS', 'Jewish Studies', 1, NULL),
(271, 'BI', 'Biblical Interpretation', 8, NULL),
(292, 'MESAS', 'Middle Eastern & South Asian', 1, NULL);

INSERT INTO `access` (`access_id`, `user_id`, `alias_id`, `permission_level`, `enrollment_status`, `autofeed_run_indicator`) VALUES
(1, 1183, 11943, 3, 'APPROVED', NULL),
(2, 111, 14979, 3, 'APPROVED', NULL),
(3, 111, 16985, 3, 'AUTOFEED', '5066_787'),
(4, 1183, 16987, 3, 'AUTOFEED', '5066_787'),
(5, 1183, 19645, 3, 'AUTOFEED', '5066_787'),
(6, 124, 20782, 3, 'PENDING', NULL);

INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `email`, `external_user_key`, `dflt_permission_level`, `last_login`, `old_id`, `old_user_id`) VALUES
(111, 'inst0', 'Jean', 'Borowski', 'inst0@emory.edu', NULL, 3, '2008-01-15', 110, 8342),
(124, 'inst1', 'Edna Jean', 'Collins', 'inst1@emory.edu', NULL, 3, '2009-02-09', 189, 7706),
(1183, 'inst2', 'Billie', 'Bay', 'inst2@emory.edu', NULL, 3, '2009-02-02', 72, 2226);
