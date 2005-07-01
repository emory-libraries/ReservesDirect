<?
class skins {

    function skins() {}

    function getSkin($skinName) {
        global $g_dbConn;

        switch ($g_dbConn->phptype) {
            default: //'mysql'
                $sql =  "SELECT skin_stylesheet FROM skins WHERE skin_name=\"$skinName\"";
        }

        $rs = $g_dbConn->query($sql);
        if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

        $row = $rs->fetchRow();
        return $row[0];

    }
}

?>
