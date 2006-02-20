<?php
/***********************************************************************
tsvGenerator.php
accepts a POSTed dataSet from a report or reserve list and generates a
tab-separated spreadsheet.

Created by Chris Roddy (croddy@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or 
modify it under the terms of the GNU General Public License as published 
by the Free Software Foundation; either version 2 of the License, or
(at your option) any later version. 
ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at: http://www.reservesdirect.org/ 

***********************************************************************/

header("Content-Type: text/tab-separated-values");
header("Content-Disposition: attachment; filename=\"reservesData.tsv\"");


$dataSet = unserialize(urldecode($_POST['dataSet']));
if (!isset($_POST['dataSet']) || (count($dataSet) < 1)) {
    echo "Empty data set.\t";
    die();
}

//column headers
foreach ($dataSet[0] as $key => $value)
    echo str_replace("\t", "     ", "$key") . "\t";
echo "\n";

//data
for ($i=0; $i<count($dataSet); $i++) {
    foreach ($dataSet[$i] as $key => $value)
        echo str_replace("\t", "     ", "$value") . "\t";
    echo "\n";
}

?>
