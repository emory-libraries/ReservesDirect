<html>
<head>
<title>Reserves Direct</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#FFFFFF" text="#000000">
<form action="index.php" method="POST">
	<center>
	<table width="80%" border="0" cellspacing="5" cellpadding="0">
		<tr>
			<td align="center" colspan="2">
			<?
				if (isset($_REQUEST['1']))
					echo "<font color=\"red\">Invalid username/password</font>\n";
			?>&nbsp;
			</td>
		</tr>
		<tr>
			<td>Username:</td><td width="100%"><input type="text" name="username"></td>
		</tr>
		<tr>
			<td>Password:</td><td><input type="password" name="pwd"></td>
		</tr>
		<tr>
			<td align="center" colspan="2"><input type="submit" value="Go"></td>
		</tr>
		
	</table>
	</center>
</form>
</body>
</html>
