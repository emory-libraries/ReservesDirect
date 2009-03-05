--
--	staff.sql
--	basic staff test set
	
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

--	This file contains datasets to build sample staff users


INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `email`, `external_user_key`, `dflt_permission_level`, `last_login`, `old_id`, `old_user_id`) VALUES
(109, 'nobody0', 'Frances', 'Bryson', 'nobody0@emory.edu', NULL, 4, '2008-08-20', 138, 2914),
(33166, 'nobody1', 'Mary', 'Pici', 'nobody1@emory.edu', NULL, 5, '2009-02-09', NULL, NULL),
(378, 'nobody2', 'Essie', 'Mills', 'nobody2@sph.emory.edu', NULL, 4, '2008-02-19', 1633, 11603),
(471, 'nobody3', 'Jimmy', 'Odem', 'nobody3@emory.edu', NULL, 4, '2009-02-05', 629, 2483),
(818, 'nobody4', 'Tim', 'Maloy', 'nobody4@emory.edu', NULL, 4, '2007-09-07', 1026, NULL);


INSERT INTO `staff_libraries` (`staff_library_id`, `user_id`, `library_id`, `permission_level_id`) VALUES
(1, 109, 2, 4),
(2, 33166, 1, 4),
(3, 378, 1, 4),
(4, 471, 4, 4),
(5, 818, 1, 4);