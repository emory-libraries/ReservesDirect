--
--	proxies.sql
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

--	This file contains datasets to load the proxies & proxied hosts

INSERT INTO `proxies` (`id`, `name`, `prefix`) VALUES (1, 'Proxy-server', 'http://proxy.me/');
INSERT INTO `proxied_hosts` (`id`, `proxy_id`, `domain`, `partial_match`) VALUES 
(1, 1, 'online.sagepub.com', 0),
(2, 1, 'sagepub.com', 1);