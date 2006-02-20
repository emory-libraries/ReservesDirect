<?
/*******************************************************************************
reservesManager.class.php


Created by Kathy Washington (kawashi@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/


*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/displayers/reservesDisplayer.class.php");
require_once("secure/classes/searchItems.class.php");
require_once("secure/classes/request.class.php");
require_once("secure/classes/faxReader.class.php");
require_once("secure/classes/itemAudit.class.php");
//require_once("classes/reserves.class.php");

class reservesManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;

	function display()
	{
		//echo "attempting to call ". $this->displayClass ."->". $this->displayFunction ."<br>";

		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);

	}

	function reservesManager($cmd, $user)
	{
		global $g_permission, $page, $loc, $g_faxDirectory, $g_documentDirectory, $g_documentURL, $ci, $g_notetype;

		$this->displayClass = "reservesDisplayer";
		//$this->user = $user;
		switch ($cmd)
		{
			case 'removeStudent':
				if ($_REQUEST['deleteAlias'])
				{
					$aliases = $_REQUEST['alias'];
					if (is_array($aliases) && !empty($aliases)){
						foreach($aliases as $a)
						{
							$user->detachCourseAlias($a);
						}
					}
				}
			case 'addStudent':
				if ($_REQUEST['aID']) {
					$user->attachCourseAlias($_REQUEST['aID']);
				}
			case 'myReserves':
			case 'viewCourseList':
				$page = "myReserves";
				$loc  = "home";

				$user->getCourseInstances();
				for ($i=0;$i<count($user->courseInstances);$i++)
				{
					$my_ci = $user->courseInstances[$i];
					$my_ci->getInstructors();
					$my_ci->getProxies();

					//Look at this later - should this logic be handled by ci->getCourseForUser? - kawashi 11.2.2004
					if (in_array($user->getUserID(),$my_ci->instructorIDs) || in_array($user->getUserID(),$my_ci->proxyIDs)) {
						//$my_ci->getCourseForInstructor($user->getUserID());
						$my_ci->getPrimaryCourse();
					} else {
						$my_ci->getCourseForUser($user->getUserID());  //load courses
					}
				}

				$this->displayFunction = "displayCourseList";
				$this->argList = array($user);
			break;
			
			
			case 'previewStudentView':	//see if($cmd==...) statement in previewReservesList	
			case 'previewReservesList':
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->getInstructors();
				$ci->getCrossListings();
				$ci->getPrimaryCourse();
				
				//get different reserve data based on $cmd
				if($cmd=='previewStudentView') {
					$reserve_data = $ci->getActiveReserves();	
				}
				elseif($cmd=='previewReservesList') {
					$reserve_data = $ci->getReserves();
				}
				
				//build treen and get recursive iterator
				$tree = $ci->getReservesAsTree(null, null, $reserve_data);
				$walker = new treeWalker($tree);
				
				$page = "myReserves";
				$loc  = "home";
				$this->displayFunction = "displayReserves";
				$this->argList = array($cmd, $ci, $walker, count($reserve_data[0]), $reserve_data[2], true);				
			break;
			
			
			case 'viewReservesList':
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->getInstructors();
				$ci->getCrossListings();
				$ci->getCourseForUser($user->getUserID());
				
				//hide/unhide items
				if(!empty($_REQUEST['hideSelected'])) {
					//we need to fetch the whole tree (including hidden items)
					$reserve_data = $ci->getActiveReservesForUser($user->getUserID(), true);
					//build tree
					$tree = $ci->getReservesAsTree(null, null, $reserve_data);
					
					$hidden = !empty($_REQUEST['selected_reserves']) ? $_REQUEST['selected_reserves'] : array();
					
					//unhide first
					if(!empty($reserve_data[2])) {	//only bother if there were hidden items before
						$unhide = array_diff($reserve_data[2], $hidden);
				
						//are there changes?
						if(!empty($unhide)) {
							foreach($unhide as $r_id) {	//must unhide element AND its children
								//unhide reserve
								$reserve = new reserve($r_id);
								$reserve->unhideReserve($user->getUserID());
								//unhide its children
								$walker = new treeWalker($tree->findDescendant($r_id));
								foreach($walker as $leaf) {
									$reserve = new reserve($leaf->getID());
									$reserve->unhideReserve($user->getUserID());
								}
							}
						}
					}
			
					//now hide (the same process in reverse)
					if(!empty($hidden)) {	//only bother if anything was checked
						$hide = array_diff($hidden, $reserve_data[2]);

						//are there changes?
						if(!empty($hide)) {
							foreach($hide as $r_id) {	//must hide element AND its children
								//hide reserve
								$reserve = new reserve($r_id);
								$reserve->hideReserve($user->getUserID());
								//hide its children
								$walker = new treeWalker($tree->findDescendant($r_id));
								foreach($walker as $leaf) {
									$reserve = new reserve($leaf->getID());
									$reserve->hideReserve($user->getUserID());
								}
							}
						}
					}					
				}
				
				//get array of reserves for this CI for tree-building
				$reserve_data = $ci->getActiveReservesForUser($user->getUserID(), $_REQUEST['showAll']);
				//build treen and get recursive iterator
				$tree = $ci->getReservesAsTree(null, null, $reserve_data);
				$walker = new treeWalker($tree);
				
				$page = "myReserves";
				$loc  = "home";
				$this->displayFunction = "displayReserves";
				$this->argList = array($cmd, $ci, $walker, count($reserve_data[0]), $reserve_data[2], false);	
			break;

			case 'customSort':
				$page = "myReserves";
				$loc  = "sort reserves list";
				
				$ci = new courseInstance($_REQUEST['ci']);
				
				if(isset($_REQUEST['saveOrder'])) {	//update order
					foreach($_REQUEST['new_order'] as $r_id=>$order) {
						$reserve = new reserve($r_id);
						$reserve->setSortOrder($order);
					}
					$reserves = $ci->getSortedReserves('', $_REQUEST['parentID']);
				}
				else {
					$reserves = $ci->getSortedReserves($_REQUEST['sortBy'], $_REQUEST['parentID']);
				}
				
				$this->displayFunction = "displayCustomSort";
				$this->argList = array($ci, $reserves);
			break;
			
			case 'selectInstructor':
				$page = "addReserve";
				$progress = array ('total' => 4, 'full' => 0);
				if (($user->getRole() >= $g_permission['staff']) && $cmd=='selectInstructor') {
					//$user->selectUserForAdmin('instructor', $page, 'selectClass');
					$this->displayFunction = "displaySelectInstructor";
					$this->argList = array($user, $page, 'addReserve');
				}
			break;
			
			case 'addReserve':
				$page = "addReserve";
				$progress = array ('total' => 4, 'full' => 0);

				if ($user->getRole() >= $g_permission['staff']) {
					//$courseInstances = $user->getCourseInstances($_REQUEST['u']);
					$this->displayFunction = "displayStaffAddReserve";
					$this->argList = array($_REQUEST);
					break;
				} elseif ($user->getRole() >= $g_permission['proxy']) { //2 = proxy
					$courseInstances = $user->getCourseInstances();
				} else {
					trigger_error("Permission Denied:  Cannot add reserves. UserID=".$user->getUserID(), E_ERROR);
				}

				for($i=0;$i<count($courseInstances); $i++)
				{
					$ci = $courseInstances[$i];
					//$ci->getCourseForUser($user->getUserID());
					$ci->getPrimaryCourse();
				}

				$this->displayFunction = "displaySelectClasses";
				$this->argList = array($courseInstances,$user);
			break;
			case 'displaySearchItemMenu':
				$page="addReserve";
				$progress = array ('total' => 4, 'full' => 1);

				$this->displayFunction = "displaySearchItemMenu";
				$this->argList = array($_REQUEST['ci']);
			break;
			case 'searchScreen':
				$page = "addReserve";

				$this->displayFunction = "displaySearchScreen";
				$this->argList = array($page, 'searchResults', $_REQUEST['ci']);
			break;
			case 'searchResults':
				$page = "addReserve";
				$search = new searchItems();

				if (isset($_REQUEST['f'])) {
					$f = $_REQUEST['f'];
				} else {
					$f = "";
				}

				if (isset($_REQUEST['e'])) {
					$e = $_REQUEST['e'];
				} else {
					$e = "";
				}

				$search->search($_REQUEST['field'], urldecode($_REQUEST['query']), $f, $e);

				if (isset($_REQUEST['request'])) {
					$HiddenRequests = $_REQUEST['request'];
				} else {
					$HiddenRequests = "";
				}
				if (isset($_REQUEST['reserve'])) {
					$HiddenReserves = $_REQUEST['reserve'];
				} else {
					$HiddenReserves = "";
				}
								
				if (!$ci->course instanceof course)
					$ci->getPrimaryCourse();
				
				if (!$ci->course->department instanceof department)
					$ci->course->getDepartment();
				$LoanPeriods = $ci->course->department->getInstructorLoanPeriods();

				$this->displayFunction = "displaySearchResults";
				$this->argList = array($user, $search, 'storeReserve', $_REQUEST['ci'], $HiddenRequests, $HiddenReserves, $LoanPeriods);
			break;
			case 'storeReserve':
				$page = "addReserve";

				$requests = (isset($_REQUEST['request'])) ? $_REQUEST['request'] : null;
				$items = (isset($_REQUEST['reserve'])) ? $_REQUEST['reserve'] : null;

				$ci = new courseInstance($_REQUEST['ci']);

				//add items to reserve
				if (is_array($items) && !empty($items)){
					foreach($items as $i_id)
					{
						$reserve = new reserve();
						if ($reserve->createNewReserve($ci->getCourseInstanceID(), $i_id))
						{
							$reserve->setActivationDate($ci->getActivationDate());
							$reserve->setExpirationDate($ci->getExpirationDate());
							//attempt to insert this reserve into order
							$reserve->getItem();
							$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());
						}
					}
				}

				//make requests
				if (is_array($requests) && !empty($requests)){
					foreach($requests as $i_id)
					{
						//store reserve with status processing
						$reserve = new reserve();
						if ($reserve->createNewReserve($ci->getCourseInstanceID(), $i_id))
						{
							$reserve->setStatus("IN PROCESS");
							$reserve->setActivationDate($ci->getActivationDate());
							$reserve->setExpirationDate($ci->getExpirationDate());
							$reserve->setRequestedLoanPeriod($_REQUEST['requestedLoanPeriod_'.$i_id]);
							//attempt to insert this reserve into order
							$reserve->getItem();
							$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());

							//create request
							$request = new request();
							$request->createNewRequest($ci->getCourseInstanceID(), $i_id);
							$request->setRequestingUser($user->getUserID());
							$request->setReserveID($reserve->getReserveID());
						}
					}
				}
				$this->displayFunction = "displayReserveAdded";
				$this->argList = array($user, null, $_REQUEST['ci']);
				//$this->argList = array($ci);
			break;
			case 'uploadDocument':
				$page="addReserve";
				$this->displayFunction = "displayUploadForm";
				$this->argList = array($user, $_REQUEST['ci'], "DOCUMENT", $user->getAllDocTypeIcons());
			break;
			case 'addURL':
				$page="addReserve";
				$this->displayFunction = "displayUploadForm";
				$this->argList = array($user, $_REQUEST['ci'], "URL", $user->getAllDocTypeIcons());
			break;
			
			case 'storeUploaded':
				$page = "addReserve";
				// Check to see if this was a valid file they submitted
	    		if ($_REQUEST['type'] == 'DOCUMENT'){
	    			if (!$_FILES['userFile']['tmp_name']) {
	    				trigger_error("Possible file upload attack. Filename: " . $_FILES['userFile']['name'] . "If you are trying to load a very file (> 10 MB) contact Reserves to add the file.", E_ERROR);
	    			}
	    		}

			    $item = new reserveItem();
			    $item->createNewItem();
	    		$item->setTitle($_REQUEST['title']);
	    		$item->setAuthor($_REQUEST['author']);
	    		$item->setPerformer($_REQUEST['performer']);
	    		$item->setVolumeTitle($_REQUEST['volumetitle']);
	    		$item->setVolumeEdition($_REQUEST['volume']);
	    		$item->setSource($_REQUEST['source']);
	    			    
	    		$item->setDocTypeIcon($_REQUEST['selectedDocIcon']);

	    		if ($_REQUEST['type'] == 'DOCUMENT'){
	    			$file = common_storeUploaded($_FILES['userfile'], $item->getItemID());
					$item->setURL($g_documentURL.$file['name']);
					$item->setMimeTypeByFileExt($file['ext']);
	    		} else {
	    			$file_path = pathinfo($_FILES['userfile']['name']);
	    			$item->setURL($_REQUEST['url']);
	    			$item->setMimeTypeByFileExt($file_path['extension']);
	    		}
	    		
				$p = $_REQUEST['pagefrom'] . " - " . $_REQUEST['pageto'];
				$t = $_REQUEST['timefrom'] . " - " . $_REQUEST['timeto'];

				//set time or pages if both set overwrite with time
				if ($p != " - ") $item->setPagesTimes($p);
				elseif ($t != " - ") $item->setPagesTimes($t);

				if(isset($_REQUEST['personal'])) $item->setPrivateUserID($user->getUserID());

				$item->setGroup('ELECTRONIC');
				$item->setType('ITEM');
				
				$ci = new courseInstance($_REQUEST['ci'])	;
				$reserve = new reserve();
				
				if ($reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID()))
				{
					if(!empty($_REQUEST['noteText']) && isset($_REQUEST['noteType'])) {
						if($_REQUEST['noteType']==$g_notetype['instructor']) {
							$reserve->setNote($_REQUEST['noteText']);
						}
						else {
							$item->setNote($_REQUEST['noteText']);
						}
					}
					
					$reserve->setActivationDate($ci->getActivationDate());
					$reserve->setExpirationDate($ci->getExpirationDate());					
					//attempt to insert this reserve into order
					$reserve->getItem();
					$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());

					$itemAudit = new itemAudit();
					$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
				}
				
				$this->displayFunction = "displayReserveAdded";
	    		$this->argList = array($user, $reserve, $_REQUEST['ci']);
			break;
			case 'faxReserve':
				$page="addReserve";
				$this->displayFunction = "displayFaxInfo";
				$this->argList = array($_REQUEST['ci']);
			break;
			case 'getFax':
				$page="addReserve";
				$faxReader = new faxReader();
				$faxReader->getFaxesFromFile($g_faxDirectory);

				$this->displayFunction = "claimFax";
				$this->argList = array($faxReader, $_REQUEST['ci']);
			break;
			case 'addFaxMetadata':
				$page="addReserve";
				$faxReader = new faxReader();

				$claims =& $_REQUEST['claimFax'];

				$claimedFaxes = array();
				if (is_array($claims) && !empty($claims))
				{
					foreach ($claims as $claim)
						$claimedFaxes[] = $faxReader->parseFaxName($claim);
				}

				$this->displayFunction = "displayFaxMetadataForm";
				$this->argList = array($user, $claimedFaxes, $_REQUEST['ci']);
			break;
			case 'storeFaxMetadata':
				$page="addReserve";
				$files = array_keys($_REQUEST['file']);

				$items = array();
				foreach ($files as $file)
				{
					$ci = new courseInstance($_REQUEST['ci']);

					$item = new reserveItem();
					$item->createNewItem();

					$item->setTitle($_REQUEST[$file]['title']);
					$item->setAuthor($_REQUEST[$file]['author']);
					$item->setVolumeTitle($_REQUEST[$file]['volumetitle']);
					$item->setVolumeEdition($_REQUEST[$file]['volume']);
					
					//store the fax
					$dst_fname = $item->getItemID().'-fax.pdf';
					if(!copy($g_faxDirectory.$_REQUEST['file'][$file], $g_documentDirectory.$dst_fname)) {
						trigger_error('Failed to copy file '.$g_faxDirectory.$_REQUEST['file'][$file].' to '.$g_documentDirectory.$dst_fname, E_USER_ERROR);
					}
	
					$item->setURL($g_documentURL.$dst_fname);
					$item->setMimeType('application/pdf');

					$p = $_REQUEST[$file]['pagefrom'] . "-" . $_REQUEST[$file]['pageto'];
					if ($p != "-") $item->setPagesTimes($p);

					if ($_REQUEST[$file]['personal'] == "on") $item->setPrivateUserID($user->getUserID());

					$item->setGroup('ELECTRONIC');
					$item->setType('ITEM');

					$reserve = new reserve();

					if ($reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID()))
					{
	    				if(!empty($_REQUEST[$file]['noteText']) && isset($_REQUEST[$file]['noteType'])) {
							if($_REQUEST[$file]['noteType']==$g_notetype['instructor']) {
								$reserve->setNote($_REQUEST[$file]['noteText']);
							}
							else {
								$item->setNote($_REQUEST[$file]['noteText']);
							}
						}
						
						$reserve->setActivationDate($ci->getActivationDate());
						$reserve->setExpirationDate($ci->getExpirationDate());
						//attempt to insert this reserve into order
						$reserve->getItem();
						$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());

						$itemAudit = new itemAudit();
						$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
					}
				}

				$this->displayFunction = "displayReserveAdded";
				$this->argList = array($user, null, $_REQUEST['ci']);
			break;

		}
	}
}
?>
