-- phpMyAdmin SQL Dump
-- version 2.7.0-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jun 17, 2009 at 04:41 PM
-- Server version: 4.1.16
-- PHP Version: 5.2.3
-- 
-- Database: `reserves`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `access`
-- 

DROP TABLE IF EXISTS access;
CREATE TABLE IF NOT EXISTS access (
  access_id int(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  alias_id int(20) NOT NULL default '0',
  permission_level int(11) NOT NULL default '0',
  enrollment_status set('AUTOFEED','APPROVED','PENDING','DENIED') NOT NULL default 'PENDING',
  autofeed_run_indicator varchar(20) default NULL,
  PRIMARY KEY  (access_id),
  UNIQUE KEY user_ca (user_id,alias_id),
  KEY alias_id (alias_id),
  KEY permission_level (permission_level),
  KEY user_id_ndx (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `circ_rules`
-- 

DROP TABLE IF EXISTS circ_rules;
CREATE TABLE IF NOT EXISTS circ_rules (
  id int(11) NOT NULL auto_increment,
  circ_rule varchar(50) NOT NULL default '',
  alt_circ_rule varchar(50) NOT NULL default '',
  default_selected set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `course_aliases`
-- 

DROP TABLE IF EXISTS course_aliases;
CREATE TABLE IF NOT EXISTS course_aliases (
  course_alias_id bigint(20) NOT NULL auto_increment,
  course_id int(11) default NULL,
  course_instance_id bigint(20) default NULL,
  course_name text,
  section varchar(8) default NULL,
  registrar_key varchar(255) default NULL,
  override_feed tinyint(1) NOT NULL default '0',
  classnbr int(11) DEFAULT NULL COMMENT 'Class Number unique within term',  
  PRIMARY KEY  (course_alias_id),
  UNIQUE KEY `unique_course_instance` (`registrar_key`,`classnbr`),
  KEY course_id (course_id),
  KEY course_instance_id (course_instance_id),
  KEY course_name (course_name(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `course_instances`
-- 

DROP TABLE IF EXISTS course_instances;
CREATE TABLE IF NOT EXISTS course_instances (
  course_instance_id bigint(20) NOT NULL auto_increment,
  primary_course_alias_id bigint(20) default NULL,
  term varchar(12) NOT NULL default '',
  `year` int(11) NOT NULL default '0',
  activation_date date NOT NULL default '0000-00-00',
  expiration_date date NOT NULL default '0000-00-00',
  `status` set('ACTIVE','INACTIVE','IN PROGRESS','AUTOFEED','CANCELED') NOT NULL default '',
  enrollment set('OPEN','MODERATED','CLOSED') NOT NULL default 'OPEN',
  reviewed_date date default NULL COMMENT 'reviewed by staff for copyright compliance',
  reviewed_by int(11) default NULL COMMENT 'reviewed by staff for copyright compliance',
  PRIMARY KEY  (course_instance_id),
  KEY primary_course_alias_id (primary_course_alias_id),
  KEY term_year_idx (term,`year`),
  KEY `status` (`status`),
  KEY enrollment (enrollment),
  KEY activation_date_ndx (activation_date),
  KEY expiration_date_ndx (expiration_date)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `courses`
-- 

DROP TABLE IF EXISTS courses;
CREATE TABLE IF NOT EXISTS courses (
  course_id int(11) NOT NULL auto_increment,
  department_id int(11) NOT NULL default '0',
  course_number varchar(10) default NULL,
  uniform_title text NOT NULL,
  old_id int(11) default NULL,
  PRIMARY KEY  (course_id),
  KEY department_id (department_id),
  KEY old_id (old_id),
  KEY course_number (course_number),
  KEY uniform_title (uniform_title(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `departments`
-- 

DROP TABLE IF EXISTS departments;
CREATE TABLE IF NOT EXISTS departments (
  department_id int(11) NOT NULL auto_increment,
  abbreviation varchar(8) default NULL,
  name text,
  library_id int(11) NOT NULL default '1',
  `status` int(5) default NULL,
  PRIMARY KEY  (department_id),
  KEY library_id (library_id),
  KEY abbr_index (abbreviation)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `electronic_item_audit`
-- 

DROP TABLE IF EXISTS electronic_item_audit;
CREATE TABLE IF NOT EXISTS electronic_item_audit (
  audit_id int(20) NOT NULL auto_increment,
  item_id bigint(20) NOT NULL default '0',
  date_added date NOT NULL default '0000-00-00',
  added_by int(11) NOT NULL default '0',
  date_reviewed date default NULL,
  reviewed_by int(11) default NULL,
  PRIMARY KEY  (audit_id),
  KEY item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `help_art_tags`
-- 

DROP TABLE IF EXISTS help_art_tags;
CREATE TABLE IF NOT EXISTS help_art_tags (
  article_id int(8) unsigned default NULL,
  tag varchar(50) default NULL,
  user_id int(11) unsigned default NULL,
  UNIQUE KEY ndx_uniq_combo (article_id,tag,user_id),
  KEY user_id (user_id),
  FULLTEXT KEY tag (tag)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `help_art_tags`
-- 

INSERT INTO `help_art_tags` (`article_id`, `tag`, `user_id`) VALUES 
(5, 'email', 1073),
(5, 'phone', 1073),
(5, 'requests', 13139),
(6, 'phone', 1073),
(6, 'website', 1073),
(11, 'statistics', 4048),
(12, 'instructor', 4048),
(13, 'proxies', 4048),
(14, 'users', 4048),
(15, 'add', 4048),
(15, 'materials', 4048),
(15, 'new', 4048),
(16, 'links', 1073),
(16, 'url', 4048),
(17, 'fax', 4048),
(18, 'upload', 4048),
(19, 'search', 4048),
(20, 'copy', 4048),
(21, 'copyright', 4048),
(33, 'editing', 4048),
(40, 'contact', 1073),
(40, 'email', 1073);

-- --------------------------------------------------------

-- 
-- Table structure for table `help_art_to_art`
-- 

DROP TABLE IF EXISTS help_art_to_art;
CREATE TABLE IF NOT EXISTS help_art_to_art (
  article1_id int(8) unsigned default NULL,
  article2_id int(8) unsigned default NULL,
  relation_2to1 enum('child','sibling') default NULL,
  UNIQUE KEY ndx_uniq_combo (article1_id,article2_id),
  KEY article2_id (article2_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='In child relationship, article1 is always parent';

-- 
-- Dumping data for table `help_art_to_art`
-- 

INSERT INTO `help_art_to_art` (`article1_id`, `article2_id`, `relation_2to1`) VALUES 
(1, 2, 'child'),
(2, 3, 'sibling'),
(1, 4, 'child'),
(5, 6, 'sibling'),
(15, 20, 'sibling'),
(15, 38, 'sibling'),
(15, 33, 'sibling'),
(15, 21, 'sibling'),
(15, 28, 'sibling'),
(15, 19, 'child'),
(15, 17, 'child'),
(15, 18, 'child'),
(15, 16, 'child'),
(16, 18, 'sibling'),
(16, 19, 'sibling'),
(16, 17, 'sibling'),
(17, 18, 'sibling'),
(17, 19, 'sibling'),
(18, 19, 'sibling'),
(20, 38, 'sibling'),
(20, 33, 'sibling'),
(21, 19, 'sibling'),
(21, 16, 'sibling'),
(21, 18, 'sibling'),
(21, 17, 'sibling'),
(28, 36, 'sibling'),
(36, 35, 'sibling'),
(36, 39, 'sibling'),
(36, 34, 'sibling'),
(35, 39, 'sibling'),
(35, 34, 'sibling'),
(39, 34, 'sibling'),
(33, 38, 'sibling'),
(33, 35, 'child'),
(23, 38, 'child'),
(23, 20, 'child'),
(23, 33, 'child'),
(23, 15, 'child'),
(23, 39, 'child'),
(23, 34, 'child'),
(23, 35, 'child'),
(23, 36, 'child'),
(23, 30, 'sibling'),
(23, 29, 'sibling'),
(23, 12, 'sibling'),
(23, 13, 'sibling'),
(13, 12, 'sibling'),
(22, 23, 'child'),
(22, 25, 'child'),
(22, 26, 'child'),
(22, 31, 'child'),
(22, 32, 'child'),
(22, 30, 'sibling'),
(22, 27, 'sibling'),
(27, 30, 'sibling'),
(14, 13, 'child'),
(14, 12, 'child'),
(25, 26, 'sibling'),
(25, 31, 'sibling'),
(25, 32, 'sibling'),
(26, 31, 'sibling'),
(26, 32, 'sibling'),
(31, 32, 'sibling'),
(40, 5, 'sibling'),
(41, 33, 'sibling'),
(41, 38, 'sibling');

-- --------------------------------------------------------

-- 
-- Table structure for table `help_art_to_role`
-- 

DROP TABLE IF EXISTS help_art_to_role;
CREATE TABLE IF NOT EXISTS help_art_to_role (
  article_id int(8) unsigned default NULL,
  permission_level tinyint(2) unsigned default NULL,
  can_view tinyint(1) NOT NULL default '1',
  can_edit tinyint(1) NOT NULL default '0',
  UNIQUE KEY ndx_uniq_combo (article_id,permission_level),
  KEY permission_level (permission_level)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Specifies if specific permission-level may view/edit the art';

-- 
-- Dumping data for table `help_art_to_role`
-- 

INSERT INTO `help_art_to_role` (`article_id`, `permission_level`, `can_view`, `can_edit`) VALUES 
(15, 1, 0, 0),
(15, 0, 0, 0),
(14, 3, 1, 0),
(14, 2, 1, 0),
(13, 3, 1, 0),
(13, 2, 1, 0),
(13, 1, 0, 0),
(13, 0, 0, 0),
(6, 3, 1, 0),
(6, 2, 1, 0),
(12, 0, 0, 0),
(12, 1, 0, 0),
(12, 2, 1, 0),
(12, 3, 1, 0),
(6, 1, 1, 0),
(6, 0, 1, 0),
(14, 0, 0, 0),
(5, 3, 1, 0),
(5, 2, 1, 0),
(5, 1, 0, 0),
(5, 0, 0, 0),
(14, 1, 0, 0),
(11, 0, 0, 0),
(11, 1, 0, 0),
(11, 2, 1, 0),
(11, 3, 1, 0),
(15, 2, 1, 0),
(15, 3, 1, 0),
(16, 0, 0, 0),
(16, 1, 0, 0),
(16, 2, 1, 0),
(16, 3, 1, 0),
(17, 0, 0, 0),
(17, 1, 0, 0),
(17, 2, 1, 0),
(17, 3, 1, 0),
(18, 0, 0, 0),
(18, 1, 0, 0),
(18, 2, 1, 0),
(18, 3, 1, 0),
(19, 0, 0, 0),
(19, 1, 0, 0),
(19, 2, 1, 0),
(19, 3, 1, 0),
(20, 0, 0, 0),
(20, 1, 0, 0),
(20, 2, 1, 0),
(20, 3, 1, 0),
(21, 0, 1, 0),
(21, 1, 1, 0),
(21, 2, 1, 0),
(21, 3, 1, 0),
(22, 0, 1, 0),
(22, 1, 1, 0),
(22, 2, 1, 0),
(22, 3, 1, 0),
(23, 0, 0, 0),
(23, 1, 0, 0),
(23, 2, 1, 0),
(23, 3, 1, 0),
(40, 0, 1, 0),
(40, 1, 1, 0),
(40, 2, 1, 0),
(40, 3, 1, 0),
(25, 0, 0, 0),
(25, 1, 0, 0),
(25, 2, 1, 0),
(25, 3, 1, 0),
(26, 0, 0, 0),
(26, 1, 0, 0),
(26, 2, 1, 0),
(26, 3, 1, 0),
(27, 0, 1, 0),
(27, 1, 1, 0),
(27, 2, 1, 0),
(27, 3, 1, 0),
(28, 0, 0, 0),
(28, 1, 0, 0),
(28, 2, 1, 0),
(28, 3, 1, 0),
(29, 0, 0, 0),
(29, 1, 0, 0),
(29, 2, 1, 0),
(29, 3, 1, 0),
(30, 0, 0, 0),
(30, 1, 0, 0),
(30, 2, 1, 0),
(30, 3, 1, 0),
(31, 0, 0, 0),
(31, 1, 0, 0),
(31, 2, 1, 0),
(31, 3, 1, 0),
(32, 0, 0, 0),
(32, 1, 0, 0),
(32, 2, 1, 0),
(32, 3, 1, 0),
(33, 0, 0, 0),
(33, 1, 0, 0),
(33, 2, 1, 0),
(33, 3, 1, 0),
(34, 0, 0, 0),
(34, 1, 0, 0),
(34, 2, 1, 0),
(34, 3, 1, 0),
(35, 0, 0, 0),
(35, 1, 0, 0),
(35, 2, 1, 0),
(35, 3, 1, 0),
(36, 0, 0, 0),
(36, 1, 0, 0),
(36, 2, 1, 0),
(36, 3, 1, 0),
(38, 0, 0, 0),
(38, 1, 0, 0),
(38, 2, 1, 0),
(38, 3, 1, 0),
(39, 0, 0, 0),
(39, 1, 0, 0),
(39, 2, 1, 0),
(39, 3, 1, 0),
(41, 0, 0, 0),
(41, 1, 0, 0),
(41, 2, 1, 0),
(41, 3, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `help_articles`
-- 

DROP TABLE IF EXISTS help_articles;
CREATE TABLE IF NOT EXISTS help_articles (
  id int(8) unsigned NOT NULL auto_increment,
  category_id int(8) unsigned default NULL,
  title varchar(100) default NULL,
  body text,
  date_created date default NULL,
  date_modified date default NULL,
  PRIMARY KEY  (id),
  KEY category_id (category_id),
  FULLTEXT KEY body (body),
  FULLTEXT KEY ft_title_body (title,body)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `help_articles`
-- 

INSERT INTO `help_articles` (`id`, `category_id`, `title`, `body`, `date_created`, `date_modified`) VALUES 
(12, 3, 'Managing Instructors', 'Instructors have full ownership of their classes and may reactivate old classes, create new classes, and edit every aspect of the class and its associated reserve materials.<p>\r\n\r\nA class can have as many instructors as are necessary. This functionality is especially suited to team teaching situations. Each instructor has full access to the class to add and edit materials as well as all other class functions.<p>\r\n\r\n<b>To add an instructor to a class</b>:<br>\r\n1.  From the â€œMy Coursesâ€ tab, click on the class you wish to edit.<br>\r\n2.  On the "Edit Class" screen, click on the "Edit" link to the right of â€œInstructor(s).â€ On the next screen, current instructors are listed on the right, and you may choose a new instructor by searching for either Last Name or Username in the box on the left. <br>\r\n3.  In the drop-down menu, choose the instructor you would like to add and click "Add Instructor".<p>\r\n \r\n<b>To remove an instructor</b>:  <br>\r\nCheck the box next to the name of the instructor you wish to remove (on the right side of the screen) and click "Remove Instructor."<p>\r\nIf you do not see the instructor you are looking for in the drop-down menu, they are not yet in the system. Please contact your Reserves Desk staff to add them to the list of instructors. (woodruff.reserves@gmail.com)', '2006-11-30', '2006-11-30'),
(5, 4, 'Library Reserves Staff', 'Put contact information for reserves staff at your library/libraries here.', '2006-06-03', '2006-12-15'),
(6, 4, 'IT Help Desk', 'Put contact information for your technical help desk (usually campus IT, if users contact campus IT for help with passwords, etc.)', '2006-06-04', '2006-12-15'),
(11, 6, 'About Viewing Statistics', 'From the â€œView Statisticsâ€ tab, you have the option to look at the â€œItem View Log for a class.â€  When you click on this link, it will take you to a list of your classes.  Select the class you wish to view the statistics for and click â€œContinue.â€<p>\r\n \r\nThis screen will show you a list of the reserve items in the class that have been viewed (a student has clicked on the link to open that reserve item).  On the right-hand side, there are two columns: â€œTotal Hitsâ€ and â€œUnique Hits.â€<p>\r\n\r\nâ€œTotal Hitsâ€ tells you how many times that particular item has been opened.  â€œUnique Hitsâ€ tells you the number of individual students who have opened the item.  If a student has opened the item more than once, your â€œTotal Hitsâ€ column will be greater than your â€œUnique Hitsâ€ column.', '2006-11-30', '2006-11-30'),
(13, 3, 'Managing Proxies', 'Proxies are "assistants" to the class for the duration of the current semester or until they are removed by the instructor. They may do everything an instructor can do within a given class, except for create other proxies. Proxies only have access to the course or courses to which they are specifically assigned by an instructor or by Reserves staff.  You may have as many proxies as you like for any given class.<p>\r\n  \r\n<b>To add a Proxy</b>:<br>\r\n1.  From the â€œMy Coursesâ€ tab, click on the class you wish to edit.<br>\r\n2.  On the "Edit Class" screen, click on the "Edit" link next to â€œProxies.â€ On the next screen, current proxies are listed on the right.  You may search a list of all users in the system in the box on the left, using either Last Name or Username.<br>\r\n3.  The drop-down menu will fill with the names of users matching your search.<br>\r\n4.  Choose a name in the drop-down menu and click "Add Proxy"<p>\r\n\r\n<b>To remove a proxy</b>: <br>\r\n1.  Check the box next to the name of the proxy you wish to remove (on the right side of the screen) and click "Remove Selected Proxies."<p>\r\n\r\nA person must have logged into ReservesDirect at least once to be available to be made a proxy. If the name of the person you are looking for does not appear in your search results, please ask the person to log into the system.', '2006-11-30', '2006-11-30'),
(14, 3, 'Managing Users', 'The Manage Users tab allows you to do three things: manage your own user profile (name and email address); and add or delete proxies from your classes. Clicking on either the "Add Proxy" or "Delete Proxy" links on this page will take you to a screen that asks you to choose one of your current classes. You will then be taken to the Add/Remove proxy screen. For more about how to use this function, consult the "Managing Proxies" article.', '2006-11-30', '2006-11-30'),
(15, 2, 'Adding New Materials', 'There are two ways you can add new materials to a class.<p>\r\n\r\n--From the Edit Class page, click on the Ã¯Â¿Â½add new materialsÃ¯Â¿Â½ link above the reserves list for the class.<br>\r\n--From the Add a Reserve tab, select the class you want to add the reserve to and click Ã¯Â¿Â½continueÃ¯Â¿Â½.<p>\r\n  \r\nEither option will take you to the same screen.  From here, you can add a reserve by searching for the item, uploading a document, adding a URL, or faxing a document.  For details on how to use these options, click the appropriate link in the Ã¯Â¿Â½Follow-UpÃ¯Â¿Â½ help section.;', '2006-11-30', '2006-12-15'),
(16, 2, 'Adding a URL', 'You can also add a URL to your reserves list, which links the reserve list for your class to an item located on the web.  This feature is often used for items such as newspaper articles, scholarly articles on sites like JSTOR, or music and videos associated with a web address.<p>\r\n \r\nTo add a URL to your reserves list, choose the â€œAdd a URLâ€ link from the main â€œAdding New Materialsâ€ page.  Then simply type (or cut & paste) the web address from the address bar of your browser into the â€œURLâ€ box.<p>\r\n\r\n<b>Describing Your File</b><br>\r\nYou have a number of options for describing your file. The most basic descriptors are document title and author (title is required for display to students in the class). Title will display most prominently to students in the class; the other fields will appear below the title. When describing your documents, try to be as thorough as possible so that students can identify materials and cite them if necessary.<p>\r\n\r\nIf you are linking to one of multiple chapters from a book, it is generally best to put the chapter or movement title in the "Title" field and use the remaining fields to describe the main work that the selection is taken from.<p>\r\n\r\nThe various fields, such as Volume/Edition, can be used for different purposes depending on the type of document you are linking to (book chapter, journal article, musical work, etc.). The fields will accept whatever text you enter into them.<p>\r\n\r\nOnce you have finished describing your file, click â€œSave URL.â€  The URL will now appear as a reserve item in your class.  When a student clicks on the title of the item in the reserves list, she will be taken to the linked URL page.<p>\r\n\r\n\r\n<b>Copyright</b><br>\r\nReservesDirect operates under the Fair Use provision of United States copyright law.  By clicking â€œSave Documentâ€ you acknowledge that you have read the libraryâ€™s copyright notice and certify that to the best of your knowledge your use of the document falls within those guidelines.  Please be responsible in observing fair use when posting copyrighted materials. If you have questions about copyright and the materials you would like to use for your class, please contact the reserves staff at your library and consult your library''s copyright policy.<p>\r\n\r\n<u>It is always preferable to link to a journal article rather than downloading it to your computer and then uploading it to the Reserves Direct system.</u>  If you need assistance creating a link to an article or other item, please contact the reserves staff at your library.  (woodruff.reserves@gmail.com)', '2006-11-30', '2006-11-30'),
(17, 2, 'Faxing a Document', 'On the "Add Reserve" tab, when you click on the option to "Fax a Document", you will see a screen that gives you instructions for sending a fax to the ReservesDirect server. You do not have to go to this page first before you fax in a document; you can simply send faxes to 404-727-9089. The server will automatically convert your fax to an Adobe PDF and place it in a holding queue so that you can "claim" it and add it to one of your classes.<p>\r\n\r\n<b>Number of Faxed Pages & File Size</b><br>\r\nPlease limit your faxes to 25 sheets; faxes exceeding 25 will be split into separate files.<p>\r\n\r\n<b>Claiming Your Fax</b><br>\r\nYou must â€œclaimâ€ your fax once it has been transmitted to the system.  Faxed documents will remain available in the "claim" queue until midnight of the day that they are faxed in. At midnight all faxed documents are deleted.<p>\r\n\r\nTo view the fax queue, click on the button that says "After your fax has finished transmitting, click here." This page displays all faxes that are currently waiting to be claimed. <p>\r\n\r\nYou can identify your fax by the number you faxed it from as well as the time stamp; you may also click the "preview" button to view your document.<p>\r\n\r\nCheck the box next to your document and click the "Continue" button.<p>\r\n\r\n<b>Describing Your File</b><br>\r\nYou have a number of options for describing your file. The most basic descriptors are title and author (title is required for display to students in the class). Title will display most prominently to students in the class; the other fields will appear below the title. When describing your documents, try to be as thorough as possible so that students can identify materials and cite them if necessary.<p>\r\n \r\nIf you are faxing more than one chapter from a book, it is generally best to put the chapter or movement title in the "Title" field and use the remaining fields to describe the main work that the selection is taken from.<p>\r\n\r\nThe various fields, such as Volume/Edition, can be used for different purposes depending on the type of document you are faxing (book chapter, journal article, musical work, etc.). The fields will accept whatever text you enter into them.<p>\r\n\r\n<b>Copyright</b><br>\r\nReservesDirect operates under the Fair Use provision of United States copyright law.  By clicking â€œSave Documentâ€ you acknowledge that you have read the libraryâ€™s copyright notice and certify that to the best of your knowledge your use of the document falls within those guidelines.  Please be responsible in observing fair use when posting copyrighted materials. If you have questions about copyright and the materials you would like to use for your class, please contact the reserves staff at your library and consult your library''s copyright policy.  (woodruff.reserves@gmail.com)<p>\r\n\r\n<u>Always include the original copyright notice and publication information for all articles and book chapters that you fax to the system.</u>  We recommend copying the title page and the copyright page from the front of a book or article.<p>\r\n\r\n<b>Availability of Materials</b><br>\r\nFaxed documents are converted to PDF and placed in the "claim" queue as soon as they are received by the server; by the time your fax machine prints a confirmation sheet, your document should be available to claim in ReservesDirect.<p>\r\n\r\nYour document will not be available to students until you claim it.<br>\r\nDocuments are available to students immediately upon being claimed by you.', '2006-11-30', '2006-11-30'),
(18, 2, 'Uploading a Document', 'At the Upload a Document screen, you will see a form you must fill out with the citation information for the item you wish to upload.  Required fields are Document Title and File, but we encourage instructors to add as much bibliographic information as possible.  On this screen you can also write a note that will be appended to this item anytime it appears in the reserve list for the class.<p>\r\n\r\n \r\n<b>Select a File</b><br>\r\nSelecting a file to upload is much like attaching a file to an email. To select a file, simply click on the "Browse" button next to the "File" field in the upload form. This will open a file browser window on your computer. Navigate to the file you wish to upload and select it. This will automatically fill in the file path on the upload form.<p>\r\n\r\n<b>File Type and Size</b><br>\r\nYou may upload any file type to ReservesDirect. The most common file types currently in use are Adobe Acrobat (PDF), Word (.doc), Excel (.xls), and PowerPoint (.ppt). You may also upload other popular files such as JPEG, TIFF, and mP3, as well as SPSS data sets and much more.<p>\r\n\r\nIf you would like to put sound or video on reserve, please make use of our streaming media services. For audio, contact the Helibrun Music and Media Library, genmus@libcat1.cc.emory.edu. For video, contact Andy Ditzler, aditzle@emory.edu.<p>\r\n\r\nWhen uploading PDFs, we recommend keeping file size to about 2 megabytes (2 MB)--or about 25 clear, clean sheets--to optimize downloading and printing times.<p>\r\n\r\nReservesDirect will accept files up to 10 megabytes (10 MB) in size.<p>\r\n\r\n<b>Copyright</b><br>\r\nReservesDirect operates under the Fair Use provision of United States copyright law.  By clicking â€œSave Documentâ€ you acknowledge that you have read the libraryâ€™s copyright notice and certify that to the best of your knowledge your use of the document falls within those guidelines.  Please be responsible in observing fair use when posting copyrighted materials. If you have questions about copyright and the materials you would like to use for your class, please contact the reserves staff at your library and consult your library''s copyright policy.  (woodruff.reserves@gmail.com)\r\n<p>\r\n\r\n<u>Always include the original copyright notice and publication information for all articles and book chapters that you upload to the system.</u>  We recommend copying the title page and the copyright page from the front of a book or article.<p>\r\n\r\n<b>Describing Your File</b><br>\r\nYou have a number of options for describing your file. The most basic descriptors are title and author (title is required for display to students in the class). Title will display most prominently to students in the class; the other fields will appear below the title. When describing your documents, try to be as thorough as possible so that students can identify materials and cite them if necessary.<p>\r\n \r\nIf you are uploading more than one chapter from a book, it is generally best to put the chapter or movement title in the "Title" field and use the remaining fields to describe the main work that the selection is taken from.<p>\r\n\r\nThe various fields, such as Volume/Edition, can be used for different purposes depending on the type of document you are uploading (book chapter, journal article, musical work, etc.). The fields will accept whatever text you enter into them.<p>\r\n\r\n<b>Availability of Materials</b><br>\r\nUploaded materials are available to your students as soon as you click "Save Document" and receive a confirmation that the upload was successful.<p>\r\n\r\nFor information on how to manipulate your reserve items once they are uploaded (for example, sorting materials or hiding them from student view for a period of time), see the topics under "Editing a Class".', '2006-11-30', '2006-11-30'),
(19, 2, 'Searching for an Item', 'ReservesDirect has an Archive of over 70,000 items that have been digitized since electronic reserves began in 1999. The bulk of the content you will find here is articles and book chapters in PDF format, but the Archive also contains a good deal of streaming audio as well as documents in a variety of formats. You may search the entire Archive for materials suitable for your class.<p>\r\n\r\n \r\n<b>You may search for an item in 3 ways.  You may search for Archived Materials, by Instructor, or through EUCLID.</b><p>\r\n\r\nSearching the Archived Materials will show you reserves that have previously been posted to the Reserves Direct system by other instructors, which you can add directly to your class.  You can search for these materials by title or by author by using the drop-down menu.  The author/title search is a keyword search that will return materials that have your search terms anywhere in the author or title.  You may view the reserve item by clicking on it.  If your search returns more than 20 results, navigate multiple pages of results by using the "Next | Previous" links.<p>\r\n\r\nSearching for materials by instructor will show you all the reserves that instructor has previously posted to the system.  Selecting an instructorâ€™s name from the drop-down menu will take you to a list of their previous reserve materials, which you can then add directly to your class.  You may view the reserve item by clicking on it.  If your search returns more than 20 results, navigate multiple pages of results by using the "Next | Previous" links.<p>\r\n\r\nTo add an item to your class, click the check-box next to the item.  When you have selected all the items you wish to add to your class, click â€œAdd Selected Materials.â€  <b>Digital items</b> that you add to your class are immediately available for use by students, as long as the class is active for the current semester. <b>Physical materials</b> that you request by searching the archive generate a request that gets sent to Reserves staff for processing. Requests will show a status of "In Process" until the item has been retrieved by staff and successfully added to your class. Please allow time for staff to retrieve the item from the shelves and add it to your class. Some items may take longer to obtain if they must be recalled from another patron. If you have questions about the availability of an item, please contact Reserves staff at your library.<p>\r\n\r\nYou can also search for materials through EUCLID by clicking on the link at the bottom of the screen.  This will take you directly to the EUCLID system.<p>\r\n\r\nOnce you have located the item you wish to place on reserve, click the â€œRequestâ€ button at the bottom of the screen.  You will then be prompted to enter your Emory ID number.  At the following screen, click on â€œWoodruff Reserveâ€”Instructor Use Only.â€  Fill out the form on the next screen, including whether you would like the item to be placed on physical reserve at the reserve desk in the library and/or if you would like it to be placed on electronic reserve in the Reserves Direct system.  Then click â€œSubmit Request.â€', '2006-11-30', '2006-11-30'),
(20, 2, 'Copying Materials to Another Class', 'From the â€œEdit Classâ€ page that appears when you click on the name of one of your classes, you can select reserve items to copy to another class.<p>\r\n \r\nSelect the items you wish to copy by checking the box to the right of the item(s).  When you have finished selecting the materials to be copied, click the box that says â€œCopy Selected to Another Class.â€<p>\r\n\r\nOn the next page, choose the class into which you wish to copy the materials by selecting the bullet to the left of the class name.  Then click â€œContinue.â€<p>\r\n\r\nThe reserve items should then appear in the new class exactly as they appeared in the class from which you copied them.', '2006-11-30', '2006-12-04'),
(21, 2, 'Copyright Policy', 'ReservesDirect operates under the Fair Use provision of United States copyright law. <b>Please be responsible in observing fair use when posting copyrighted materials.</b> If you have questions about copyright and the materials you would like to use for your class, please contact the reserves staff at your library and consult your library''s copyright policy. (woodruff.reserves@gmail.com)<p>\r\n\r\n<u>Always include the original copyright notice and publication information for all articles and book chapters that you add to the system.</u>  We recommend copying the title page and the copyright page from the front of a book or article.<p>\r\n \r\nNB: It is always better to link to an article in a database (e.g. â€“ JSTOR, EBSCO, etc.) than to download it to your computer and re-upload it to the system.  If you need assistance creating a stable link to an article for your class, please contact the reserves staff.<p>\r\n\r\nFind out more about Fair Use \r\n<a href=http://web.library.emory.edu/services/circulation/reserves/copyright.html>here.</a>', '2006-11-30', '2006-11-30'),
(22, 5, 'Working with the My Courses tab/list', 'The "My Courses" tab is the main control screen for setting up your classes and managing your reserves lists. The â€œMy Coursesâ€ tab automatically displays all of the classes for which you are the instructor or proxy and any classes in which you are currently enrolled.  You can switch between viewing classes you are teaching and classes in which you are enrolled by clicking on the tabs.  You can also click on the circles next to each semester in the â€œYou are teaching:â€ tab to view your classes for that semester.<p>\r\n\r\nIn the â€œYou are enrolled in:â€ tab, you can join a class or leave a class by clicking on the appropriate option in the upper right-hand corner.<p>\r\n  \r\nIn the â€œYou are teaching:â€ tab, the icon next to each class identifies its current status in the system.  A pencil icon means that the class is active (students can enroll in the class and view the reserves).  A triangle/exclamation point icon means that the class has been created by the registrar but is not yet active in the system (students cannot enroll in the class or view the reserves).  An unavailable icon means that the course has been cancelled by the registrar.<p>\r\n\r\nThe right-hand column next to a class identifies its enrollment status.  If a class is listed as OPEN, students may look up the class and join it (in the Reserves Direct system onlyâ€”not through the registrar).  If the class is listed as MODERATED, students may request to be added to the class, but must be approved by the instructor or proxy before gaining access to it.  If the class is listed as CLOSED, students may not join the class and may not request to be added.<p>\r\n\r\nClicking on the name of a class in the â€œYou are teaching:â€ tab will redirect you to the Edit Class page.  Clicking on the name of a class in the â€œYou are enrolled in:â€ list will take you to the reserves list for that class.', '2006-11-30', '2006-11-30'),
(23, 5, 'Editing a Class', 'Clicking on the name of a class in the â€œYou are teaching:â€ tab will redirect you to the Edit Class page.  This screen shows you all the materials you have placed on reserve for the class.  This is the main screen you will use to edit, add, and remove materials and information pertaining to the class.  You can also view enrollment for the class by clicking on the â€œEnrollmentâ€ tab.', '2006-11-30', '2006-11-30'),
(25, 5, 'Creating a New Class', 'Creating a new class to add reserves to is a quick and easy process.<p>\r\n \r\n1.  Simply click on the "Create New Class" link.<br>\r\n2.  Choose the Department of the course from the drop-down menu and fill in the course number, section, and course name as they appear in OPUS.<br>\r\n3.  Select the semester you will be teaching the course (you may choose the current semester, next semester, or either of the 2 following semesters).<br>\r\n4.  Click "Create Course.<p>\r\n\r\nYour course has been established and you are now ready to begin adding materials. Follow the links provided to begin adding materials or just click on the "Add a Reserve" tab.', '2006-11-30', '2006-11-30'),
(26, 5, 'Reactivating a Class', 'At the end of each semester, all classes in ReservesDirect are archived for future use. You can view a list of all your past classes by clicking on the "Reactivate Class" link on the "My Courses" tab.<p>\r\n\r\n1.  To see what the reserve list for any of your courses was click on the "preview" link.<br> \r\n2.  After determining which class you would like to reactivate, select the bullet to the left of it and click â€œContinue.â€<br>\r\n3.  Select the name of the class you will be teaching in the current or upcoming semester.  If the class name does not appear, click the â€œCreate New Classâ€ link.<br>\r\n4.  On the next screen, by default, all of the boxes next to reserve items will be checked.  If the box next to an item is checked, it will reactivate with the class.  De-select any materials you do not want imported from the last time the class was taught.<p>\r\n\r\nYour class will now be set up to become active on the first day of the semester you specified with all imported class materials appearing as they did the last time you taught the class. Requests to add physical items that are in the reserve list will be sent to Reserves staff to process. Please allow some time for physical materials to be retrieved from the shelves or recalled from other patrons who may have the materials checked out.', '2006-11-30', '2006-11-30'),
(27, 5, 'Joining or Leaving a Class', '<b>Joining Classes</b><br> \r\nTo join a class, simply click on the "Join a Class" link on the right-hand side of the â€œYou are enrolled in:â€ screen under the â€œMy Coursesâ€ tab. Lookup your class by instructor or department.  When searching by instructor name, use the drop-down list to find instructor names matching your search.  The search results screen will display all classes active for the current semester. Find your class and select the bullet to the left of the class name.  You may preview classes by clicking on the â€œpreviewâ€ link on the right-hand side of the class name.  When you have selected your class, click â€œContinue.â€  If the class enrollment status is Open, it will appear immediately in the â€œYou are enrolled in:â€ list.  If the class enrollment is Moderated, the class will appear as pending approval in the â€œYou are enrolled in:â€ list.<p>\r\n\r\n<b>Leaving Classes</b><br> \r\nTo leave class you are enrolled in, just click on "Leave a Class" from the â€œYou are Enrolled in:â€ screen under the â€œMy Coursesâ€ tab.  Select the bullet to the left of the class you would like to leave.  Then click â€œContinue.â€ You may not leave any classes for which you are the instructor or proxy. These will automatically disappear from your list after the class expiration date.', '2006-11-30', '2006-11-30'),
(28, 5, 'Previewing a Class & Previewing Student View', 'Anytime you reactivate a class, export a class, or add a reserve, you have the option to preview the classes in the list by clicking on the â€œpreviewâ€ link to the right of the class.  Doing so will open a new window that shows you the reserve list for that class.<p>\r\n  \r\nTo preview the student view for a class you are teaching, select that class from the â€œMy Coursesâ€ list and then click the link in the upper right-hand corner that says â€œPreview Student View.â€  A new window will open that shows you the reserve list for that class as the students in the class will see it.', '2006-11-30', '2006-11-30'),
(29, 5, 'Editing Cross-Listings', 'You may crosslist classes under multiple course names and numbers to reflect the crosslistings that appear in OPUS. For example, ILA 135 may be crosslisted with ARTH 110.<p>\r\n\r\nTo create a crosslisting, click on the course you wish to edit from the â€œMy Coursesâ€ tab if one of the crosslisted classes already exists in ReservesDirect. If none of the crosslisted classes exist yet, first create a class and then select the class you just created from the â€œMy Coursesâ€ tab.<p>\r\n\r\nOn the "Edit Class" screen you will see a list of all current Crosslistings for the class.<p>\r\n\r\nClick on the â€œEditâ€ link next to â€œCrosslistingsâ€ to create a new crosslisting (or delete an old one).<p>\r\n\r\nThis screen will show you the primary listing for the class and all current crosslistings under the "Class Title and Crosslistings" box, where you may edit the title and course number/section of existing crosslistings.<p>\r\n\r\n<b>To add a new crosslisting</b>:<br> \r\n1.  Select the Department of the crosslisting from the drop-down menu in the "Add New Crosslisting" box and enter the number, section, and title of the crosslisting.<br> \r\n2.  Click on "Add Crosslisting"\r\nThe crosslisting will immediately be available for students to add to their list of classes.<p>\r\n\r\nNote: You may not delete a crosslisting if any students have added it to their list of classes. If you try to do so, an error message will appear informing you that students are currently "enrolled" in the class.', '2006-11-30', '2006-11-30'),
(30, 5, 'About Class Enrollment', 'To manage your class enrollment, click on the â€œManage Class Enrollmentâ€ link under the â€œMy Coursesâ€ tab.  Then select the class you wish to manage.  Alternatively, you may select a class from the â€œMy Coursesâ€ home and then click on the â€œEnrollmentâ€ tab located next to the â€œCourse Materialsâ€ tab.<p>\r\n  \r\nOn the â€œEnrollmentâ€ screen you can adjust the enrollment status of the class.  If a class is listed as OPEN, students may look up the class and join it (in the Reserves Direct system onlyâ€”not through the registrar).  If the class is listed as MODERATED, students may request to be added to the class, but must be approved by the instructor or proxy before gaining access to it.  If the class is listed as CLOSED, students may not join the class and may not request to be added.<p>\r\n\r\nTo add a student to the class (to give a student access to the reserves list for that class) simply type either the studentâ€™s name or Emory username into the box.  The box will produce a list of auto-completed names as you type; you may select the name from the drop-down list once you have located the student you wish to add.  Then click â€œAdd Student to Roll.â€  The student should immediately gain access to the class.<p>\r\n\r\nIf a student has requested to be added to the class, a notice will appear next to the â€œEnrollmentâ€ tab for that class that says â€œ! students requesting to join class !â€   At the â€œEnrollmentâ€ tab, you may approve or deny students to join the class.  You can approve or deny them individually or all at once by clicking the appropriate link.<p>\r\n\r\nYou may also remove students from the class by clicking the â€œremoveâ€ link next to their names under the â€œCurrently enrolled in this classâ€ list on the â€œEnrollmentâ€ tab.  If a student was automatically added to the class by the registrar, you cannot remove them from the class in Reserves Direct.', '2006-11-30', '2006-11-30'),
(31, 5, 'Exporting a Class', 'You may export your reserves list for any of your classes to Blackboard, Learnlink, or to a personal web page. Exporting your reserve list involves pasting a piece of code into the Blackboard class or page where you want your reserves list to appear. This creates a live feed of the list into your Blackboard class (through RSS), which is updated automatically. Any changes that are made to your list in ReservesDirect appear instantly in Blackboard.<p>\r\n\r\n<b>To export your class from the Manage Classes tab:</b><br>\r\n1.  Click on "Export Class" under the â€œMy Coursesâ€ tab.  (Alternatively, if you are in the â€œEdit Classâ€ view, you can click the link in the upper right-hand corner that says â€œExport Readings to Courseware.â€)  Select where you would like to export your reserves to (Blackboard, Learnlink, or a personal web page).<br> \r\n2.  Follow the on-screen instructions.', '2006-11-30', '2006-11-30'),
(32, 5, 'Exporting a Class to Spreadsheet', 'To export a class to spreadsheet, simply click on the name of the class from the â€œMy Coursesâ€ tab.  Then click on the link in the upper right-hand side of the page that says â€œExport Class to Spreadsheet.â€  A window will pop up asking whether you want to open or save the file.  Choose which one you want to do and the file will appear on your computer.', '2006-11-30', '2006-11-30'),
(33, 5, 'Editing Materials', 'FIX THIS ARTICLE FROM STAGING SITE <p>\r\n\r\nAfter you add an item to your class, you may edit all of the information associated with the item (author, title, etc.) and add notes to the item that will display to students in the class. Your reserves list is your to edit as you wish. The changes that you make', '2006-11-30', '2006-12-15'),
(34, 5, 'Sorting the Main List', 'You may sort the items in your reserve list by title, author, or by a custom order of your choosing, such as syllabus order. You may also add headings to further divide your class into syllabus order or subject/topic area.<p>\r\n\r\n<b>To sort by author or title</b>:<br>\r\n1.  From the â€œMy Coursesâ€ tab, select the class you wish to sort.  Then click on the "Sort Main List" link just above the reserve list.<br>\r\n2.  On the sort screen, click on "title" or "author" in the "Sort By" box. The list will automatically sort by title or author.<br>\r\n3.  Click "Save Order" to save the new order.<p>\r\n\r\n<b>To sort materials in a custom order</b>:<br>\r\n1.  Go to the sort screen as described in step 1 above.<br>\r\n2.  On the right side of your readings, you will see boxes with numbers inside of them. These are the "sort order" your readings appear in.<br> \r\n3.  To change the position of a reading, simply type the new position number into the text box and hit the "Tab" key or click in a new box. The order number of all of the readings will automatically update to reflect the change.<br>\r\n4.  Continue assigning numbers to the readings. If you make an error and would like to put the readings back in their original positions, you may do so by clicking "Reset to Saved Order."<br>\r\n5.  When you are finished, click "Save Order."<p>\r\n\r\n<b>Sorting headings</b>:<br>\r\n1.  Headings show up in the reserve list as a divider with text in it. To position your heading where you want it to appear (for instance, above all the Week 1 readings), click on the "sort" link. You may use the custom sort numbers (described above) to position the heading wherever you want.<br>\r\n2.  If you have added reserve items to a heading, they will automatically be moved with their associated heading whenever you rearrange the sort order of the heading.  To rearrange the sort order of items within a heading, click on the stacked-paper â€œSortâ€ icon next to the heading and sort the items as described above.', '2006-11-30', '2006-12-04'),
(35, 5, 'Adding Headings', 'Headings help organize your list of materials into topics or weeks. Headings can stand alone, or you can add items to them.  You can use any heading you want that you think would be useful to your students in identifying a group of readings that belong together, e.g. "Week 1" or "Byzantium".<p>  \r\n \r\nTo add a heading to your reserve list for a class, select that class from the â€œYou are Teaching:â€ list in the â€œMy Coursesâ€ tab and click on the link that says â€œAdd New Heading.â€  Type the name of the heading into the box and click â€œSave Heading.â€<p>\r\n\r\nTo add an item to a heading (like you would to a folder), go to the Edit Class screen, check the box next to the items to add to the heading, and click â€œEdit Selected.â€  On the â€œEdit Multiple Reservesâ€ screen, select which heading to add the materials to and click the "Submit" button.  Those items will then appear under the heading you have chosen.  When sorting the main list, any files that are listed under a separate heading will act like â€œfilesâ€ within the â€œfolderâ€ of the heading.<p>\r\n\r\nYou may edit an existing heading by selecting the check box next to it or by clicking the pencil icon next to it.  You may also add a note to headings in the same way you can add a note to reserve items.  For example, you could add a note that says â€œAll of the readings for this week are required.â€  You may continue adding as many headings as you like and positioning them in your list of materials.  For information on positioning headings in the reserve list, consult the â€œSorting the Main Listâ€ help article.', '2006-11-30', '2006-11-30'),
(36, 5, 'Hiding Items from Student View', 'You can "hide" items from student view and have them automatically appear to the whole class on a given date. For example, if you had a number of take-home tests for the class and only wanted the students to have access to them during the week of the test, you could upload all of the tests at once and set dates for each one to appear to the students. You can also hide readings or labs until the week they will be covered in class if you do not want students to work ahead. By default, all items have an Activation Date of the first day of the semester.<p>\r\n\r\n<b>To hide an item</b>:<br>  \r\n1.  From the Ã¯Â¿Â½My CoursesÃ¯Â¿Â½ tab, click on the class you wish to edit.<br>\r\n2.  This will take you to the "Edit Class" screen, where you will see a list of all your reserve items. Check the box(es) next to the item(s) you wish to hide and click Ã¯Â¿Â½Edit Selected.Ã¯Â¿Â½<br> \r\n3.  Enter the date you want the item to appear to students in the "Activation Date" Ã¯Â¿Â½From:Ã¯Â¿Â½ field in the format YYYY-MM-D. This date must fall during the current semester.  Enter the date you want the item to disappear in the Ã¯Â¿Â½To:Ã¯Â¿Â½ field.  Clicking the Ã¯Â¿Â½Reset DatesÃ¯Â¿Â½ link will reset the dates to the start and end of the semester during which that class takes place.''', '2006-11-30', '2006-12-15'),
(38, 5, 'Deleting Materials', 'From the Ã¯Â¿Â½Edit ClassÃ¯Â¿Â½ page that appears when you click on the name of one of your classes, you can select reserve items to delete.<p>\r\n  \r\nSelect the items you wish to delete by checking the box to the right of the item(s).  When you have finished selecting the materials to be deleted, click the box that says Ã¯Â¿Â½Delete Selected.Ã¯Â¿Â½<p>\r\n\r\nThe reserve items will then be permanently deleted from the class and will not appear in the reserve list.', '2006-11-30', '2006-12-13'),
(39, 5, 'Highlighting Reserve Links', 'To highlight reserve links in a class, select that class from the â€œYou are Teaching:â€ list in the â€œMy Coursesâ€ tab and click on the link that says â€œHighlight Reserve Links.â€  All of the links to your reserve items will then be highlighted in yellow.  You may use this function to help locate the URL for items in order to copy & paste them into an email, Blackboard, etc.', '2006-11-30', '2006-12-04'),
(40, 4, 'Contact the Reserves Desk', 'Put your main reserves contact point information here (listserv, phone, etc.)', '2006-12-06', '2006-12-15'),
(41, 5, 'Editing Multiple Items', 'If you wish to edit multiple reserve items at the same time, use the check-box option and choose all of the reserve items you wish to edit. Editing multiple items at the same time means that whatever values you enter into their information fields will appear identical for each of the individual items. When editing multiple items, you are only able to edit the following fields: Status, Active Dates, Heading, and Note.', '2006-12-12', '2006-12-13');

-- --------------------------------------------------------

-- 
-- Table structure for table `help_cat_to_role`
-- 

DROP TABLE IF EXISTS help_cat_to_role;
CREATE TABLE IF NOT EXISTS help_cat_to_role (
  category_id int(8) unsigned default NULL,
  permission_level tinyint(2) unsigned default NULL,
  can_view tinyint(1) NOT NULL default '0',
  can_edit tinyint(1) NOT NULL default '0',
  UNIQUE KEY ndx_uniq_combo (category_id,permission_level),
  KEY category_id (permission_level)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Specifies if specific permission-level may view/edit the art';

-- 
-- Dumping data for table `help_cat_to_role`
-- 

INSERT INTO `help_cat_to_role` (`category_id`, `permission_level`, `can_view`, `can_edit`) VALUES 
(3, 1, 0, 0),
(3, 2, 1, 0),
(3, 3, 1, 0),
(3, 0, 0, 0),
(4, 0, 1, 0),
(4, 1, 1, 0),
(4, 2, 1, 0),
(4, 3, 1, 0),
(1, 0, 1, 0),
(1, 1, 1, 0),
(1, 2, 1, 0),
(1, 3, 1, 0),
(5, 0, 1, 0),
(5, 1, 1, 0),
(5, 2, 1, 0),
(5, 3, 1, 0),
(2, 0, 1, 0),
(2, 1, 1, 0),
(2, 2, 1, 0),
(2, 3, 1, 0),
(6, 0, 0, 0),
(6, 1, 0, 0),
(6, 2, 1, 0),
(6, 3, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `help_categories`
-- 

DROP TABLE IF EXISTS help_categories;
CREATE TABLE IF NOT EXISTS help_categories (
  id smallint(4) unsigned NOT NULL auto_increment,
  title varchar(100) default NULL,
  description tinytext,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `help_categories`
-- 

INSERT INTO `help_categories` (`id`, `title`, `description`) VALUES 
(1, 'Tutorials', 'Tutorials'),
(2, 'Adding Materials', 'Instructions on how to add reserve materials to courses.'),
(3, 'Managing Users', 'Managing your user profile & managing proxies.'),
(4, 'Contacts', 'Articles about how to contact staff for help with the system.'),
(5, 'Managing Courses', 'An overview of the "My Courses" tab options and how to manage individual courses.'),
(6, 'Viewing Statistics', 'About viewing statistics for a course.');

-- --------------------------------------------------------

-- 
-- Table structure for table `hidden_readings`
-- 

DROP TABLE IF EXISTS hidden_readings;
CREATE TABLE IF NOT EXISTS hidden_readings (
  hidden_id int(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  reserve_id bigint(20) NOT NULL default '0',
  PRIMARY KEY  (hidden_id),
  UNIQUE KEY unique_constraint (user_id,reserve_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `ils_requests`
-- 

DROP TABLE IF EXISTS ils_requests;
CREATE TABLE IF NOT EXISTS ils_requests (
  request_id int(8) unsigned NOT NULL auto_increment,
  date_added date default NULL,
  ils_request_id varchar(16) default NULL,
  ils_control_key varchar(16) default NULL,
  user_net_id varchar(16) default NULL,
  user_ils_id varchar(16) default NULL,
  ils_course varchar(150) default NULL,
  requested_loan_period varchar(16) default NULL,
  PRIMARY KEY  (request_id),
  UNIQUE KEY ils_request_id (ils_request_id),
  UNIQUE KEY ils_control_key (ils_control_key)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `inst_loan_periods`
-- 

DROP TABLE IF EXISTS inst_loan_periods;
CREATE TABLE IF NOT EXISTS inst_loan_periods (
  loan_period_id bigint(20) NOT NULL auto_increment,
  loan_period varchar(255) NOT NULL default '',
  PRIMARY KEY  (loan_period_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `inst_loan_periods_libraries`
-- 

DROP TABLE IF EXISTS inst_loan_periods_libraries;
CREATE TABLE IF NOT EXISTS inst_loan_periods_libraries (
  id bigint(20) NOT NULL auto_increment,
  library_id bigint(20) NOT NULL default '0',
  loan_period_id bigint(20) NOT NULL default '0',
  `default` set('true','false') NOT NULL default 'false',
  PRIMARY KEY  (id),
  UNIQUE KEY unique_library_loan_period (library_id,loan_period_id),
  KEY library_id_ndx (library_id),
  KEY loan_period_id_ndx (loan_period_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `instructor_attributes`
-- 

DROP TABLE IF EXISTS instructor_attributes;
CREATE TABLE IF NOT EXISTS instructor_attributes (
  instructor_attribute_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  ils_user_id varchar(50) default NULL,
  ils_name varchar(75) default NULL,
  organizational_status varchar(25) default NULL,
  PRIMARY KEY  (instructor_attribute_id),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `item_upload_log`
-- 

DROP TABLE IF EXISTS item_upload_log;
CREATE TABLE IF NOT EXISTS item_upload_log (
  id bigint(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  course_instance_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  timestamp_uploaded timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  filesize varchar(10) NOT NULL default '',
  ipaddr varchar(15) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY user_id_ndx (user_id),
  KEY course_instance_id_ndx (course_instance_id),
  KEY item_id_ndx (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `items`
-- 

DROP TABLE IF EXISTS items;
CREATE TABLE IF NOT EXISTS items (
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
  url text,
  mimetype tinyint(4) NOT NULL default '7',
  home_library int(11) NOT NULL default '0',
  private_user_id int(11) default NULL,
  item_group set('MONOGRAPH','MULTIMEDIA','ELECTRONIC','HEADING') NOT NULL default '',
  item_type set('ITEM','HEADING') NOT NULL default 'ITEM',
  item_icon varchar(255) default NULL,
  ISBN varchar(13) default NULL,
  ISSN varchar(8) default NULL,
  OCLC varchar(9) default NULL,
  `status` set('ACTIVE','DENIED') NOT NULL default 'ACTIVE' COMMENT 'Show this item to students?',
  material_type varchar(255) default NULL,  
  publisher varchar(255) default NULL,       
  availability tinyint(1) default NULL COMMENT 'boolean: 1 or 0', 
  pages_times_range varchar(255) default NULL,
  pages_times_used varchar(255) default NULL,
  pages_times_total varchar(255) default NULL,      
  PRIMARY KEY  (item_id),
  KEY private_user_id (private_user_id),
  KEY home_library (home_library),
  KEY mimetype (mimetype),
  KEY item_group (item_group),
  KEY controlKey (local_control_key),
  KEY ndx_title (title),
  KEY ndx_source (source),
  KEY ndx_content_notes (content_notes),
  KEY ndx_volume_edition (volume_edition),
  KEY ndx_pages_times (pages_times),
  KEY ndx_performer (performer),
  KEY ndx_url (url(255)),
  KEY ndx_author (author),
  KEY ndx_volume_title (volume_title)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `libraries`
-- 

DROP TABLE IF EXISTS libraries;
CREATE TABLE IF NOT EXISTS libraries (
  library_id int(11) NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  nickname varchar(15) NOT NULL default '',
  ils_prefix varchar(10) NOT NULL default '',
  reserve_desk varchar(50) NOT NULL default '',
  url text,
  contact_email varchar(255) default NULL,
  monograph_library_id int(11) NOT NULL default '0',
  multimedia_library_id int(11) NOT NULL default '0',
  copyright_library_id int(11) default NULL,
  PRIMARY KEY  (library_id),
  KEY monograph_library_id_ndx (monograph_library_id),
  KEY multimedia_library_id_ndx (multimedia_library_id),
  KEY copyright_library_id_ndx (copyright_library_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
INSERT INTO libraries( library_id, name, nickname, ils_prefix, reserve_desk, url, contact_email, monograph_library_id, multimedia_library_id )
VALUES (1, 'Change Me', 'Change Me', '', '', '', '', 1, 1 );


-- 
-- Table structure for table `mimetype_extensions`
-- 

DROP TABLE IF EXISTS mimetype_extensions;
CREATE TABLE IF NOT EXISTS mimetype_extensions (
  id int(11) NOT NULL auto_increment COMMENT 'primary key',
  mimetype_id int(11) NOT NULL default '0' COMMENT 'foreign key to mimetypes table',
  file_extension varchar(5) NOT NULL default '' COMMENT 'file extension',
  PRIMARY KEY  (id),
  UNIQUE KEY file_extension (file_extension),
  KEY mimetype_id (mimetype_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='allow storage of mulitple file extensions for each mimetype';

-- 
-- Dumping data for table `mimetype_extensions`
-- 

INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (1, 1, 'pdf');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (2, 2, 'ram');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (3, 3, 'mov');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (4, 4, 'doc');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (5, 5, 'xls');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (6, 6, 'ppt');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (7, 7, '');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (8, 4, 'docx');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (9, 5, 'xlsx');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (10, 6, 'pptx');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (11, 6, 'ppsx');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (12, 7, 'html');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (13, 7, 'htm');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (14, 7, 'xhtml');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (15, 8, 'jpeg');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (16, 8, 'jpg');
INSERT INTO mimetype_extensions (id, mimetype_id, file_extension) VALUES (17, 8, 'gif');
-- --------------------------------------------------------

-- 
-- Table structure for table `mimetypes`
-- 

DROP TABLE IF EXISTS mimetypes;
CREATE TABLE IF NOT EXISTS mimetypes (
  mimetype_id int(11) NOT NULL auto_increment,
  mimetype varchar(100) NOT NULL default '',
  helper_app_url text,
  helper_app_name text,
  helper_app_icon text,
  PRIMARY KEY  (mimetype_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `mimetypes`
-- 

INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (1, 'application/pdf', 'http://www.adobe.com/products/acrobat/readstep2.html', 'Adobe Acrobat Reader', 'images/doc_type_icons/doctype-pdf.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (2, 'audio/x-pn-realaudio', 'http://www.real.com/', 'RealPlayer', 'images/doc_type_icons/doctype-sound.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (3, 'video/quicktime', 'http://www.apple.com/quicktime/', 'Quicktime Player', 'images/doc_type_icons/doctype-movie.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (4, 'application/msword', 'http://office.microsoft.com/Assistance/9798/viewerscvt.aspx', 'Microsoft Word', 'images/doc_type_icons/doctype-text.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (5, 'application/vnd.ms-excel', 'http://office.microsoft.com/Assistance/9798/viewerscvt.aspx', 'Microsoft Excel', 'images/doc_type_icons/doctype-excel.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (6, 'application/vnd.ms-powerpoint', 'http://office.microsoft.com/Assistance/9798/viewerscvt.aspx', 'Microsoft Powerpoint', 'images/doc_type_icons/doctype-ppt.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (7, 'text/html', NULL, 'Link', 'images/doc_type_icons/doctype-link.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (8, 'image/jpeg', NULL, 'Image', 'images/doc_type_icons/doctype-image.gif');

-- --------------------------------------------------------

-- 
-- Table structure for table `news`
-- 

DROP TABLE IF EXISTS news;
CREATE TABLE IF NOT EXISTS news (
  news_id bigint(20) NOT NULL auto_increment,
  news_text text NOT NULL COMMENT 'Text which will be displayed on all pages',
  font_class varchar(50) NOT NULL default '' COMMENT 'css class of text',
  permission_level set('0','1','2','3','4','5') default '',
  begin_time datetime default NULL,
  end_time datetime default NULL,
  sort_order int(11) NOT NULL default '0',
  PRIMARY KEY  (news_id),
  KEY permission_level (permission_level),
  KEY begin_time (begin_time),
  KEY end_time (end_time)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `not_trained`
-- 

DROP TABLE IF EXISTS not_trained;
CREATE TABLE IF NOT EXISTS not_trained (
  user_id int(11) NOT NULL default '0',
  permission_level int(11) NOT NULL default '0',
  PRIMARY KEY  (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `notes`
-- 

DROP TABLE IF EXISTS notes;
CREATE TABLE IF NOT EXISTS notes (
  note_id bigint(20) NOT NULL auto_increment,
  `type` varchar(25) NOT NULL default '',
  target_id bigint(20) NOT NULL default '0',
  note text NOT NULL,
  target_table varchar(50) NOT NULL default '',
  PRIMARY KEY  (note_id),
  KEY `type` (`type`),
  KEY target (target_table,target_id),
  KEY ndx_note (note(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `permissions_levels`
-- 

DROP TABLE IF EXISTS permissions_levels;
CREATE TABLE IF NOT EXISTS permissions_levels (
  permission_id int(11) NOT NULL default '0',
  label varchar(25) NOT NULL default '',
  PRIMARY KEY  (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `permissions_levels`
-- 

INSERT INTO `permissions_levels` (`permission_id`, `label`) VALUES 
(0, 'student'),
(1, 'custodian'),
(2, 'proxy'),
(3, 'instructor'),
(4, 'staff'),
(5, 'admin');

-- --------------------------------------------------------

-- 
-- Table structure for table `physical_copies`
-- 

DROP TABLE IF EXISTS physical_copies;
CREATE TABLE IF NOT EXISTS physical_copies (
  physical_copy_id int(11) NOT NULL auto_increment,
  reserve_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  `status` varchar(30) NOT NULL default '',
  call_number text,
  barcode varchar(15) default NULL,
  owning_library varchar(15) NOT NULL default '0',
  item_type varchar(30) default NULL,
  owner_user_id int(11) default NULL,
  PRIMARY KEY  (physical_copy_id),
  KEY reserves_id (reserve_id),
  KEY item_id (item_id),
  KEY `status` (`status`),
  KEY barcode (barcode),
  KEY item_type (item_type),
  KEY owner_user_id (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `proxied_hosts`
-- 

DROP TABLE IF EXISTS proxied_hosts;
CREATE TABLE IF NOT EXISTS proxied_hosts (
  id int(11) NOT NULL auto_increment COMMENT 'primary key',
  proxy_id int(11) NOT NULL default '0' COMMENT 'foreign key to proxy table',
  domain varchar(255) NOT NULL default '' COMMENT 'host domain',
  partial_match binary(1) NOT NULL default '0' COMMENT 'if 0 require exact match against domain',
  PRIMARY KEY  (id),
  UNIQUE KEY unique_domain (domain)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='list of host to be proxied';

-- --------------------------------------------------------

-- 
-- Table structure for table `proxies`
-- 

DROP TABLE IF EXISTS proxies;
CREATE TABLE IF NOT EXISTS proxies (
  id int(11) NOT NULL auto_increment COMMENT 'primary key',
  name varchar(50) NOT NULL default '' COMMENT 'display name',
  prefix varchar(255) NOT NULL default '' COMMENT 'url prefix',
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='proxies';

-- --------------------------------------------------------

-- 
-- Table structure for table `reports`
-- 

DROP TABLE IF EXISTS reports;
CREATE TABLE IF NOT EXISTS reports (
  report_id bigint(20) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  param_group set('term','department','class','term_lib','term_dates') default NULL,
  `sql` text NOT NULL,
  parameters varchar(255) default NULL,
  min_permissions int(11) NOT NULL default '4',
  sort_order int(11) NOT NULL default '0',
  cached tinyint(1) NOT NULL default '1' COMMENT 'boolean: 1 of 0',
  cache_refresh_delay int(4) NOT NULL default '6' COMMENT 'measured in hours',
  PRIMARY KEY  (report_id),
  KEY min_permissions (min_permissions)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `reports_cache`
-- 

DROP TABLE IF EXISTS reports_cache;
CREATE TABLE IF NOT EXISTS reports_cache (
  report_cache_id bigint(20) NOT NULL auto_increment,
  report_id bigint(20) default NULL COMMENT 'foreign key -- `reports`',
  params_cache text,
  report_cache longtext,
  last_modified timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (report_cache_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `requests`
-- 

DROP TABLE IF EXISTS requests;
CREATE TABLE IF NOT EXISTS requests (
  request_id bigint(20) NOT NULL auto_increment,
  reserve_id int(11) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  date_requested date NOT NULL default '0000-00-00',
  date_processed date default NULL,
  date_desired date default NULL,
  priority int(11) default NULL,
  course_instance_id bigint(20) NOT NULL default '0',
  max_enrollment int(11) default NULL COMMENT 'max enrollment as specified by instructor',
  `type` set('PHYSICAL','SCAN') NOT NULL default 'PHYSICAL',
  PRIMARY KEY  (request_id),
  KEY item_id (item_id),
  KEY user_id (user_id),
  KEY date_requested (date_requested),
  KEY date_desired (date_desired),
  KEY priority (priority),
  KEY course_instance_id (course_instance_id),
  KEY reserve_id (reserve_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `reserves`
-- 

DROP TABLE IF EXISTS reserves;
CREATE TABLE IF NOT EXISTS reserves (
  reserve_id bigint(20) NOT NULL auto_increment,
  course_instance_id bigint(20) NOT NULL default '0',
  item_id bigint(20) NOT NULL default '0',
  activation_date date NOT NULL default '0000-00-00',
  expiration date NOT NULL default '0000-00-00',
  `status` set('ACTIVE','INACTIVE','IN PROCESS','DENIED','SEARCHING STACKS','UNAVAILABLE','RECALLED','PURCHASING','RESPONSE NEEDED','SCANNING','COPYRIGHT REVIEW') NOT NULL default 'ACTIVE',
  copyright_status set('NEW', 'PENDING', 'ACCEPTED', 'DENIED') NOT NULL DEFAULT 'NEW' COMMENT 'Do we need/have permission from pub?',
  sort_order int(11) NOT NULL default '0',
  date_created date NOT NULL default '0000-00-00',
  last_modified date NOT NULL default '0000-00-00',
  requested_loan_period varchar(255) default NULL,
  parent_id bigint(20) default NULL,
  PRIMARY KEY  (reserve_id),
  UNIQUE KEY unique_constraint (course_instance_id,item_id),
  KEY reserves_sort_ci_idx (course_instance_id,sort_order),
  KEY item_id (item_id),
  KEY reserves_date_range_idx (activation_date,expiration),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `rightsholders`
-- 

DROP TABLE IF EXISTS rightsholders;
CREATE TABLE IF NOT EXISTS rightsholders (
  ISBN varchar(13) NOT NULL,
  name varchar(255) default NULL,
  contact_name varchar(255) default NULL,
  contact_email varchar(255) default NULL,
  fax varchar(50) default NULL,
  post_address varchar(255) default NULL,
  rights_url varchar(255) default NULL,
  policy_limit varchar(255) default NULL,
  PRIMARY KEY (ISBN)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `skins`
-- 

DROP TABLE IF EXISTS skins;
CREATE TABLE IF NOT EXISTS skins (
  id int(11) NOT NULL auto_increment,
  skin_name varchar(20) NOT NULL default '',
  skin_stylesheet text NOT NULL,
  default_selected set('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (id),
  UNIQUE KEY skin_name (skin_name)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `skins`
-- 

INSERT INTO `skins` (`id`, `skin_name`, `skin_stylesheet`, `default_selected`) VALUES 
(1, 'general', 'css/ReservesStyles.css', 'yes');

-- --------------------------------------------------------

-- 
-- Table structure for table `special_users`
-- 

DROP TABLE IF EXISTS special_users;
CREATE TABLE IF NOT EXISTS special_users (
  user_id int(11) NOT NULL default '0',
  `password` varchar(75) NOT NULL default '',
  expiration date default NULL,
  PRIMARY KEY  (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `special_users_audit`
-- 

DROP TABLE IF EXISTS special_users_audit;
CREATE TABLE IF NOT EXISTS special_users_audit (
  user_id bigint(20) NOT NULL default '0',
  creator_user_id bigint(20) NOT NULL default '0',
  date_created timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  email_sent_to varchar(255) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `staff_libraries`
-- 

DROP TABLE IF EXISTS staff_libraries;
CREATE TABLE IF NOT EXISTS staff_libraries (
  staff_library_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  library_id int(11) NOT NULL default '0',
  permission_level_id int(11) NOT NULL default '0',
  PRIMARY KEY  (staff_library_id),
  KEY user_id (user_id),
  KEY library_id (library_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `terms`
-- 

DROP TABLE IF EXISTS terms;
CREATE TABLE IF NOT EXISTS terms (
  term_id int(11) NOT NULL auto_increment,
  sort_order int(11) NOT NULL default '0',
  term_name varchar(100) NOT NULL default '',
  term_year varchar(4) NOT NULL default '',
  begin_date date NOT NULL default '0000-00-00',
  end_date date NOT NULL default '0000-00-00',
  PRIMARY KEY  (term_id),
  KEY sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `terms`
-- 

INSERT INTO `terms` (`term_id`, `sort_order`, `term_name`, `term_year`, `begin_date`, `end_date`) VALUES 
(1, 1, 'FALL', '2009', '2009-08-26', '2009-12-18'),
(2, 2, 'SPRING', '2010', '2010-01-01', '2010-05-31'),
(3, 3, 'SUMMER', '2010', '2010-05-15', '2010-08-16');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_view_log`
-- 

DROP TABLE IF EXISTS user_view_log;
CREATE TABLE IF NOT EXISTS user_view_log (
  id bigint(20) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  reserve_id bigint(20) NOT NULL default '0',
  timestamp_viewed timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY user_id (user_id),
  KEY reserve_id (reserve_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
  user_id int(11) NOT NULL auto_increment,
  username varchar(50) NOT NULL default '',
  first_name varchar(50) default NULL,
  last_name varchar(50) default NULL,
  email varchar(75) default NULL,
  external_user_key varchar(50) default NULL COMMENT 'Can be used to link users to external systems',
  dflt_permission_level int(11) NOT NULL default '0',
  last_login date NOT NULL default '0000-00-00',
  old_id int(11) default NULL,
  old_user_id int(11) default NULL,
  PRIMARY KEY  (user_id),
  UNIQUE KEY username (username),
  UNIQUE KEY external_user_key (external_user_key),
  KEY max_permission_level (dflt_permission_level),
  KEY old_id (old_id),
  KEY old_user_id (old_user_id),
  KEY last_name (last_name)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- View `course_book_usage`
-- 

-- view for calculating, given a course instance and an isbn, what total
-- percentage of that isbn have we stored for that course (to the degree
-- that we have that information). This would be a lot cleaner if we had
-- some kind of per-book information, but we haven't got that quite yet.
DROP VIEW IF EXISTS course_book_usage;
CREATE VIEW course_book_usage AS
  SELECT i.ISBN, ci.course_instance_id,
         SUM(CAST(i.pages_times_used AS DECIMAL) /
             CAST(i.pages_times_total AS DECIMAL)) * 100 AS percent_used
  FROM items i
    JOIN reserves r ON r.item_id = i.item_id
    JOIN course_instances ci ON r.course_instance_id = ci.course_instance_id
  WHERE i.ISBN IS NOT NULL AND
        i.ISBN <> '0' AND
        i.pages_times_used IS NOT NULL AND
        i.pages_times_total IS NOT NULL
  GROUP BY i.ISBN, ci.course_instance_id;
