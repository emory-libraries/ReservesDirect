#!/usr/bin/perl

use strict;
my $r = Apache->request;

$r->status(200);
my $uri = $r->prev->uri;
# if there are args, append that to the uri
my $args = $r->prev->args;
if($args) {
	$args = "?" . $args;
}

my $skin = "";
my $docs = "";
my $img = "";
my $title = "";
my $loginpath = "/reserves2.1/index.php";
my $css = "/reserves2/css/ReservesStyles.css";
my $imagepath = "../images";
my $hostname = "biliku.library.emory.edu";

my $reason = $r->prev->subprocess_env("AuthCookieReason");
my $errorString = "";

if ($reason eq "no_cookie")
{
       $errorString = "";
#	$errorString = "<TR><TD ALIGN=CENTER>\n<P><FONT COLOR=\"#FF0000\">You don't have a cookie yet. Sign in and you get one!</P></TD></TD></TR>";
	my $action = $r->prev->dir_config('FormAction');
	$title = "Reserves Direct";

my $form = <<HERE;

<html>
<head>
<title>$title Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="$css" rel="stylesheet" type="text/css">
</head>

<body onLoad="document.forms[0].credential_0.focus();">
<FORM METHOD="POST" ACTION="https://$hostname/$action">
<INPUT TYPE=hidden NAME=destination VALUE="$uri$args");
<table width="60%" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td><div align="center">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr valign="top">
          <td valign="middle"><img src="$imagepath/logo_emorylibraries.gif" width="219" height="56"></td>
          <td><div align="right"><img src="$imagepath/logo_reservesDirect.gif" width="105" height="80"></div></td>
        </tr>
      </table>
    </div></td>
  </tr>
  <tr>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" valign="top" bgcolor="#000099">
    	<div align="center">
    		<a href="http://www.library.emory.edu/">
    			<font color="#FFFFFF"><strong>EUCLID</strong></font>
    		</a>
    		<strong><font color="#FFFFFF">|</font></strong>
    		<a href="http://www.emory.edu/LIBRARIES/"><font color="#FFFFFF"><strong>Emory Libraries</strong></font></a>
    		<font color="#FFFFFF"><strong> |</strong></font> <a href="http://www.emory.edu"><font color="#FFFFFF"><strong>Emory Home</strong></font></a>
    	</div>
    </td>
  </tr>
  <tr>
    <td width="50%" align="left" valign="top"><table width="75%" border="0" align="center" cellpadding="5" cellspacing="0" bgcolor="#CCCCCC" class="borders">
        <td width="50%" align="right" valign="middle"><strong>Login:</strong></td>
        <td width="50%" align="left" valign="middle"><INPUT TYPE="text" NAME="credential_0" SIZE=10 MAXLENGTH=20 tabindex=0></td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td width="50%" align="right" valign="middle"><p><strong>Password:</strong></p>
          </td>
        <td width="50%" align="left" valign="middle"><INPUT TYPE="password" NAME="credential_1" SIZE=10 MAXLENGTH=20 tabindex=0></td>
      </tr>
      <tr bgcolor="#CCCCCC"><td colspan="2">&nbsp;</td></tr>
      <tr bgcolor="#CCCCCC"><td colspan="2" align="center"><INPUT TYPE="submit" VALUE="Sign In"></td></tr>
    </table></td>
  </tr>
  <tr>
    <td align="center" valign="top" bgcolor="#000099">&nbsp;$errorString</td>
  </tr>
  <tr>
    <td align="center" valign="top"><br>Enter your Emory Network (Eagle/Dooley) ID/password to sign in.<p><strong>Goizueta
          Business School Faculty and Students: Use your Goizueta Network Account
          (GBSNET) login. </strong><br>
      (Don't know your GBSNET ? Call 404-727-0581 for assistance) </p>
      <p>Help with passwords is available
          from ITD (404-727-7777) during normal business hours Monday through
        Friday and limited hours on the weekend
    or check their web site: <a href="http://it.emory.edu/showdoc.cfm?docid=1079">http://it.emory.edu/showdoc.cfm?docid=1079</a></p>
    <p>Problems logging in? <a href="http://$hostname/emailReservesDesk.php">Email the Reserves Desk</a>  </p></td>
  </tr>
</table>
</FORM>
</body>
</html>

HERE

	
	$form = $form . $skin;
	$r->no_cache(1);
	my $x = length($form);
	$r->content_type("text/html");
	$r->header_out("Content-length","$x");
	$r->header_out("Pragma", "no-cache");
	$r->send_http_header;
	
	$r->print ($form);
}

if ($reason eq "bad_credentials") 
{
	$errorString = "<TR><TD ALIGN=CENTER><P><FONT COLOR=\"#FF0000\">Sorry, you entered an invalid Username or Password, please try again in 4 seconds ...</FONT></P></TD></TR>";
	$errorString = $errorString . "<TR><TD ALIGN=CENTER><A HREF=\"$loginpath\">(If you are not returned, click here)</A></TD></TR>";
        $r->no_cache(1);
	$r->content_type("text/html");
	$r->header_out("Pragma", "no-cache");
	$r->send_http_header;
	$r->print ("<html><head><META HTTP-EQUIV=\"Refresh\" CONTENT=\"4;URL=$loginpath\"></head><body><br /><br /><table width=50% align=center>$errorString</table></body></html>");

}

