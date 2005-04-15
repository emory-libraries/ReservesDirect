<?php
/*  Feed2JS : RSS feed to JavaScript src file
	
	ABOUT
	This PHP script will take an RSS feed as a value of src="...."
	and return a JavaScript file that can be linked 
	remotely from any other web page. Output includes
	site title, link, and description as well as item site, link, and
	description with these outouts contolled by extra parameters.
	
	Developed by Alan Levine 13.may.2004
	http:/jade.mcli.dist.maricopa.edu/alan/
	
	This is a new version and re-qrite of original version
	"rss2js.php" now set up to to use the Magpaie RSS Parser
	which is capable of handling Atom Feeds.
	 
	USAGE:
	See http://jade.mcli.dist.maricopa.edu/feed/
	
	Local customization can be achieved via declarations of CSS for
	  div.rssbox (style for bounding box)
	  class.rss_title (style for title of feed)
	  class.rss_item, class.rss_item a (style for linked entry)
	  class.rss_date (style for date display)
	
	HISTORY
    I got tired of keeping this up to date, see:
      http://jade.mcli.dist.maricopa.edu/feed/index.php?s=history
    
    or the RSS feed:
      http://jade.mcli.dist.maricopa.edu/feed//content/feed2js.xml
      
      
	This makes use of the Magpie RSS parser 0.71 from
	 http://magpierss.sourceforge.net/
	 
	which should be downloaded and installed separately.
	
   ------------- small print ---------------------------------------
	GNU General Public License 
	Copyright (C) 2004 Alan Levine
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details
	http://www.gnu.org/licenses/gpl.html
	------------- small print ---------------------------------------

*/

// MAGPIE SETUP ----------------------------------------------------
// access configuration settings
require_once('feed2js_config.php');

//  check for utf encoding type

if ($_GET['utf'] == 'y') {
	define('MAGPIE_CACHE_DIR', MAGPIE_DIR . 'cache_utf8/');
	// chacrater encoding
	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');

} else {
	define('MAGPIE_CACHE_DIR', MAGPIE_DIR . 'cache/');
	
}

// GET VARIABLES ---------------------------------------------
// retrieve values from posted variables
//just in case PHP globals are off, extract variables from $_GET array

// If REGISTER GLOBALS is off these lines can be commented out
$src = $_GET['src'];
//$chan = $_GET['chan'];
//$num = $_GET['num'];
//$desc = $_GET['desc'];
//$date = $_GET['date'];
//$targ = $_GET['targ'];
//$html = $_GET['html'];



// ERROR TRAP ---------------------------------------------------
// Check variables and set default values if needed

// trap for missing src param, divert to an error page
if (!$src) header("Location:nosource.php");

// Create CONNECTION CONFIRM
// create output string for local javascript variable to let 
// browser know that the server has been contacted
$feedcheck_str = "feed2js_ck = true;\n\n";

// flag to show channel info
if (!$chan) $chan = 'n';

// variable to limit number of displayed items; default = 0 (show all, 20 is a safe bet to list a big list of feeds)
if (!$num) $num = 20;

// indicator to show item description,  0 = no; 1=all; n>1 = characters to display
// values of -1 indicate to displa item without the title as a link
// (default=0)
if (!$desc) $desc = 0;

// flag to show date of posts, values: no/yes (default=no)
if (!$date) $date = 'n';


// flag to open target window in new window; n = same window, y = new window,
// other = targeted window, 'popup' = call JavaScript function popupfeed to display
// in new window
// (default is n)


if (!$targ or $targ == 'n') {
	$target_window = ' target="_self"';
} elseif ($targ == 'y' ) {
	$target_window = ' target="_blank"';
} elseif ($targ == 'popup') {
	$target_window = ' target="popup" onClick="popupfeed(this.href);return false"';
} else {
	$target_window = ' target="' . $targ . '"';
}



// flag to show feed as full html output rather than JavaScript, used for alternative
// views for JavaScript-less users. 
//     y = display html only for non js browsers
//     n = default (JavaScript view)
//     a = display javascript output but allow HTML 
//     p  = display text only items but convert linefeeds to BR tags

// default setting for no conversion of linebreaks
$br = ' ';

if (!$html) $html = 'n';

if ($html == 'a') {
	$desc = 1;
} elseif ($html == 'p') {
	$br = '<br />';
}



// PARSE FEED and GENERATE OUTPUT -------------------------------
// This is where it all happens!

$rss = @fetch_rss( $src );

// begin javascript output string for channel info
$str= "document.write('<div class=\"rss_box\">');\n";


