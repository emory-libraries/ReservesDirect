<?php
  /**
  * @desc This AJAX function is invoked when an onChange occurs in the 
  * add/edit item form for field pages_time, used pages or total pages.
  * Purpose:
  * 1. If the calling onChange textbox field is the "times_pages" (range), 
  * then type=2 and proceed to calculate the used pages based on this range.
  * 2. Calculate usage percentage of book for the current item only.
  * 3. Calculate combined percentage of book usage will be calculated for this
  * ISBN using the current used & total page data, and then adding any remaining
  * items in the database for this particular course and ISBN.  
  * 
  * Also ensure that this current item data is not calculated twice for
  * when the current item already exists in the db during an edit item.
  * @return 
  *  first return the used page calculation (for when type=2),
  *  second return the percentages for the current item.
  *  third return the sum of used percentages for this course/ISBN.
  */ 
  
  set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/local/lib/php');
  require_once("secure/classes/pagesTime.class.php");
  require_once("secure/config.inc.php");
  require_once("secure/classes/item.class.php");
  
  $debug = false;
  // Here we set the input parameters based on the url $_REQUEST array.
  if (!empty($_REQUEST)) {
    $range_param=htmlspecialchars($_REQUEST['range']);  // Page Range
    $range_param=stripslashes($range_param);
    $used_param=htmlspecialchars($_REQUEST['used']);    // Used Pages
    $used_param=stripslashes($used_param); 
    $total_param=htmlspecialchars($_REQUEST['total']);  // Total pages in book
    $total_param=stripslashes($total_param); 
    $isbn_param=htmlspecialchars($_REQUEST['isbn']);    // ISBN
    $isbn_param=stripslashes($isbn_param);
    $type_param=htmlspecialchars($_REQUEST['type']);    // TYPE=1 do not calculate used pages
    $type_param=stripslashes($type_param);              // TYPE=2 calculate used pages
    $item_param=htmlspecialchars($_REQUEST['item']); 
    $item_param=stripslashes($item_param);        
    $ci_param=htmlspecialchars($_REQUEST['ci']);        // Course Instance ID
    $ci_param=stripslashes($ci_param);   
  }
  // Allow this script to be tested externally from the AJAX URL call.
  // (add) php AJAX_copyright.php "1-2" "" "300" "0140444734" "2" "" "57900" "debug"
  // (edit) php AJAX_copyright.php "2-4" "23" "190" "0300033060" "2" "156865" "57900" "debug"
  elseif (!empty($argv)) {
    $range_param= (isset($argv[1])) ? $argv[1] : "";
    $used_param= (isset($argv[2])) ? $argv[2] : "";
    $total_param= (isset($argv[3])) ? $argv[3] : "";
    $isbn_param= (isset($argv[4])) ? $argv[4] : "";
    $type_param= (isset($argv[5])) ? $argv[5] : "";
    $item_param = (isset($argv[6])) ? $argv[6] : "";    
    $ci_param = (isset($argv[7])) ? $argv[7] : "";
    $debug = (isset($argv[8])) ? true : false;    
  }  

  $input = array( 
        "range" => $range_param, 
        "used" => $used_param, 
        "total" => $total_param, 
        "isbn" => $isbn_param, 
        "type" => $type_param, 
        "item" => $item_param,         
        "ci" => $ci_param, 
        "debug" => $debug,                                     
        );
        
  echo calc_copyright($input);
    
  /**
   * return the range for the current item (it may be that the range has not been changed)
   * return the copyright percentage for the current item.
   * return the combined copyright percentage for all items with the same ISBN in this course. 
   * 
   * @param input_date The original date to be padded
   * @param padding The amount in days to pad the date.
   * @return ret  The adjusted date value.
   */
  function calc_copyright($in)
  {   
    global $g_dbConn;
          
    // The range is only calculated if call came from the 'Page ranges' onChange trigger.
    // Otherwise it uses the given 'Total pages used in book' parameter.
    $current_used = ($in['used'] == null) ? 0 :  $in['used']; 
    if ($in['type'] == 2) {
      $pagesTimes = new PagesTime(); 
      $pagesTimes->process_list($in['range']);
      if ($pagesTimes->pgs_used > 0)  {
        $current_used = $pagesTimes->pgs_used;
      }
    } 
    if ($in['debug']) echo "current_used = $current_used\n"; 
    
    // The cummulative_percentage is updated when the database is searched for 
    // all items in the given course with the same ISBN, and sums all the calculated 
    // percentages via (pages_times_used/pages_times_total)*100.
    // The goal is to be able to determine if the combined used percentage for this 
    // book in this particular course exceeds the limit.
    $cummulative_percentage = 0;
    $current_item_percentage = 0;
    if ($current_used > 0 && $in['total'] > 0) {
      $current_item_percentage = ($current_used/$in['total'])*100;
      $cummulative_percentage = $current_item_percentage;
    }
    
    // If there is no course_id or the ISBN is not define, don't bother querying the db.
    if ($in['ci'] > 0 && !empty($in['isbn'])) { 
      
      // Get all the used and total page values for this course and ISBN, except for current item id.
      $query = 'SELECT i.item_id, i.pages_times_used, i.pages_times_total FROM items as i
              LEFT JOIN reserves as r on r.item_id = i.item_id
              WHERE r.course_instance_id = ? and i.ISBN = ?';
      $params = array($in['ci'], $in['isbn']);
      $item = new item();
      $additional_book_percentages = $item->selectOverallBookUsage($query, $params, $in['item']);  
      $cummulative_percentage += $additional_book_percentages;
    }
    
    // There are two goals for the return value
    // First return the used page calculation (type=2)
    // Second return the percentage for this current item only.
    // Second return the sum of used percentages for this ISBN.
    $return_value = intval($current_used) . ";" . intval($current_item_percentage) . ";" . intval($cummulative_percentage);
    if ($in['debug']) echo "final return_value = $return_value\n";
    echo $return_value;
  }

?>
