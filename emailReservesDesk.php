<?
/*******************************************************************************
emailReservesDesk.php

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2006 Emory University, Atlanta, Georgia.

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
unset($error);

if ($_REQUEST['Submit'] == "Send")
{	
	//sets $xmlConfig to path of config.xml file
	require_once("config_loc.inc.php");
		
	if (!is_readable($xmlConfig)) { trigger_error("Could not read configure xml file path=$xmlConfig", E_USER_ERROR); }	
		$configure = simplexml_load_file($xmlConfig);		
		
		$toEmail = (string)$configure->reservesEmail;	
		$EmailRegExp = (string)$configure->EmailRegExp;		
		$fromEmail = $_REQUEST['fromEmail'];		
		//if (!ereg($EmailRegExp, $fromEmail)) 
			//$error = "Email Address is not valid";		
		$name	= $_REQUEST['name'];	
		$msg	= $_REQUEST['message'];	
		$subject = $_REQUEST['subject'];		
		$addHeaders = "From: $fromEmail" . "Reply-To: $fromEmail\r\n";		
		
		if (!isset($error))	{
			//echo $toEmail;		
			$mail = mail($toEmail, $subject, $msg, "From:$fromEmail\n");		
			if($mail)        
			{			
				$error = "Email Sent";			
				$fromEmail = "";			
				$msg  = "";			
				$name = "";			
				$subject = "";		
			}	
		}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>EMAIL THE RESERVES DESK</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link href="/reserves2/css/ReservesStyles.css" rel="stylesheet" type="text/css"></head>
<body>
<form action="emailReservesDesk.php" method="POST">
<table width="60%" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>    
  <td><div align="center">      <table width="100%" border="0" cellspacing="0" cellpadding="0">        <tr valign="top">          <td valign="middle"><img src="/reserves2/images/logo_emorylibraries-blue.jpg"></td>          <td><div align="right"><img src="/reserves2/images/logo_reservesDirect.gif" width="105" height="80"></div></td>        </tr>      </table>    </div></td>  </tr>  <tr>    <td align="center" valign="top"><font color="Red"><? echo $error ?>&nbsp;</FONT></td>  </tr>  <tr>    <td align="left" valign="top" bgcolor="#000099"><div align="center"><font color="#FFFFFF"><strong>SEND    AN EMAIL TO THE RESERVES DESK</strong></font></div></td>  </tr>  <tr>    <td width="50%" align="left" valign="top"><table width="75%" border="0" align="center" cellpadding="5" cellspacing="0" bgcolor="#CCCCCC" class="borders">      <tr bgcolor="#CCCCCC">        <td width="50%" align="right" valign="middle"><strong>Subject:</strong></td>        <td width="50%" align="left" valign="middle"><input name="subject" type="text" size="20" value="<?php echo $subject ?>"></td>      </tr>      <tr bgcolor="#CCCCCC">        <td width="50%" align="right" valign="middle"><p><strong>Your Name:</strong></p>          </td>        <td width="50%" align="left" valign="middle"><input name="name" type="text" size="20" value="<?php echo $name ?>"></td>      </tr>      <tr bgcolor="#CCCCCC">        <td width="50%" align="right" valign="middle"><strong>Your Email Address:</strong></td>        <td align="left" valign="middle"><input name="fromEmail" type="text" size="20" value="<?php echo $fromEmail ?>"></td>      </tr>      <tr valign="top" bgcolor="#CCCCCC">        <td colspan="2" align="right"><div align="center"><strong>Your Message:<br>                <textarea name="message" cols="45"><?php echo $msg ?></textarea>        </strong></div></td>        </tr>      <tr valign="top" bgcolor="#CCCCCC">        <td colspan="2" align="right"><div align="center">          <input type="submit" name="Submit" value="Send">        </div></td>      </tr>    </table></td>  </tr>  <tr>    <td align="left" valign="top" bgcolor="#000099">&nbsp;</td>  </tr>  <tr>    <td align="center" valign="top">&nbsp;</td>  </tr></table></form></body></html>
