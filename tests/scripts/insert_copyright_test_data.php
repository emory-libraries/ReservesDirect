#!/usr/local/bin/php -q
<?php
/**
 * This script will add 16 items of material type = "BOOK_PORTION" into the 
 * items and reserves tables for course_instance_id = 57900.   
 * 4 items added for ISBN: 0312214499 AUTHOR:Alexander Chasin
 * 3 items added for ISBN: 0140444734 AUTHOR:Tolstoy, Leo
 * 3 items added for ISBN: 0300033060 AUTHOR:Muriel Gardiner
 * 1 item  added for ISBN: 0394712161 AUTHOR:Brecht Bertolt (21% of book used)
 * 3 items added for ISBN: 046500699  AUTHOR:Starke, Catherine Juanita
 * 4 items added for ISBN: 047026649  AUTHOR:Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.
 * 1 item  added for ISBN: 0788504886 AUTHOR:Hoffner, Harry A. (35% of book used)
 * This script is to be run from the rd home directory. 
*******************************************************************************/
  require_once("config_loc.inc.php");
  require_once("secure/config.inc.php");
  
  $course_instance_id = 57900;

  $items_table_data = array(
    array("A confession / Chapter 5, 6, 7, and 8","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 34-51 (18 of 238)","","","2008-03-06","2010-03-18","3b/3bf97d4145a5737afdca939ffd106e33_133509.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","34-51","238","18"),
    array("A confession / Chapter 9, 10, 11, 12, 13, 14, 15, and 16","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 52-80 (29 of 238)","","","2008-03-06","2010-03-18","b4/b48ea3568ff4698304846c68bc247b13_133510.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","52-80","238","29"),
    array("A confession / Chapter 1, 2, and 3","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 19-29 (11 of 238)","","","2008-03-31","2010-03-23","2c/2c6eeba88b7d42f2fb2babe7c962b90b_134034.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","19-29","238","11"),
    array("Confession / Chapter 4","Tolstoy, Leo","Harmondsworth, Middlesex, England ; New York, N.Y., U.S.A. : Penguin Books, c1987.","A confession and other religious writings / translated with an introduction by Jane Kentish","","1987","pp. 30-33 (4 of 238)","","","2008-03-31","2010-03-18","f1/f16978a701d39a62b6514ef1be4029ca_134044.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0140444734","","17217493","ACTIVE","BOOK_PORTION","","","30-33","238","4"),
    array("Gloria","Muriel Gardiner","1985","The Deadly Innocents","","","24-46 (23 of 190)","","","2009-12-02","2009-12-02","32/327a9518fd75746bf16e85d1ab31f4df_152535.pdf",1,0,"ELECTRONIC","ITEM","","0300033060","","","ACTIVE","BOOK_PORTION","","","24-46","190","23"),
    array("Peter","Muriel Gardiner","1985","The Deadly Innocents","","","3-23 (21 of 190)","","","2009-12-02","2009-12-02","3d/3db534f67c940253944bfa0f96c2530c_152536.pdf",1,0,"ELECTRONIC","ITEM","","0300033060","","","ACTIVE","BOOK_PORTION","","","3-23","190","21"),
    array("Tom","Muriel Gardiner","1985","The Deadly Innocents","","","95-128 (34 of 190)","","","2009-12-02","2009-12-02","f3/f3fac367105e7991985b0ebf63df8aae_152537.pdf",1,0,"ELECTRONIC","ITEM","","0300033060","","","ACTIVE","BOOK_PORTION","","","95-128","190","34"),
    array("The Caucasian Chalk Circle","Brecht Bertolt","New York : Vintage Books, 1975, ©1974","Bertolt Brecht Collected Plays","","Volume 7","pp. 136-229 (94 of 443)","","","2010-04-12","2010-04-12","8b/8bba98022a279beb5eb823147b5d6419_157144.pdf",1,0,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0394712161","","","ACTIVE","BOOK_PORTION","","","136-229","443","94"),
    array("Black portraiture in American fiction / Stock characters / Chapter 2","Starke, Catherine Juanita","New York : Basic Books, c1971.","Black portraiture in American fiction: stock characters, archetypes, and individuals","","1971","pp. 29-72 (44 of 280)","","","2006-03-03","2010-03-16","ba/baf32f03df6fc0f4b3cb5128db67e92c_101845.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","046500699","","199973","ACTIVE","BOOK_PORTION","","","29-72","280","44"),
    array("Black portraiture in American fiction / Contexts / Chapter 1","Starke, Catherine Juanita","New York : Basic Books, c1971.","Black portraiture in American fiction: stock characters, archetypes, and individuals","","1971","pp. 16-25 (10 of 280)","","","2006-03-03","2010-03-17","77/775478d4e99060598ae97f4ed1f045aa_101848.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","046500699","","199973","ACTIVE","BOOK_PORTION","","","16-25","280","10"),
    array("Black portraiture in American fiction / Archetypal patterns / Chapter 3","Starke, Catherine Juanita","New York : Basic Books, c1971.","Black portraiture in American fiction: stock characters, archetypes, and individuals","","1971","pp. 125-137 (13 of 280)","","","2006-03-03","2010-03-16","36/36ceb5bbefe955e6abbc5f6f6bef8b93_101849.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","046500699","","199973","ACTIVE","BOOK_PORTION","","","125-137","280","13"),
    array("Cognitive psychology and information processing / The information-processing paradigm / Chapter 4","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 88-129 (42 of 573)","","","2004-11-22","2010-04-04","0a/0a54e0219783b474110bed694dbd3c22_25749.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","88-129","573","42"),
    array("Cognitive psychology and information processing / Contributions of other disciplines to information-processing psychology / Chapter 3","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 60-87 (28 of 573)","","","2004-11-22","2010-04-04","b8/b838a5eb7be31678202729136913fc8e_25750.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","60-87","573","28"),
    array("Cognitive psychology and information processing / Psychologys contribution to the information-processing paradigm / Chapter 2","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 35-59 (25 of 573)","","","2004-11-22","2010-04-02","b7/b7bbcf87f7c7f7bc17ac6bc075e881d6_25751.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","35-59","573","25"),
    array("Cognitive psychology and information processing / Science and paradigms: The premises of this book / Chapter 1","Lachman, Roy; Lachman, Janet L.; Butterfield, Earl C.","Hillsdale, N.J. : Lawrence Erlbaum Associates ; 1979. New York distributed by Halsted Press, c1979.","Cognitive psychology and information processing : an introduction","","1979","pp. 1-34 (34 of 573)","","","2004-11-22","2010-04-02","f4/f4bb7ba318421fb353c3d51a4a43b2cd_25752.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","047026649","","5017863","ACTIVE","BOOK_PORTION","","","1-34","573","34"),
    array("Hurrian Myths","Hoffner, Harry A.","Atlanta, GA : Scholars Press, c1998.","Hittite Myths","","","pp. 40-81 (42 of 120)","","","2009-01-08","2010-04-02","49/49c190ce770409fdf46f7b1cc5ec6b46_142754.pdf",1,1,"ELECTRONIC","ITEM","images/doc_type_icons/doctype-pdf.gif","0788504886","","","ACTIVE","BOOK_PORTION","","","40-81","120","42"),
  );

  $g_dbConn->autoCommit(false);
  $count = 0;
  
  foreach ($items_table_data as $item_params) {

    $item_id = 0;
    $insert_items_query = "INSERT INTO items (title,author,source,volume_title,content_notes,volume_edition,pages_times,performer,local_control_key,creation_date,last_modified,url,mimetype,home_library,item_group,item_type,item_icon,ISBN,ISSN,OCLC,status,material_type,publisher,availability,pages_times_range,pages_times_total,pages_times_used) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";  
    $rs =& $g_dbConn->query($insert_items_query, $item_params);
    if (DB::isError($rs)) {
      $g_dbConn->rollback();
      echo $rs->getMessage(); 
      echo "DB error on INSERT INTO ITEMS\n";
      exit(1);
    }
    $sql_getlast = "SELECT LAST_INSERT_ID() FROM items";      
    $rv =& $g_dbConn->getOne($sql_getlast);
    if (DB::isError($rv)) {
      $g_dbConn->rollback();
      echo $rs->getMessage();       
      echo "DB error on SELECT LAST_INSERT_ID() FROM items\n";         
    }      
    $item_id = ($rv == 0) ? null :  $rv; 
  
    // use the last entered item_id as input when inserting in to the reserves table.
    $reserve_params = array($course_instance_id,$item_id,"2009-12-01","2010-05-16","ACTIVE","2010-03-17","2010-04-22");
    $insert_reserves_query = "INSERT INTO reserves (course_instance_id,item_id,activation_date,expiration,status,date_created,last_modified) VALUES (?,?,?,?,?,?,?)";  
    $rs =& $g_dbConn->query($insert_reserves_query, $reserve_params);
    if (DB::isError($rs)) {
      $g_dbConn->rollback();
      echo "DB error on INSERT INTO RESERVES\n";
      exit(1);
    } 
    $sql_getlast = "SELECT LAST_INSERT_ID() FROM reserves";      
    $rv =& $g_dbConn->getOne($sql_getlast);
    if (DB::isError($rv)) {
      $g_dbConn->rollback();
      echo $rs->getMessage();       
      echo "DB error on SELECT LAST_INSERT_ID() FROM reserves\n";         
    }   
    $reserve_id = ($rv == 0) ? null :  $rv; 
    
    echo "Inserted ITEM_ID = $item_id, RESERVE_ID = $reserve_id for TITLE = $item_params[0]\n";
    
  }
  echo "Done\n";
  $g_dbConn->commit();
  $g_dbConn->disconnect();
 
  exit(0);
?>
 
