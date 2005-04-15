<?php
/*  Feed2JS : RSS feed to JavaScript
	build.php
	
	ABOUT
	This script can be used to create a form that is useful
	for creating the JavaScript strings and testing the output
		
	Developed by Alan Levine 26.may.2004
	http:/jade.mcli.dist.maricopa.edu/alan/
	
	MORE:
	Part of the Feed2JS package
	See http://jade.mcli.dist.maricopa.edu/feed/

*/


	
// GET VARIABLES ---------------------------------------------
// Get variables from input form and set default values

	
	$src = (isset($_GET['src'])) ? $_GET['src'] : '';
	$chan = (isset($_GET['chan'])) ? $_GET['chan'] : 'n';
	$num = (isset($_GET['num'])) ? $_GET['num'] : 0;
	$desc = (isset($_GET['desc'])) ? $_GET['desc'] : 0;
	$date = (isset($_GET['date'])) ? $_GET['date'] : 'n';
	$targ = (isset($_GET['targ'])) ? $_GET['targ'] : 'n';
	$html = (isset($_GET['html'])) ? $_GET['html'] : 'n';
	$utf = (isset($_GET['utf'])) ? $_GET['utf'] : 'n';
	
	
	$preview = (isset($_GET['preview'])) ? $_GET['preview'] : '';
	$generate = (isset($_GET['generate'])) ? $_GET['generate'] : '';
	
	if (isset($preview)) $preview = $_GET['preview'];
	if (isset($generate)) $generate = $_GET['generate'];

	
	if ($html=='a') $desc = 0;
	$options = '';
	
	if ($chan != 'n') $options .= "&chan=$chan";
	if ($num != 0) $options .= "&num=$num";
	if ($desc != 0) $options .= "&desc=$desc";
	if ($date != 'n') $options .= "&date=$date";
	if ($targ != 'n') $options .= "&targ=$targ";
	if ($html != 'n') $html_options = "&html=$html";
	if ($utf == 'y') $options .= '&utf=y';


	
if ($preview or $generate) {
	// URLs for a preview or a generated feed link
	
		$my_dir = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
		
		$rss_str = "$my_dir/feed2js.php?src=" . urlencode($src) . $options . $html_options;

		$noscript_rss_str = "$my_dir/feed2js.php?src=" . urlencode($src) . $options .  '&html=y';

}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Cut n' Paste JavaScript RSS Feed</title>
	<link rel="stylesheet" href="style/main.css" media="all" />
	<link rel="stylesheet" href="style/basic1.css" media="all" />
	<script src="popup.js" type="text/javascript" language="Javascript">
</script>

</head>
<body>
<div id="content">
<h1>Feed2JS</h1>

<?php if ($generate) : ?>

<h2>Cut and Paste JavaScript</h2>
<p class="first">Below is the code you need to copy and paste to your own web page to include this RSS feed. The NOSCRIPT tag provides a link to a HTML display of the feed for users who may not have JavaScript enabled. </p>

<form>
<p class="caption">cut and paste javascript:<br><textarea name="t" rows="10" cols="70">
&lt;script language="JavaScript" src="<?php echo htmlentities($rss_str)?>" type="text/javascript"&gt;&lt;/script&gt;

&lt;noscript&gt;
&lt;a href="<?php echo htmlentities($noscript_rss_str)?>"&gt;View RSS feed&lt;/a&gt;
&lt;/noscript&gt;
</textarea></p>
</form>
<p>&nbsp;</p>

<h2>Feed it Again?</h2>

<?php elseif ($preview) : ?>
<h2>Feed Preview</h2>
<p class="first">Below is a preview of how this feed will appear. The output style is controlled by a <a href="style/basic1.css">linked CSS file</a>. You can use our <a href="style.php?feed=<?php echo urlencode($src)?>">style tool</a> to enhance the display by use of style sheets.</p>

<script language="JavaScript" src="<?php echo $rss_str?>" type="text/javascript"></script>
<noscript>
<a href="<?php echo $noscript_rss_str?>">View Feed</a>
</noscript>

