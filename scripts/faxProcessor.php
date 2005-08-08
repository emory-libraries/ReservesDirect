#!/usr/local/bin/php

<?php 
/*******************************************************************************
faxProcessor.php

Process incoming faxes by converting them to PDF, adding a copyright page,
and depositing them in the "incoming" directory

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

require_once(dirname(__FILE__) . "/../secure/config.inc.php");

$log = fopen("$g_faxLog", w) || exit("Fatal error: Could not open log file '$g_faxLog' for writing");

if($argv[1] == "")
    exit("Usage: faxProcessor.php <filename.tif>");

$inFile = $argv[1];

$now = time(); 

if (!is_executable($g_faxinfo_bin)) {
    fwrite($log, "Processing $inFile: Cannot execute faxinfo binary '$g_faxinfo_bin'\n");
    exit(1);
}

if (!is_executable($g_fax2pdf_bin)) {
    fwrite($log, "Processing $inFile: Cannot execute fax2pdf binary '$g_fax2pdf_bin'\n");
    exit(1);
}

if (!is_executable($g_gs_bin)) {
    fwrite($log, "Processing $inFile: Cannot execute gs binary '$g_gs_bin'");
    exit(1);
}

$sender = exec("$g_faxinfo_bin $inFile | grep Sender | cut -d: -f2 | sed -e 's/ //g'");
if ($sender == "") {
    fwrite($log, "Processing $inFile: Sender is blank\n");
    exit(1);
}

$pages = exec("$g_faxinfo_bin $inFile | grep Pages | sed -e 's/[^0-9]//g'");
if ($pages == "") {
    fwrite($log, "Processing $inFile: Page count is blank\n");
    exit(1);
}

$newFile = $sender . "_" . $now . "_" . $pages;

if (!exec("$g_fax2pdf_bin $inFile $g_faxDirectory/$newFile.tmp.pdf")) {
    fwrite($log, "Processing $inFile: error executing fax2pdf\n");
    exit(1);
}

if (!rename("$inFile", "$g_faxDirectory/$newFile.tif")) {
    fwrite($log, "Processing $inFile: error moving file '$inFile' to '$g_faxDirectory'\n");
    exit(1);
}

if (!exec("$g_gs_bin -q -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=$g_faxDirectory/$newFile.pdf $g_faxCopyright $g_faxDirectory/$newFile.tmp.pdf -c quit")) {
    fwrite($log, "Processing $inFile: error executing gs\n");
    exit(1);
}

if (!unlink("$g_faxDirectory/$newFile.tmp.pdf")) {
    fwrite($log, "Processing $inFile: error removing temporary PDF '$newFile.tmp.pdf'\n");
    exit(1);
}

if (!chmod("$g_faxDirectory/$newFile.pdf", 0664)) {
    fwrite($log, "Processing $inFile: Cannot change permissions of $g_faxDirectory/$newFile.pdf\n");
    exit(1);
}

if (!chmod("$g_faxDirectory/$newFile.tif", 0664)) {
    fwrite($log, "Processing $inFile: Cannot change permissions of $g_faxDirectory/$newFile.tif\n");
    exit(1);
}

fclose($log);

?>