if (!$rss) {
	// error, nothing grabbed
	$str.= "document.write('<p class=\"rss_item\"><em>Error:</em> No data was found for RSS feed $src or no items are available for this feed. Please verify that the URL works first in your browser.</p></div>');\n";

} else {

	// we have a feed, so let's process
	if ($chan == 'y') {
	
		// output channel title and description	
		$str.= "document.write('<p class=\"rss_title\"><a class=\"rss_title\" href=\"" . trim($rss->channel['link']) . '"' . $target_window . ">" . addslashes(strip_returns($rss->channel['title'])) . "</a><br /><span class=\"rss_item\">" . addslashes(strip_returns($rss->channel['description'])) . "</span></p>');\n";
	
	} elseif ($chan == 'title') {
		// output title only
		$str.= "document.write('<p class=\"rss_title\"><a class=\"rss_title\" href=\"" . trim($rss->channel['link']) . '"' . $target_window . ">" . addslashes(strip_returns($rss->channel['title'])) . "</a></p>');\n";
	
	}	
	
	// begin item listing
	$str.= "document.write('<ul class=\"rss_items\">');\n";
		
	// Walk the items and process each one
	$all_items = array_slice($rss->items, 0, $num);
	
	foreach ( $all_items as $item ) {
		
		if ($item['link']) {
			// link url
			$my_url = addslashes($item['link']);
		} elseif  ($item['guid']) {
			//  feeds lacking item -> link
			$my_url = ($item['guid']);
		}
		
		
		if ($desc < 0) {
			$str.= "document.write('<li class=\"rss_item\">');\n";
			
		} elseif ($item['title']) {
			// format item title
			$my_title = addslashes(strip_returns($item['title']));
						
			// create a title attribute. thanks Seb!
			$title_str = substr(addslashes(strip_returns(strip_tags(($item['summary'])))), 0, 255) . '...'; 

			// write the title strng
			$str.= "document.write('<li class=\"rss_item\"><a class=\"rss_item\" href=\"" . trim($my_url) . "\" title=\"$title_str\"". $target_window . '>' . $my_title . "</a><br />');\n";

		} else {
			// if no title, build a link to tag on the description
			$str.= "document.write('<li class=\"rss_item\">');\n";
			$more_link = " <a class=\"rss_item\" href=\"" . trim($my_url) . '"' . $target_window . ">&laquo;details&raquo;</a>";
		}
	
		// print out date if option indicated and feed returns a value. 
		// Use the new date_timestamp function in Magpie 0.71
		if ($date == 'y') {
		
//   NOT working now on RSS 2.0 feeds, help Magpie!!		
//			$pretty_date = date($date_format, $item['date_timestamp']);


// use the old approach until Magpie is fixed
			$in_date = "";
			$rss_2_date = $item['pubdate'];
			$rss_1_date = $item['dc']['date'];
			$atom_date = $item['issued'];
			if ($atom_date != "") $in_date = parse_w3cdtf($atom_date);
			if ($rss_1_date != "") $in_date = parse_w3cdtf($rss_1_date);
			if ($rss_2_date != "") $in_date = strtotime($rss_2_date);
			if ($in_date == "") $in_date = time();	
			
			// format date
			
			if (strlen($rss_1_date) == 10) {
				// hack to catch dc:date in format 200x-XX-XX
				$pretty_date = date("F d, Y", strtotime($rss_1_date));
			} else {
				$pretty_date = date($date_format, $in_date);
			}

			
			$str.= "document.write('<span class=\"rss_date\">posted on $pretty_date</span><br />');\n"; 
		}
	
	
		// output description of item if desired
		if ($desc) {
		
			if ($item['summary']) {
				$my_blurb = $item['summary'];
	
			} else {    // Atom support (thanks David Carter-Tod)
				$my_blurb = $item['content']['encoded'];
			}

			// strip html
			if ($html != 'a') $my_blurb = strip_tags($my_blurb);
			
			// trim descriptions
			if ($desc > 1) {
			
				// display specified substring numbers of chars;
				//   html is stripped to prevent cut off tags
				$my_blurb = substr($my_blurb, 0, $desc) . '...';
			}
	
		
			$str.= "document.write('" . addslashes(strip_returns($my_blurb, $br)) . "');\n"; 
			
		}
			
		$str.= "document.write('$more_link</li>');\n";	
	}


	$str .= "document.write('</ul></div>');\n";

}

if ($html != 'y') {
	// Render as JavaScript
	// START OUTPUT
	// headers to tell browser this is a JS file
	if ($rss) {
		header("Content-type: application/x-javascript");
	}

	// Spit out the results as the series of JS statements
	echo $feedcheck_str . $str;

} else {
	// for HTML output, strip out JavaScript, strip escaped quotes
	$str = preg_replace("/document.write\(\'/", "", $str);
	$str = preg_replace("/\'\)\;/", "", $str);
	$str = stripslashes($str);

	// Now write a basic page with the feed as content
	echo "<html><head><title>RSS Feed: " . $rss->channel['title'] . '</title></head><style type="text/css">';
	echo "#content { margin: 10px 20px;}
p { font-family: verdana, arial, sans-serif; font-size: 12px; margin-top:0; margin-bottom:1em;
	}
h1 { font-family: verdana, arial, sans-serif; font-size: 24px; margin-bottom: 2px;	   text-align:center;}
</style>
</head>";

	echo '<body bgcolor="#FFFFFF"><div id="content">';
	echo '<h1>RSS Feed for ' . $rss->channel['title'] . '</h1><p>Note: Content for this RSS feed is provided as a text alternative to inline RSS feeds that may not display on all browsers.</p>';
	echo "<!-- $feedcheck_str --> $str</div></body></html>";
}


?>
