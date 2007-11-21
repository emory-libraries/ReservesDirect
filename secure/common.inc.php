<?
/*******************************************************************************
common.inc.php
common functions that don't quite fit anywhere else

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
require_once("secure/classes/users.class.php");
require_once("secure/classes/note.class.php");
require_once("secure/classes/reserveItem.class.php");

$g_permission = array("student"=>0, "custodian"=>1, "proxy"=>2, "instructor"=>3, "staff"=>4, "admin"=>5);
$g_notetype = array('instructor'=>'Instructor', 'content'=>'Content', 'staff'=>'Staff', 'copyright'=>'Copyright');
$g_terms	= array('Fall', 'Spring', 'Summer');

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
	   mail($g_errorEmail, "ReservesDirect Error", $err);

	   include_once "error.php";
	   exit;
	}
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
				.	"WHERE name IS NOT NULL "
				.	"ORDER BY abbreviation";
	}

	$deptList = $g_dbConn->query($sql);
	if (DB::isError($deptList))  trigger_error($deptList->getMessage(), E_USER_ERROR) ;
	return $deptList;
}


/**
 * @return assoc array (dir, name, ext) of new dir/filename.ext and ext (ext used to set mimetypes)
 * @param string $src_name filename to be formatted
 * @param int $item_id necessary to format the proper destination path
 * @desc  create filename 
 *		  <upload_directory>/dir/md4hash_itemID.ext where dir is the first 2 char or the md5hash
*/
function common_formatFilename($src, $item_id) {

	$src_file = $src['tmp_name'];
	$src_name = $src['name'];

	//get filename/ext
	$file_path = pathinfo($src_name);	
	
	$md5_file = md5_file($src_file);
	
	if ($md5_file == '' || $item_id == '')
		trigger_error("Could not formatFilename common_formatFilename($src_name, $item_id) tmp_name=$src_file", E_USER_ERROR);
		
	$filename = $md5_file . "_" . $item_id;
	$dir = substr($md5_file,0,2) . "/";
	$ext = ".".$file_path['extension'];
	
	return array('dir' => $dir, 'name'=>$filename, 'ext'=>$ext);
}

/**
 * @return array (name, ext) of new filename and ext
 * @param string $src element of $_FILES[]; uploaded file info array
 * @param int $item_id necessary to format the proper destination path
 * @desc cleans up filename and moves uploaded file to a destination set in the config
*/
function common_storeUploaded($src, $item_id) {
	global $g_documentDirectory;
	
	//check for errors
	if( $src['error'] ) {
		echo 'If you are trying to load a very large file (> 10 MB) contact Reserves to add the file.';
		trigger_error("Possible file upload attack. Filename: " . $src['name'], E_USER_ERROR);
	}
	
	//format the filename; extract extension
	$file = common_formatFilename($src, $item_id);
	
	//test dir
	if (!opendir($g_documentDirectory.$file['dir']))
	{
		//create directory
		if(!mkdir($g_documentDirectory.$file['dir'], 0775, true))
			trigger_error("Could not create directory " .$g_documentDirectory.$file['dir'], E_USER_ERROR);
	}
	
	$newFile = $g_documentDirectory.$file['dir'].$file['name'].$file['ext'];
	//store file
	if( !move_uploaded_file($src['tmp_name'], $newFile) ) {
		trigger_error('Failed to move uploaded file '.$src['tmp_name'].' to '.$newFile, E_USER_ERROR);
	}
	
	//return destination filename/ext to store in DB
	return $file;
}


function common_getStatusStyleTag($status)
{

	$status = strtoupper($status);
	switch ($status) {
		case 'ACTIVE':
		case 'PUBLIC':
			$statusTag = 'active';
		break;

		case 'INACTIVE':
			$statusTag = 'inactive';
		break;

		case 'IN PROCESS':
		case 'HIDDEN':
			$statusTag = 'inprocess';
		break;
		
		case 'HEADING':
			$statusTag = 'heading';
		break;

		default:
			$statusTag = 'black';
	}

	return $statusTag;
}

function common_getEnrollmentStyleTag($enrollment) {
	switch(strtoupper($enrollment)) {
		case 'OPEN':
			$tag = 'openEnrollment';
		break;
		case 'MODERATED':
			$tag = 'moderatedEnrollment';
		break;
		case 'CLOSED':
			$tag = 'closedEnrollment';
		break;
		default:
			$tag = '';
	}
	return $tag;
}

function common_formatDate($d, $format)
{
		$D = split('-', $d);
		if (is_array($D) && count($D) > 2)
		{
			switch ($format)
			{
				case "MM-DD-YYYY":
				default:
					return $D[1].'-'.$D[2].'-'.$D[0];
			}
		} else return '';
}

?>
