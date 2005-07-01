<?
/*******************************************************************************
skins.class.php
methods for manipulating skin/stylesheet configurations

Created by Chris Roddy (croddy@emory.edu)

This file is part of ReservesDirect 2.1

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

class skins {

    function skins() {}

    /**
    * @return string
    * @param string $skinName
    * @desc retrieve stylesheet filename from database
    */
    function getSkin($skinName) {
        global $g_dbConn;

        switch ($g_dbConn->phptype) {
            default: //'mysql'
                $sql =  "SELECT skin_stylesheet FROM skins WHERE skin_name=\"$skinName\"";
        }

        $rs = $g_dbConn->query($sql);
        if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

        $row = $rs->fetchRow();
        return $row[0]; // relative pathname of CSS stylesheed

    }
}

?>
