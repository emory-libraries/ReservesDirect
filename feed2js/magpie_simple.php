<?php

include 'feed2js_config.php';

$url = (isset($_GET['url'])) ? $_GET['url'] : '';


if ( $url ) {
	$rss = fetch_rss( $url );
	
	echo "Channel: " . $rss->channel['title'] . "<p>";
	echo "<ul>";
	foreach ($rss->items as $item) {
		$href = $item['link'];
		$title = $item['title'];	
		echo "<li><a href=$href>$title</a></li>";
	}
	echo "</ul>";
}
?>

<form>
	RSS URL: <input type="text" size="30" name="url" value="<?php echo $url ?>"><br />
	<input type="submit" value="Parse RSS">
</form>
