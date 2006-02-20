<?
/*******************************************************************************
reportManager.class.php


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
require_once("secure/displayers/reportDisplayer.class.php");
require_once("secure/classes/report.class.php");

class reportManager extends baseManager {
	public $user;

	function reportManager($cmd, $user, $request)
	{
		global $ci, $loc;
		
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
			
				$this->displayFunction = "displayReportList";
				$this->argList = array($user->getReportList());
			default:
		}
	}
}
?>
