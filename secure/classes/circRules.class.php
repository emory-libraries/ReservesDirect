<?
/*******************************************************************************
circRules.class.php
Circulation Rules Primitive Object

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
			$tmpArray['default'] = ($row[2] == 'yes') ? 'selected="selected"' : '';

			$cRules[$i] = $tmpArray;
			$i++;
		}
		return $cRules;
	}


}
?>
