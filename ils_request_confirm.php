<?
/*******************************************************************************
ils_request_confirm.php
display confirmation that materials have been added to class

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2008 Emory University, Atlanta, Georgia.

Licensed under the ReservesDirect License, Version 1.0 (the "License");      
you may not use this file except in compliance with the License.     
You may obtain a copy of the full License at                              
http://www.reservesdirect.org/licenses/LICENSE-1.0

ReservesDirect is distributed in the hope that it will be useful,
but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE, and without any warranty as to non-infringement of any third
party's rights.  See the License for the specific language governing         
permissions and limitations under the License.

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");


// workaround for workaround for ie's idiotic caching policy handling
header("Cache-Control: no-cache");
header("Pragma: no-cache");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Reserve Request for Woodruff Library, Emory University</title>
  <link rev="made" href="mailto:reserves@emory.edu" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="description" content="Request form for reserving books in Woodruff Library and for requesting book chapters and other materials for online use in  ReservesDirect." />

  <meta name="keywords" content="reserves, ereserves, electronic reserves, e-reserves, request, scanning, scan, digitization, Emory, Woodruff, library, Woodruff library, Emory University, main library" />
  <link rel="stylesheet" href="css/ReservesStyles.css" type="text/css">

  <style type="text/css">
	body {font-family: verdana; margin: 25px;}
	p, h1, h2, legend, li { font-family: verdana; margin-left: 50px; }
	p, li { font-size: small; }
	
	h1 { font-size: x-large; font-weight: bold; } 
	h2 { font-size: large; font-weight: bold; }
  </style>
</head>

<body bgcolor="#FFFFCC" text="#000000" link="#000080" vlink="#800080" alink="#FF0000">
	<h1>Reserve Request for Woodruff Library</h1>

	<p>
		<h2>Your reserves request has been submitted.</h2>
		<br/>
		Please remember:
	</p>	
	
	<ul>
		<li>Reserve requests are processed in the order received.</li>
		
		<li>Reserve requests are normally fulfilled within <strong>7 days</strong> during the semester or <strong>14 days</strong> near the start of the semester.</li> 
		<li>Reserve requests can take significantly longer to fulfill when items are checked out or missing.</li>
	</ul>
	
	<p>Click <a href="<?= $g_siteURL ?>?cmd=editClass&ci=<?= $_REQUEST['ci'] ?>">here</a> to review your course materials.</p>
	<div style="height: 300px;"></div>
	
    <? include("secure/html/footer.inc.html"); ?>
</body>
</html>