<?
/*******************************************************************************
common.inc.php
common functions that dont quite fit anywhere else

Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/classes/users.class.php");
require_once("secure/classes/note.class.php");
require_once("secure/classes/reserveItem.class.php");

$g_permission = array("student"=>0, "custodian"=>1, "proxy"=>2, "instructor"=>3, "staff"=>4, "admin"=>5);

// user defined error handling function
/**
 * @return void
 * @param int $errno
 * @param string $errmsg
 * @param string $filename
 * @param string $linenum
 * @param string $vars
 * @desc Handle Errors 
*/
function common_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{	
   global $g_errorEmail, $g_error_log, $u, $cmd;
	/*
	echo "E_USER_ERROR=".E_USER_ERROR."<br>";
	echo "E_ERROR=".E_ERROR."<br>";
	echo "E_WARNING=".E_WARNING."<br>";
	echo "E_PARSE=".E_PARSE."<br>";
	echo "E_NOTICE=".E_NOTICE."<br>";
	echo "E_CORE_ERROR=".E_CORE_ERROR."<br>";
	echo "E_CORE_WARNING=".E_CORE_WARNING."<br>";
	echo "E_COMPILE_ERROR=".E_COMPILE_ERROR."<br>";
	echo "E_COMPILE_WARNING=".E_COMPILE_WARNING."<br>";
	echo "E_USER_ERROR=".E_USER_ERROR."<br>";
	echo "E_USER_WARNING=".E_USER_WARNING."<br>";
	echo "E_USER_NOTICE=".E_USER_NOTICE."<br>";
	echo "E_STRICT=".E_STRICT."<br>";
	*/
   //echo "secure/common_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars)<br>";

   if ($errno <> E_NOTICE && $errno <> E_STRICT && $errno <> E_WARNING)
   {
	   
		// timestamp for the error entry
	   $dt = date("Y-m-d H:i:s (T)");
	   // define an assoc array of error string
	   // in reality the only entries we should
	   // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
	   // E_USER_WARNING and E_USER_NOTICE
	   $errortype = array (
	               E_USER_ERROR		=> "SQL Error",
	   			   E_ERROR          => "Error",
	               E_WARNING        => "Warning",
	               E_PARSE          => "Parsing Error",
	               E_NOTICE          => "Notice",
	               E_CORE_ERROR      => "Core Error",
	               E_CORE_WARNING    => "Core Warning",
	               E_COMPILE_ERROR  => "Compile Error",
	               E_COMPILE_WARNING => "Compile Warning",
	               E_USER_ERROR      => "User Error",
	               E_USER_WARNING    => "User Warning",
	               E_USER_NOTICE    => "User Notice",
	               E_STRICT          => "Runtime Notice"
	               );
	   // set of errors for which a var trace will be saved
	   
	   $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_USER_ERROR);
	   $err = "<errorentry>\n";
	   $err .= "\t<datetime>" . $dt . "</datetime>\n";
	   $err .= "\t<errornum>" . $errno . "</errornum>\n";
	   $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
	   $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
	   $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
	   $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

	   if ($u instanceof user)
	   		$err .= "\t<user><username>" . $u->getUserName() . "</username><userID>" . $u->getUserID() . "</userID></user>\n";
	   		
	   $err .= "\t<cmd>$cmd</cmd>\n";
	   if (in_array($errno, $user_errors)) {
	       $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
	   }
	   $err .= "</errorentry>\n\n";
	  
	   // for testing
	   //echo htmlentities($err);
	
	   // save to the error log, and e-mail me if there is a critical user error	
	   error_log($err, 3, $g_error_log);
	   mail($g_errorEmail, "Reserves Direct Error", $err);
	      
	   include_once "error.php";
	   exit;
	}
}	

/**
* @return array of notes
* @param string $targetTable
* @param int $targetID
* @desc get record by targ
*/

function common_getNotesByTarget($targetTable, $targetID)
{
	global $g_dbConn;
	
	switch ($g_dbConn->phptype)
	{
		default: //'mysql'
			$sql = "SELECT note_id, note, target_id, target_table, type "
				.  "FROM notes "						  
				.  "WHERE target_id = ! AND target_table = ? "
				.  "ORDER BY type, note_id";
	}
	
	$rs = $g_dbConn->query($sql, array($targetID, $targetTable));

	if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	
	$tmpArray = array();
	while ($row = $rs->fetchRow()) {
		$tmpNote = new note();
		list($tmpNote->noteID, $tmpNote->text, $tmpNote->targetID, $tmpNote->targetTable, $tmpNote->type) = $row;
		$tmpArray[] = $tmpNote;
	}
	
	return $tmpArray;
}

/**
* @return note object
* @param optional int $noteID
* @param string $noteType
* @param string $noteText
* @param string $targetTable
* @param int $targetID
* @desc Creates a note object, and calls individual set methods to set the note attributes
*/
function common_setNote($noteID=NULL, $noteType, $noteText, $targetTable, $targetID)
{
	$tempNote = new note($noteID);
	if (is_null($noteID)) {
		$tempNote->noteID = $tempNote->createNewNote();
	}
	$tempNote->setType($noteType);
	$tempNote->setTarget($targetID, $targetTable);
	$tempNote->setText($noteText);
	return $tempNote;
}

/**
 * @return user Array
 * @param mixed $role int role or all
 * @desc returns array of users with role >= given role'
*/
function common_getUsers($role)
{
	$usersObject = new users();
	return $usersObject->getUsersByRole($role);
}

function common_getAllUsers()
{
	$usersObject = new users();
	return $usersObject->getAllUsers();
}

function common_getDepartments()
{
	global $g_dbConn;

	switch ($g_dbConn->phptype)
	{
		default: //'mysql'
			$sql =	"SELECT department_id, `abbreviation` "
				.	"FROM `departments` "
				.	"ORDER BY abbreviation";
	}
	
	$deptList = $g_dbConn->query($sql);
	if (DB::isError($deptList))  trigger_error($deptList->getMessage(), E_USER_ERROR) ; 	
	return $deptList;
}

/**
 * @return void
 * @param string $src source file 
 * @param string $dest file destination location
 * @desc moves a file to the specified destination within the documentDirectory defined in the configuration
*/
function common_moveFile($src, $dest)
{
	global $g_documentDirectory;	
	exec("/usr/bin/sudo -u coursecontrol /usr/local/bin/reserveMover $src $dest", $stat);

	//if the new file is not readable something went wrong
	if (!is_readable($g_documentDirectory.$dest)) trigger_error("file $src could not be moved to $dest" . $stat, E_USER_ERROR);
}


function common_getStatusDisplayColor($status)
{
	
	$status = strtoupper($status);
	switch ($status) {
		case 'ACTIVE':
		case 'PUBLIC':
			$statusColor = '#009900';
		break;

		case 'INACTIVE':
			$statusColor = '#CC0000';
		break;

		case 'IN PROCESS':
		case 'HIDDEN':
			$statusColor = '#666666';
		break;
				
		default:
			$statusColor = 'black';
	}
	
	return $statusColor;
}

function common_getSkin($skin)
{
	global $g_skins;
	return (key_exists($skin, $g_skins)) ? $g_skins[$skin] : $g_skins['default'];
}

function common_formatDate($d, $format)
{
	if (is_array($d)){	
		list ($Y, $M, $D) = split('-', $d);
		switch ($format)
		{
			case "MM-DD-YYYY":
			default:
				return "$M-$D-$Y";
		}
	} else return "";
}
?>
