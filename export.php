<?
header("Content-Type:  application/octet-stream");

$link = "http://".$_SERVER['SERVER_NAME'] . ereg_replace('export.php', 'perl/reserves2.cgi', $_SERVER['PHP_SELF']) . "?ci=" . $_REQUEST['ci'];

echo("<html>\n<head><title>Reserves List</title>\n<script  src=\"$link\"></script>\n</head>\n<body>\n</body>\n</html>\n");
?>