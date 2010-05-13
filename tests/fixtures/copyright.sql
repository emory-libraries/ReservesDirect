--
--  copyright.sql
--  basic copyright test set
  
--  Created by Sari Connard (sconna2@emory.edu)
--  
--  This file is part of ReservesDirect

--  Copyright (c) 2004-2010 Emory University, Atlanta, Georgia.

--  Licensed under the ReservesDirect License, Version 1.0 (the "License");      
--  you may not use this file except in compliance with the License.     
--  You may obtain a copy of the full License at                              
--  http://www.reservesdirect.org/licenses/LICENSE-1.0

--  ReservesDirect is distributed in the hope that it will be useful,
--  but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
--  implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
--  PURPOSE, and without any warranty as to non-infringement of any third
--  party's rights.  See the License for the specific language governing         
--  permissions and limitations under the License.

--  ReservesDirect is located at:
--  http://www.reservesdirect.org/

--  This file contains datasets to build sample classes, reserves, requests and instructors

INSERT INTO `requests` (`request_id`, `reserve_id`, `item_id`, `user_id`, `date_requested`, `date_processed`, `date_desired`, `priority`, `course_instance_id`, `max_enrollment`, `type`) VALUES
(1800, 202864, 63031, 1183, '2005-08-03', NULL, NULL, NULL, 11496, NULL, 'PHYSICAL'),
(5776, 252126, 105260, 111, '2006-08-10', NULL, NULL, NULL, 16130, NULL, 'PHYSICAL'),
(5777, 252127, 105261, 111, '2006-08-10', NULL, NULL, NULL, 16130, NULL, 'PHYSICAL'),
(6247, 256519, 96261, 124, '2006-08-28', NULL, NULL, NULL, 14214, NULL, 'PHYSICAL'),
(7130, 272740, 19762, 1245, '2006-12-15', NULL, NULL, NULL, 19707, NULL, 'PHYSICAL');

