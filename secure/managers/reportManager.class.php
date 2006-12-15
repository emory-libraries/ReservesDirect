<?
/*******************************************************************************
reportManager.class.php


Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

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

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/


*******************************************************************************/
require_once("secure/displayers/reportDisplayer.class.php");
require_once("secure/classes/report.class.php");

class reportManager extends baseManager {
	public $user;

	function reportManager($cmd, $user, $request)
	{
		global $ci, $loc, $help_article;
		
		$this->displayClass = "reportDisplayer";
		$this->user = $user;
		
		switch ($cmd)
		{
			case 'viewReport':	
				$report = new cachedReport($_REQUEST['reportID']);	//init the object
				$report->fillParameters($_REQUEST);	//attempt to fill parameters
				
				if($report->checkParameters()) {	//are all the parameters filled in?
					$this->displayFunction = "displayReport";
					$this->argList = array($report->getTitle(), $report->doQry());	//run the query and display results
				}
				else {	//need to fill params
					$this->displayFunction = "enterReportParams";
					$this->argList = array($report);
				}
			break;
			
			case 'reportsTab':
				$loc = "View System Statistics";
				$help_article = "11";
			
				$this->displayFunction = "displayReportList";
				$this->argList = array($user->getReportList());
			default:
		}
	}
}
?>
