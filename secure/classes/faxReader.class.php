<?
/*******************************************************************************
faxReader.class.php
methods to read and display faxes for selection

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