INSERT INTO `items` (`item_id`, `title`,`author`,`source`,`volume_title`,`content_notes`,`volume_edition`,`pages_times`,`performer`,`local_control_key`,`creation_date`,`last_modified`,`url`,`mimetype`,`home_library`,`item_group`,`item_type`,`item_icon`,`ISBN`,`ISSN`,`OCLC`,`status`,`material_type`,`publisher`,`availability`,`pages_times_range`,`pages_times_total`,`pages_times_used`) VALUES
(133509,"A confession / Chapter 5, 6, 7, and 8","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 34-51 (18 of 238)","","","2008-03-06","2010-03-18","3b/3bf97d4145a5737afdca939ffd106e33_133509.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","34-51","238","18"),
(133510,"A confession / Chapter 9, 10, 11, 12, 13, 14, 15, and 16","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 52-80 (29 of 238)","","","2008-03-06","2010-03-18","b4/b48ea3568ff4698304846c68bc247b13_133510.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","52-80","238","29"),
(134034,"A confession / Chapter 1, 2, and 3","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 19-29 (11 of 238)","","","2008-03-31","2010-03-23","2c/2c6eeba88b7d42f2fb2babe7c962b90b_134034.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","19-29","238","11"),
(134044,"Confession / Chapter 4","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 30-33 (4 of 238)","","","2008-03-31","2010-03-18","f1/f16978a701d39a62b6514ef1be4029ca_134044.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","30-33","238","4"),
(152535,"Gloria","Muriel Gardiner","1985","The Deadly Innocents","","","24-46 (23 of 190)","","","2009-12-02","2009-12-02","32/327a9518fd75746bf16e85d1ab31f4df_152535.pdf",1,0,"ELECTRONIC","ITEM","","0300033060","","","ACTIVE","BOOK_PORTION","","","24-46","190","23"),
(152536,"Peter","Muriel Gardiner","1985","The Deadly Innocents","","","3-23 (21 of 190)","","","2009-12-02","2009-12-02","3d/3db534f67c940253944bfa0f96c2530c_152536.pdf",1,0,"ELECTRONIC","ITEM","","0300033060","","","ACTIVE","BOOK_PORTION","","","3-23","190","21"),
(152537,"Tom","Muriel Gardiner","1985","The Deadly Innocents","","","95-128 (34 of 190)","","","2009-12-02","2009-12-02","f3/f3fac367105e7991985b0ebf63df8aae_152537.pdf",1,0,"ELECTRONIC","ITEM","","0300033060","","","ACTIVE","BOOK_PORTION","","","95-128","190","34"),
(157144,"The Caucasian Chalk Circle","Brecht Bertolt","New York : Vintage Books, 1975, ©1974","Bertolt Brecht Collected Plays","","Volume 7","pp. 136-229 (94 of 443)","","","2010-04-12","2010-04-12","8b/8bba98022a279beb5eb823147b5d6419_157144.pdf",1,0,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0394712161","","","ACTIVE","BOOK_PORTION","","","136-229","443","94"),
(101845,"Black portraiture in American fiction / Stock characters / Chapter 2","Starke, Catherine Juanita","New York : Basic Books, c1971.","Black portraiture in American fiction: stock characters, archetypes, and individuals","","1971","pp. 29-72 (44 of 280)","","","2006-03-03","2010-03-16","ba/baf32f03df6fc0f4b3cb5128db67e92c_101845.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","046500699","","199973","ACTIVE","BOOK_PORTION","","","29-72","280","44"),
(101848,"Black portraiture in American fiction / Contexts / Chapter 1","Starke, Catherine Juanita","New York : Basic Books, c1971.","Black portraiture in American fiction: stock characters, archetypes, and individuals","","1971","pp. 16-25 (10 of 280)","","","2006-03-03","2010-03-17","77/775478d4e99060598ae97f4ed1f045aa_101848.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","046500699","","199973","ACTIVE","BOOK_PORTION","","","16-25","280","10"),
(101849,"Black portraiture in American fiction / Archetypal patterns / Chapter 3","Starke, Catherine Juanita","New York : Basic Books, c1971.","Black portraiture in American fiction: stock characters, archetypes, and individuals","","1971","pp. 125-137 (13 of 280)","","","2006-03-03","2010-03-16","36/36ceb5bbefe955e6abbc5f6f6bef8b93_101849.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","046500699","","199973","ACTIVE","BOOK_PORTION","","","125-137","280","13"),
(25749,"Cognitive psychology and information processing / The information-processing paradigm / Chapter 4","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 88-129 (42 of 573)","","","2004-11-22","2010-04-04","0a/0a54e0219783b474110bed694dbd3c22_25749.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","88-129","573","42"),
(25750,"Cognitive psychology and information processing / Contributions of other disciplines to information-processing psychology / Chapter 3","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 60-87 (28 of 573)","","","2004-11-22","2010-04-04","b8/b838a5eb7be31678202729136913fc8e_25750.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","60-87","573","28"),
(25751,"Cognitive psychology and information processing / Psychologys contribution to the information-processing paradigm / Chapter 2","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 35-59 (25 of 573)","","","2004-11-22","2010-04-02","b7/b7bbcf87f7c7f7bc17ac6bc075e881d6_25751.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","35-59","573","25"),
(25752,"Cognitive psychology and information processing / Science and paradigms: The premises of this book / Chapter 1","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 1-34 (34 of 573)","","","2004-11-22","2010-04-02","f4/f4bb7ba318421fb353c3d51a4a43b2cd_25752.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","1-34","573","34"),
(142752,"Hurrian Myths","Hoffner, Harry A.","Atlanta, GA : Scholars Press, c1998.","Hittite Myths","","","pp. 40-81 (42 of 120)","","","2009-01-08","2010-04-02","49/49c190ce770409fdf46f7b1cc5ec6b46_142754.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0788504886","","","ACTIVE","BOOK_PORTION","","","40-81","120","42");
(142753,"Hurrian Myths II","Hoffner, Harry A.","Atlanta, GA : Scholars Press, c1998.","Hittite Myths","","","pp. 40-81 (42 of 120)","","","2009-01-08","2010-04-02","49/49c190ce770409fdf46f7b1cc5ec6b46_142754.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0788504881","","","ACTIVE","BOOK_PORTION","","","40-81","120","42");

INSERT INTO `reserves` (`reserve_id`, `course_instance_id`, `item_id`, `activation_date`, `expiration`, `status`, `date_created`, `last_modified`, `requested_loan_period`, `parent_id`, `copyright_status`) VALUES
(412602, 57900, 133509, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'NEW'),
(412603, 57900, 133510, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'NEW'),
(412600, 57900, 134034, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'NEW'),
(412601, 57900, 134044, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'NEW'),
(405012, 57900, 152535, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'DENIED'),
(405013, 57900, 152536, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'DENIED'),
(405014, 57900, 152537, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'DENIED'),
(419683, 57900, 157144, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'NEW'),
(411007, 57900, 101845, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'PENDING'),
(411008, 57900, 101848, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'PENDING'),
(411009, 57900, 101849, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'PENDING'),
(407981, 57900, 25749, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'ACCEPTED'),
(407984, 57900, 25750, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'ACCEPTED'),
(407983, 57900, 25751, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'ACCEPTED'),
(407982, 57900, 25752, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'ACCEPTED'),
(371613, 57900, 142752, '2009-12-01', '2010-05-16', 'ACTIVE', '2010-03-17', '2010-04-22', NULL, NULL, 'PENDING');

INSERT INTO `course_instances` (`course_instance_id`, `primary_course_alias_id`, `term`, `year`, `activation_date`, `expiration_date`, `status`, `enrollment`, `reviewed_date`, `reviewed_by`) VALUES
(60904, 57900, 'SPRING', 2010, '2009-12-01', '2010-05-16', 'ACTIVE', 'OPEN', NULL, NULL);

INSERT INTO `course_aliases` (`course_alias_id`, `course_id`, `course_instance_id`, `course_name`, `section`, `registrar_key`, `override_feed`) VALUES
(60904, 17823, 57900, 'Test Course', 'AAAA', NULL, 0);

INSERT INTO `courses` (`course_id`, `department_id`, `course_number`, `uniform_title`, `old_id`) VALUES
(17823, 129, '129', 'Test Copyright Course Title', NULL);
