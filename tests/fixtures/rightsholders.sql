--
--	rightsholders.sql
--	basic sample rightsholder information
	
--	Created by Ben Ranker (branker@emory.edu)
--	
--	This file is part of ReservesDirect

--	Copyright (c) 2010 Emory University, Atlanta, Georgia.

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

--	This file contains datasets to build sample materials rightsholders

INSERT INTO `rightsholders` (`ISBN`, `name`, `contact_name`, `contact_email`, `fax`, `post_address`, `rights_url`, `policy_limit`) VALUES
('FAKE ISBN', 'Somebody Book Publishers, inc', 'Jane Doe', 'jdoe@somebody.com', '308-555-6789', '123 Example Pl\nAtlanta, NE  68923', 'http://www.somebody.com/copyright/policy/', 'free up to 83%');
