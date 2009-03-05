--
--	libraries.sql
--	basic library test set
	
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

--	This file contains datasets to load the library table

INSERT INTO `libraries` (`library_id`, `name`, `nickname`, `ils_prefix`, `reserve_desk`, `url`, `contact_email`, `monograph_library_id`, `multimedia_library_id`, `copyright_library_id`) VALUES
(1, 'Woodruff Library', 'general', 'GEN', 'GENERAL', 'http://web.library.emory.edu/', 'reserves@emory.edu', 1, 3, NULL),
(2, 'Goizueta Business Library', 'bus', 'BUS', 'BUS', 'http://business.library.emory.edu/', 'Reserves@bus.emory.edu', 2, 3, NULL),
(3, 'Music & Media', 'musicmedia', 'MM', 'MUSICMEDIA', 'http://web.library.emory.edu/libraries/music/', 'genmus@libcat1.cc.emory.edu', 3, 3, NULL),
(4, 'Health Sciences Center Library', 'health', 'HEALTH', 'HEALTH', 'http://www.healthlibrary.emory.edu/', 'medsys@listserv.cc.emory.edu,barbara.abu-zeid@emory.edu', 4, 4, NULL),
(6, 'Oxford College Hoke O''Kelley Memorial Library', 'oxford', 'OXF', 'OXFORD', 'http://www.emory.edu/OXFORD/Library/', 'apric03@learnlink.emory.edu', 6, 6, NULL),
(7, 'James S. Guy Chemistry Library', 'chemistry', 'CHEM', 'CHEMISTRY', 'http://chemistry.library.emory.edu/', 'wfmason@emory.edu', 7, 3, NULL),
(8, 'Pitts Theology Library', 'pitts', 'THE', 'THEOLOGY', 'http://www.pitts.emory.edu/', 'nwilli9@emory.edu', 8, 3, NULL),
(9, 'Hugh F. MacMillan Library', 'law', 'LAW', 'LAW', 'http://law.emory.edu/library/', 'lawcirch@libcat1.cc.emory.edu', 9, 9, NULL);
