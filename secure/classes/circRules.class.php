<?
/*******************************************************************************
circRules.class.php
Circulation Rules Primitive Object

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

class circRules
{
	function circRules() {}
	
	function getCircRules()
	{
		global $g_dbConn;
				
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT circ_rule, alt_circ_rule, default_selected FROM circ_rules ORDER BY circ_rule";
		}

		$rs = $g_dbConn->query($sql);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }		
		
		unset ($cRules);		
		$i = 0;
		while($row = $rs->fetchRow())
		{
			unset ($tmpArray);
			$tmpArray['circRule'] = $row[0];
			$tmpArray['alt_circRule'] = $row[1];
			$tmpArray['default'] = ($row[2] == 'yes') ? 'selected' : '';
			
			$cRules[$i] = $tmpArray;
			$i++;
		}
		return $cRules;
	}
	
	
}
?>