<?
/*******************************************************************************
circRules.class.php
Circulation Rules Primitive Object

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
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
			$tmpArray['default'] = ($row[2] == 'yes') ? 'selected' : '';

			$cRules[$i] = $tmpArray;
			$i++;
		}
		return $cRules;
	}


}
?>