<?php
/*******************************************************************************
login.php

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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>ReservesDirect Login</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" href="css/standalone_login.css" type="text/css">
    <script language="JavaScript1.2" src="secure/javascript/jsFunctions.js"></script>
  </head>

  <body onload="focusOnForm();">
    
<table width="600" border="0" align="center" cellpadding="10" cellspacing="0" bgcolor="#FFFFFF" class="borders2">
  <tr align="left" valign="top"> 
    <td width="50%" align="left"><img alt="reserves direct logo" src="images/logo-el-blue.jpg" /></td>
    <td width="50%" align="right" valign="middle"><img alt="reserves direct logo" src="images/logo-rd-gold-book.jpg" width="145" height="100" /></td>
  </tr>
  <tr align="center" valign="top"> 
    <td colspan="2">
      <table width="100%" border="0" align="left" cellpadding="7" cellspacing="0">
        <tr align="center" valign="top"> 
          <td> 
          <table width="50%" border="0" cellspacing="0" cellpadding="0">
              <tr bgcolor="#003366"> 
                <td align="left" valign="top" bgcolor="#003366"><img alt="reserves direct logo corner" src="images/corner-blue-top-lt.gif" width="15" height="15" /></td>
                <td width="100%" bgcolor="#003366"> <div align="center"><font color="#FFFFFF" face="Arial, Helvetica, sans-serif"><strong>Welcome
                    to ReservesDirect</strong></font></div></td>
                <td align="right" valign="top" bgcolor="#003366"><img alt="reserves direct logo corner" src="images/corner-blue-top-rt.gif" width="15" height="15" /></td>
              </tr>
              <tr> 
                <td height="103" colspan="3" align="left" valign="top"> 
                
                <form name="RDlogin" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" style="margin:0px;">
                <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="loginBox" height="44">
                  <tr valign="top">
                    <td width="11%" height="10">&nbsp; </td>
                    <td width="75%">
                      <div id="loginText">
            <?php if($login_error): ?>
              <font color="red">Invalid username or password</font>
            <?php endif; ?>
            <p><strong>Net ID:</strong>
                          <input name="username" type="text" size="15"></p> 
                          
                          <p><strong>Password:
                          <input name="pwd" type="password" size="15">
                        </strong> </p></div>
                    </td>
                    <td width="14%">&nbsp;</td>
                  </tr>
                  <tr valign="top">
                    <td height="34" colspan="3" align="center">
                      <input name="submitForm" type="submit" value="Sign In">
                    </td>
                  </tr>
                </table>
                </form></td>
                
              </tr>
              <tr bgcolor="#003366"> 
                <td align="left" valign="bottom"><img alt="reserves direct logo corner" src="images/corner-blue-bottom-lt.gif" width="15" height="15" /></td>
                <td width="100%">&nbsp;</td>
                <td align="right" valign="bottom"><img alt="reserves direct logo corner" src="images/corner-blue-bottom-rt.gif" width="15" height="15" /></td>
              </tr>
            </table>          
            </td>
          </tr>
      </table>
      <p>&nbsp;
      <table class="contacts" cellpadding="2">
      <tr><th>For information on submitting a Course Reserves request, please contact:<br>&nbsp;</th></tr>      
      <tr><td>
        <a target="_blank" href="http://web.library.emory.edu/services/course-reserves">Woodruff Library/General</a>
      </td></tr>
      <tr><td>
      <a target="_blank" href="http://business.library.emory.edu/communities/faculty/e-reserves">Business</a>
      </td></tr>      
      <tr><td>
      <a target="_blank" href="http://health.library.emory.edu/what-we-do/books-journals-articles-etc/reserves">Health</a>
      </td></tr>
      <tr><td>
      <a target="_blank" href="http://library.law.emory.edu/for-law-faculty/circulation-reserves-for-law-faculty/#c19862">Law</a>
      </tr>
      <tr><td>
      <a target="_blank" href="http://oxford.library.emory.edu/services/course-reserves">Oxford College</a>
      </td></tr>
      <tr><td>
      <a target="_blank" href= "http://www.pitts.emory.edu/services/facultysupport_reserves.cfm">Theology</a>
      </td></tr>
      </table>

  </body>
</html>
