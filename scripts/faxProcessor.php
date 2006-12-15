#!/usr/local/bin/php

<?php 
/*******************************************************************************
faxProcessor.php
processes incoming faxes

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

require_once(dirname(__FILE__) . "/../secure/config.inc.php");

if($argv[1] == "")
    exit("Usage: faxProcessor.php <filename.tif>\n");

$inFile = $argv[1];

$now = time(); 

if (!is_executable($g_faxinfo_bin)) {
    echo("Processing $inFile: Cannot execute faxinfo binary '$g_faxinfo_bin'\n");
}

if (!is_executable($g_fax2pdf_bin)) {
    echo("Processing $inFile: Cannot execute fax2pdf binary '$g_fax2pdf_bin'\n");
}

if (!is_executable($g_gs_bin)) {
    echo("Processing $inFile: Cannot execute gs binary '$g_gs_bin'");
}

$sender = exec("$g_faxinfo_bin $inFile | grep Sender | cut -d: -f2 | sed -e 's/ //g'");
if ($sender == "") {
    echo("Processing $inFile: Sender is blank\n");
}

$pages = exec("$g_faxinfo_bin $inFile | grep Pages | sed -e 's/[^0-9]//g'");
if ($pages == "") {
    echo("Processing $inFile: Page count is blank\n");
}

$newFile = $sender . "_" . $now . "_" . $pages;

if (!exec("$g_fax2pdf_bin $inFile $g_faxDirectory/$newFile.tmp.pdf")) {
    echo("Processing $inFile: error executing fax2pdf\n");
}

if (!rename("$inFile", "$g_faxDirectory/$newFile.tif")) {
    echo("Processing $inFile: error moving file '$inFile' to '$g_faxDirectory'\n");
}

if (!exec("$g_gs_bin -q -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=$g_faxDirectory/$newFile.pdf $g_faxCopyright $g_faxDirectory/$newFile.tmp.pdf -c quit")) {
    echo("Processing $inFile: error executing gs\n");
}

if (!unlink("$g_faxDirectory/$newFile.tmp.pdf")) {
    echo("Processing $inFile: error removing temporary PDF '$newFile.tmp.pdf'\n");
}

if (!chmod("$g_faxDirectory/$newFile.pdf", 0664)) {
    echo("Processing $inFile: Cannot change permissions of $g_faxDirectory/$newFile.pdf\n");
}

if (!chmod("$g_faxDirectory/$newFile.tif", 0664)) {
    echo("Processing $inFile: Cannot change permissions of $g_faxDirectory/$newFile.tif\n");
}

?>

