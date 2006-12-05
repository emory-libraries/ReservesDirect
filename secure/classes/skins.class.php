<?
/*******************************************************************************
skins.class.php
methods for manipulating skin/stylesheet configurations

Created by Chris Roddy (croddy@emory.edu)

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
                $skin_sql =  "SELECT skin_stylesheet FROM skins WHERE skin_name=\"$skinName\" LIMIT 1";
                $default_sql =  "SELECT skin_stylesheet FROM skins WHERE default_selected='yes' LIMIT 1";
        }

        $rs = $g_dbConn->query($skin_sql);
        if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

        $row = $rs->fetchRow();
        
        if (count($row) != 1) {
            $rs = $g_dbConn->query($default_sql);
            $row = $rs->fetchRow();
        }

        if (count($row) != 1) { 
            trigger_error("No usable skin configuration: ", E_ERROR);
        }

        return $row[0]; // relative pathname of CSS stylesheet

    }
}

?>
