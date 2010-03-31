#!/usr/local/bin/php -q
<?php
  /*
  This script will generate a sql file to alter the pages_times field in the items table.
  */  

  require_once 'secure/classes/pagesTime.class.php'; 

  echo "\nBEGIN migrate_pagestimes_update.php\n";

  set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');  
  require_once('secure/config.inc.php');

  global $dsn;
    
  $sql_filename = realpath(dirname(__FILE__)) . "/../db/2.5_pagestimes_update.sql";
  $csv_filename = realpath(dirname(__FILE__)) . "/../db/2.5_pagestimes_update.csv";
  
  $fsql = fopen($sql_filename, 'w');  // Write out all the sql statements for the update.
  $fcsv = fopen($csv_filename, 'w');  // send output to a csv file.
  fwrite($fcsv, "Processed\tPattern\tNew_Range\tNew_Used_Total\tNew_Total_In_Book\tOriginal\n");  
    
  $cleanup = new PagesTime();    

  $select_sql = "SELECT item_id, pages_times FROM items order by item_id ASC";

  $count = 0;
  $null_counter = 0;
  $rs = $g_dbConn->query($select_sql);
  $arr = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0, 50=>0, 98=>0, 99=>0);
  $skip = 0;
  $updated = 0;
  while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) 
  {
    // A progress bar for this script.
    $count++;
    if ($count % 1000 == 0)  {  // every 1000 records output a dot/period
      echo ".";            
      if ($count % 10000 == 0) {  // every 10000 records output progress.
        echo " " . $count . " records processed.\n";
      }
    }

    // Don't bother processing null values in the pages_times field
    if (empty($row['pages_times']) || is_null($row['pages_times']) ) {   
        if ($row['item_id'] == 971)  {
          echo "NULL This is 971  and pages_times = " . $row['pages_times'] . "\n";
        }      
      $null_counter++;
    }
    // Valid data, so perform cleanup.
    // UPDATE items set total_pages_times = NULL, used_pages_times = NULL,  pages_times = "" where item_id = 155204;
    else {
      $cleanup->process_data($row['pages_times']);
      if ($row['pages_times'] == '')  {
        echo "This is empty " . $row['item_id'] . " row pages_times = [" . $row['pages_times'] . "]\n";
      }
      $pat_arr[$cleanup->pattern]++;  // just for kicks, let's see which patterns were used.
      if ($cleanup->pgs_format == $row['pages_times'] && $cleanup->pgs_total == 0 && $cleanup->pgs_used == 0) {      
        // Nothing changed here.        
        $skip++; 
        $sql_stmt = 'UPDATE items SET ';

        $pos = strpos($row['pages_times'], "\"");
        if ($pos === false) {
          $sql_stmt .= 'pages_times_range = "' . $row['pages_times'] . '" ';            
        } else {  // if a quote is found in the data, then use a single quote delimiter.         
          $sql_stmt .= "pages_times_range = '" . $row['pages_times'] . "' ";
        }

        $sql_stmt .= 'WHERE item_id = ' . $row['item_id'] . ';';
        fwrite($fsql, $sql_stmt . "\n");   
        fwrite($fcsv, "NO\t\t$cleanup->pgs_format\t$cleanup->pgs_used\t$cleanup->pgs_total\t" . $row['pages_times'] . "\n");
      }
      else if (empty($cleanup->pgs_format) && empty($cleanup->pgs_total) && empty($cleanup->pgs_used)) {
        // Invalid data, such as " / " or  " 0 / 0 "  or  "selected chapters"
        //echo "Invalid data for item record = " . $row['pages_times'] . "\n"; 
      }
      else {      
        $updated++;
        $comma = false;        
        $sql_stmt = 'UPDATE items SET ';
        if (!empty($cleanup->pgs_format)) {        
          $sql_stmt .= 'pages_times_range = "' . $cleanup->pgs_format . '"';
          $comma = true;
        }
        if (!empty($cleanup->pgs_total)) {
          if ($comma) $sql_stmt .= ',';          
          $sql_stmt .= ' pages_times_total = "' . $cleanup->pgs_total . '"';
          $comma = true;          
        }      
        if (!empty($cleanup->pgs_used)) {  
          if ($comma) $sql_stmt .= ',';
          $sql_stmt .= ' pages_times_used = "' . $cleanup->pgs_used . '"';
        }
        $sql_stmt .= ' WHERE item_id = ' . $row['item_id'] . ';';
        fwrite($fsql, $sql_stmt . "\n");  
        fwrite($fcsv, "YES\t$cleanup->pattern\t$cleanup->pgs_format\t$cleanup->pgs_used\t$cleanup->pgs_total\t" . $row['pages_times'] . "\n");               
      }
    }
  }

  fclose($fsql);
  fclose($fcsv);  
  
  $result_msg = "\n\nDone processing.\n";
  $result_msg .= $null_counter . " NULL records.\n";
  $result_msg .=  $skip . " skipped records.\n";
  $result_msg .=  $updated . " updated records.\n";
  $result_msg .=  "==>  " . $count . " records in the items table were processed.\n";
  
  // OUTPUT RESULTS
  $result_msg .=  "\n";
  foreach ($pat_arr as  $key=>$value) { 
    $result_msg .=  "Processed pattern " . $key . "\t" . $value . " times.\n"; 
  } 
  
  $result_msg .= "\nCSV FILE: " . $csv_filename;
  $result_msg .= "\nSQL FILE: " . $sql_filename . "\n";   
  
  echo $result_msg;   
?>
