<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ReservesDirect Demo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="css/standalone_login.css" type="text/css">
</head>

<body>
<table width="600" border="0" align="center" cellpadding="10" cellspacing="0" bgcolor="#FFFFFF" class="borders2">
  <tr align="left" valign="top"> 
    <td width="50%">&nbsp;</td>
    <td width="50%" align="right" valign="middle"><img src="images/logo-rd-gold-book.jpg" width="145" height="100"></td>
  </tr>
  <tr align="center" valign="top"> 
    <td colspan="2">
    	<table width="100%" border="0" align="left" cellpadding="7" cellspacing="0">
        <tr align="center" valign="top"> 
          <td> 
          <table width="50%" border="0" cellspacing="0" cellpadding="0">
              <tr bgcolor="#003366"> 
                <td align="left" valign="top" bgcolor="#003366"><img src="images/corner-blue-top-lt.gif" width="15" height="15"></td>
                <td width="100%" bgcolor="#003366"> <div align="center"><font color="#FFFFFF" face="Arial, Helvetica, sans-serif"><strong>Welcome
                    to ReservesDirect</strong></font></div></td>
                <td align="right" valign="top" bgcolor="#003366"><img src="images/corner-blue-top-rt.gif" width="15" height="15"></td>
              </tr>
              <tr> 
                <td height="103" colspan="3" align="left" valign="top"> 
                
                <form name="RDlogin" method="post" action="index.php">
                <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="loginBox" height="44">
                  <tr valign="top">
                    <td width="11%" height="10">&nbsp; </td>
                    <td width="75%">
                      <div id="loginText">
					  <? 
					  	if (isset($_REQUEST['1']))
   						echo "<font color=\"red\">Invalid username or password</font>\n";
					  ?>
					  <p><strong>Username:</strong>
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
                <td height="10" align="left" valign="bottom"><img src="images/corner-blue-bottom-lt.gif" width="15" height="15"></td>
                <td width="100%" height="10">&nbsp;</td>
                <td height="10" align="right" valign="bottom"><img src="images/corner-blue-bottom-rt.gif" width="15" height="15"></td>
              </tr>
            </table>       	  </td>
          </tr>
      </table></td>
  </tr>
  <tr> 
    <td colspan="2"><p align="center" class="small">For documentation and support, visit <a href="https://www.reservesdirect.org"><strong>ReservesDirect.org</strong></a></p></td>
  </tr>
</table>
</body>
</html>

