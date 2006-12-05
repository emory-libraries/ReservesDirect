<?
/*******************************************************************************
faxReader.class.php
methods to read and display faxes for selection

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

class faxReader
{
	public $faxes;

	function faxReader(){}

	function getFaxesFromFile($faxDirectory)
	{
		// Open the fax directory
		if ($handle = opendir($faxDirectory)) {
			// All we know about a fax is its caller id, time and how many pages it was.  So the user will have to determine which seems like theirs

			while (false !== ($file = readdir($handle))) {
				if(eregi("\.pdf", $file)) {
					$this->faxes[] = $this->parseFaxName($file);
				}
			}
			closedir($handle);
		}
	}

	function parseFaxName($faxName)
	{
		list($fname, $ext) = split("\.", $faxName);
		// course/control's default filename (if using Hylafax and supplied faxrcvd script) is in the format phonenumber_unixepoch_pages.pdf
		list($phone, $time, $pages) = split("_", $fname);

		// Construct some kind of logical looking phone number.
		if(strlen($phone) == 10) {
			$a = substr($phone, 0, 3);
			$b = substr($phone, 3, 3);
			$c = substr($phone, 6, 4);
			$phone = "(" . $a . ") " . $b . "-" . $c;
		} elseif (strlen($phone) == 7) {
			$a = substr($phone, 0, 3);
			$b = substr($phone, 3,4);
			$phone = $a . "-" . $b;
		} elseif (strlen($phone) == 11) {
			$a = substr($phone, 0, 1);
			$b = substr($phone, 1, 3);
			$c = substr($phone, 4, 3);
			$d = substr($phone, 7, 3);
			$phone = $a . " (" . $b . ") " . $c . "-" . $d;
		}

		return array('phone' => $phone, 'time' => date("g:i A m/j/Y",$time), 'pages' => $pages, 'file' => $faxName);
	}
}
?>
