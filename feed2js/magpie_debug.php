<?php

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

// set magpie root
define('MAGPIE_DIR', $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']) . '/magpie/');

// define magpie cache directory
define('MAGPIE_CACHE_DIR', MAGPIE_DIR . 'cache/');
// access magpie libraries

require_once(MAGPIE_DIR.'rss_fetch.inc');
require_once(MAGPIE_DIR.'rss_utils.inc');


// set magpie debugging
define('MAGPIE_DEBUG', 2);
// flush cache quickly for debugging purposes, don't do this on a live site
define('MAGPIE_CACHE_AGE', 2);


$url = (isset($_GET['url'])) ? $_GET['url'] : '';


if ( $url ) {
	$rss = fetch_rss( $url );
	
	if ($rss) {
		echo "Channel: " . $rss->channel['title'] . "<p>";
		echo "<ul>";
		foreach ($rss->items as $item) {
			$href = $item['link'];
			$title = $item['title'];	
			echo "<li><a href=$href>$title</a></li>";
		}
		echo "</ul>";
	}
	else {
		echo "Error: " . magpie_error();
	}
}
?>

<form>
	RSS URL: <input type="text" size="30" name="url" value="<?php echo $url ?>"><br />
	<input type="submit" value="Parse RSS">
</form>

<pre>
<?php if ( $url ) var_dump($rss); ?>
</pre>