<!-- for script testing, comment out if you do not want users to see -->
<p>If no preview appears, there may be a problem with the script. 
<a href="<?php echo $noscript_rss_str?>" target="feedtest">Test/Debug</a></p>
<!-- end script testing -->


<h2>Feed it Again?</h2>


<?php endif ?>


<h2>Build a Feed!</h2>
<p class="first">The tool below will help you format a feed's display with the information you want to use on your web site. All you need to enter is the URL for the RSS source, and select the desired options below. </p>

<p>First, be sure to <strong>preview</strong> the feed to verify the content and format. Once the content is displayed how you like, just use the <strong>generate javascript</strong> button to get your code. Once the content looks okay, move on to our <a href="style.php?feed=<?php echo urlencode($src)?>">style tool</a> to make it pretty.</p>

<form method="get" action="build.php"  name="builder">

<p><strong>URL</strong> Enter the web address of the RSS Feed<br>
<span style="font-size:x-small">Note: Please verify the URL of your feed and check that it is valid  before using this form.</span><br>
<input type="text" name="src" size="50" value="<?php echo $src?>"> <input type="button" value="Check URL with Feed Validator" onClick="window.open('http://feedvalidator.org/check.cgi?url=' + encodeURIComponent(document.builder.src.value), 'check')">
</p>

<p><strong>Show channel?</strong> (yes/no/title) Display information about the publisher of the feed (yes=show the title and description; title= display title only, no=do not display anything) <br>
<input type="radio" name="chan" value="y" <?php if ($chan=='y') echo 'checked="checked"'?> /> yes <input type="radio" name="chan" value="title" <?php if ($chan=='title') echo 'checked="checked"'?>/> title <input type="radio" name="chan" value="n" <?php if ($chan=='n') echo 'checked="checked"'?>/> no</p>

<p><strong>Number of items to display.</strong> Enter the number of items to be displayed (enter 0 to show all available)<br>
<input type="text" name="num" size="10" value="<?php echo $num?>"></p>

<p><strong>Show/Hide item descriptions? How much?</strong> (0=no descriptions; 1=show full description text; n>1 = display first n characters of description; n=-1 do not link item title, just display item contents)<br>
<input type="text" name="desc" size="10" value="<?php echo $desc?>"></p>

<p><strong>Use HTML in item display? </strong> ("yes" = use HTML from feed and the full item descriptions will be used, ignoring any character limit set above; "no" = output is text-only formatted by CSS; "preserve paragraphs" = no HTML but convert all RETURN/linefeeds to &lt;br&gt; to preserve paragraph breaks)<br>
<input type="radio" name="html" value="a" <?php if ($html=='a') echo 'checked="checked"'?>/> yes <input type="radio" name="html" value="n" <?php if ($html=='n') echo 'checked="checked"'?> /> no <input type="radio" name="html" value="p" <?php if ($html=='p') echo 'checked="checked"'?> /> preserve paragraphs only</p>

<p><strong>Show item posting date?</strong> (yes/no) Display the date the item was added to the feed.<br>
<input type="radio" name="date" value="y" <?php if ($date=='y') echo 'checked="checked"'?>/> yes <input type="radio" name="date" value="n" <?php if ($date!='y') echo 'checked="checked"'?> /> no</p>

<p><strong>Target links in the new window?</strong> (n="no, links open the same page", y="yes, open links in a new window", "xxxx" = open links in a frame named 'xxxx', 'popup' = use a <a href="popup.js">JavaScript function</a> <code>popupfeed()</code> to open in new window) <br>
<input type="text" name="targ" size="10" value="<?php echo $targ?>"></p>

<p><strong>UTF-8 Character Encoding (advanced users)</strong><br>
<input type="checkbox" name="utf" value="y" <?php if ($utf=='y') echo 'checked="checked"'?> /> use UTF-8 character encoding
</p>

<p>
<input type="submit" name="preview" value="Preview" />
<input type="submit" name="generate" value="Generate JavaScript" />

</p>
</form>

</div>

<?php include 'footer'?>

</body>
</html>
