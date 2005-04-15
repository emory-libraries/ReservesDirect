<?php
/* Feed2JS : RSS feed to JavaScript Configuration include

	Use this include to establish server specific paths
	and other common functions used by the feed2js.php
	
	See main script for all the gory details
	
	created 10.sep.2004
*/


// MAGPIE SETUP ----------------------------------------------------

// define Magpie variables
        $chan = 'title';
        $num = 250;
        $desc = 1;
        $date = 'n';
        $targ = 'n';
        $html = 'a';

// Define path to Magpie files and load library
// The easiest setup is to put the 4 Magpie include
// files in the same directory:
// define('MAGPIE_DIR', './')

// Otherwise, provide a full valid file path to the directory
// where magpie sites

define('MAGPIE_DIR', $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']) . '/magpie/');

// access magpie libraries
require_once(MAGPIE_DIR.'rss_fetch.inc');
require_once(MAGPIE_DIR.'rss_utils.inc');

// value of 2 optionally show lots of debugging info but breaks JavaScript
// This should be set to 0 unless debugging
define('MAGPIE_DEBUG', 0);

// Define cache age in seconds.
define('MAGPIE_CACHE_AGE', 60*15);

// OTHER SETTIINGS ----------------------------------------------
// Output spec for item date string if used
// see http://www.php.net/manual/en/function.date.php
$date_format = "F d, Y h:i:s a";


// Utility to remove return characters from strings that might
// pollute JavaScript commands. While we are at it, substitute 
// valid single quotes as well and get rid of any escaped quote
// characters
function strip_returns ($text, $linefeed=" ") {
	$subquotes = ereg_replace("&apos;", "'", stripslashes($text));
	return ereg_replace("(\r\n|\n|\r)", $linefeed, $subquotes);
}

?>
