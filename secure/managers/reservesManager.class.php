<?
/*******************************************************************************
Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Kathy A. Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

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
		global $g_permission, $page, $loc, $g_faxDirectory, $g_documentURL;
		
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
					$ci = $user->courseInstances[$i];
					$ci->getInstructors();
					$ci->getProxies();
		
					//Look at this later - should this logic be handled by ci->getCourseForUser? - kawashi 11.2.2004
					if (in_array($user->getUserID(),$ci->instructorIDs) || in_array($user->getUserID(),$ci->proxyIDs)) {
						//$ci->getCourseForInstructor($user->getUserID());
						$ci->getPrimaryCourse();
					} else {
						$ci->getCourseForUser($user->getUserID());  //load courses 												
					}
				} 
			
				$this->displayFunction = "displayCourseList";
				$this->argList = array($user);
			break;
			
			case 'viewReservesList':
				global $ci;
				$page = "myReserves";
				$loc  = "home";	
				
				$ci = new courseInstance($_REQUEST['ci']);
		
				$ci->getCourseForUser($user->getUserID());
				//$ci->getCourseForUser($user->getUserID(),$_REQUEST['ca']);
				$ci->getActiveReserves();
				$ci->getInstructors();
				$ci->getCrossListings();
				
				$this->displayFunction = "displayReserves";
				$this->argList = array($user, $ci);
			break;

			case 'previewReservesList':
				$page = "myReserves";
				$loc  = "home";	
				
				$ci = new courseInstance($_REQUEST['ci']);
		
				$ci->getReserves();
				$ci->getInstructors();
				$ci->getCrossListings();
				$ci->getPrimaryCourse();
				
				$this->displayFunction = "displayReserves";
				$this->argList = array($user, $ci, 'no_exit');
			break;			
			
			case 'sortReserves':
				$page = "myReserves";
				$loc  = "home";	
							
				$sortBy=$_REQUEST['sortBy'];
				$ci = new courseInstance($_REQUEST['ci']);
		
				//$ci->getCourseForUser($user->getUserID());

				if ($_REQUEST['saveOrder']) {
					$ci->updateSortOrder($sortBy);
				} else {
					$ci->getReserves($sortBy);
				}
				
				$this->displayFunction = "displaySortScreen";
				$this->argList = array($user,$ci);
			break;
			
			case 'customSort':
				$page = "myReserves";
				
				if ($_REQUEST['customSort'])
				{
					//get Post Data that contains the newSortValues assigned by the user
					$reserveSortIDs = array_keys($_REQUEST[reserveSortIDs]);
					
					foreach ($reserveSortIDs as $reserveSortID)
					{
						$reserve = new reserve($reserveSortID);
						$reserve->setSortOrder($_REQUEST[reserveSortIDs][$reserveSortID][newSortOrder]);
					}

					// goto sort Reserves
					reservesManager::reservesManager('sortReserves', $user);
					break;
				}
				
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->updateSortOrder();
				
				$this->displayFunction = "displayCustomSort";
				$this->argList = array($user,$ci);
			break;
			
			case 'selectInstructor':
				$page = "addReserve";
				$progress = array ('total' => 4, 'full' => 0);
				if (($user->getDefaultRole() >= $g_permission['staff']) && $cmd=='selectInstructor') {
					//$user->selectUserForAdmin('instructor', $page, 'selectClass');
					$this->displayFunction = "displaySelectInstructor";
					$this->argList = array($user, $page, 'addReserve');
				}
			break;
			case 'addReserve':	
				$page = "addReserve";
				$progress = array ('total' => 4, 'full' => 0);

				if ($user->getDefaultRole() >= $g_permission['staff']) {
					$courseInstances = $user->getCourseInstances($_REQUEST['u']);
				} elseif ($user->getDefaultRole() >= $g_permission['proxy']) { //2 = proxy
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
				
				$this->displayFunction = "displaySearchResults";
				$this->argList = array($search, 'storeReserve', $_REQUEST['ci'], $HiddenRequests, $HiddenReserves);
			break;
			case 'storeReserve':
				$page = "addReserve";
				
				$requests = $_REQUEST['request'];
				$reserves = $_REQUEST['reserve'];

				$ci = new courseInstance($_REQUEST['ci']);
		
				//add items to reserve
				if (is_array($reserves) && !empty($reserves)){
					foreach($reserves as $r)
					{				
						$reserve = new reserve();
						$reserve->createNewReserve($ci->getCourseInstanceID(), $r);
						$reserve->setActivationDate($ci->getActivationDate());	
						$reserve->setExpirationDate($ci->getExpirationDate());					
					}
				}
		
				//make requests
				if (is_array($requests) && !empty($requests)){
					foreach($requests as $r)
					{
						$request = new request();				
						$request->createNewRequest($ci->getCourseInstanceID(), $r);
				
						//also store reserve with status processing	
						$reserve = new reserve();
						$reserve->createNewReserve($ci->getCourseInstanceID(), $r);	
						$reserve->setStatus("IN PROCESS");
						$reserve->setActivationDate($ci->getActivationDate());	
						$reserve->setExpirationDate($ci->getExpirationDate());
					}
				}
				$this->displayFunction = "displayReserveAdded";
				$this->argList = array($_REQUEST['ci']);
				//$this->argList = array($ci);
			break;
			case 'uploadDocument':
				$page="addReserve";
				$this->displayFunction = "displayUploadForm";
				$this->argList = array($_REQUEST['ci'], "DOCUMENT");
			break;
			case 'addURL':
				$page="addReserve";	
				$this->displayFunction = "displayUploadForm";
				$this->argList = array($_REQUEST['ci'], "URL");
			break;
			case 'storeUploaded':
				$page = "addReserve";
				// Check to see if this was a valid file they submitted
	    		if ($_REQUEST['type'] == 'DOCUMENT'){
	    			if (!$_FILES['userfile']['tmp_name']) {
	    				trigger_error("Possible file upload attack. Filename: " . $_FILES['userfile']['name'] . "If you are trying to load a very file (> 10 MB) contact Reserves to add the file.", E_ERROR);
	    			}
	    		}
	    		      
			    list($filename, $type) = split("\.", $_FILES['userfile']['name']);
			    $item = new reserveItem();
			    $item->createNewItem();
	    		$item->setTitle($_REQUEST['title']);
	    		$item->setAuthor($_REQUEST['author']);
	    		$item->setPerformer($_REQUEST['performer']);
	    		$item->setVolumeTitle($_REQUEST['volumetitle']);
	    		$item->setvolumeEdition($_REQUEST['volume']);
	    		$item->setSource($_REQUEST['source']);
	    		$item->setContentNotes($_REQUEST['contents']);
	    	
	    		if ($_REQUEST['type'] == 'DOCUMENT'){
	        		//move file set permissions and store location
	        		//position uploaded file so that common_move and move it
	        		move_uploaded_file($_FILES['userfile']['tmp_name'], $_FILES['userfile']['tmp_name'] . "." . $type);
	        		chmod($_FILES['userfile']['tmp_name'] . "." . $type, 0644);   	       	     	
	        	
	        		$newFileName = $item->getItemID() ."-". str_replace(" ", "_", $filename . "." . $type);
	        		common_moveFile($_FILES['userfile']['tmp_name'] . "." . $type,  $newFileName );
	        		$item->setURL($g_documentURL . $newFileName);
	    		} else {
	    			$item->setURL($_REQUEST['url']);
	    		}
	    		$item->setMimeTypeByFileExt($type);
	    	
				$p = $_REQUEST['pagefrom'] . " - " . $_REQUEST['pageto'];
				$t = $_REQUEST['timefrom'] . " - " . $_REQUEST['timeto'];		
			
				//set time or pages if both set overwrite with time
				if ($p != " - ") $item->setPagesTimes($p);
				elseif ($t != " - ") $item->setPagesTimes($t);
			
				if ($_REQUEST['personal'] == "on") $item->setprivateUserID($user->getUserID());
			
				$item->setGroup('ELECTRONIC');
				$item->setType('ITEM');
				
				$ci = new courseInstance($_REQUEST['ci'])	;
			
				$reserve = new reserve();
				$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());
				$reserve->setActivationDate($ci->getActivationDate());	
				$reserve->setExpirationDate($ci->getExpirationDate());
				
				$itemAudit = new itemAudit();
				$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
	    	
	    		$this->displayFunction = "displayReserveAdded";
	    		$this->argList = array($_REQUEST['ci']);
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
				$this->argList = array($claimedFaxes, $_REQUEST['ci']);				
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
					$item->setvolumeEdition($_REQUEST[$file]['volume']);
					$item->setContentNotes($_REQUEST[$file]['contents']);
				
					//move file and store new location
					$newFileName = str_replace(" ", "_", $item->getItemID()."-".$_REQUEST['file'][$file]);
				
					common_moveFile($g_faxDirectory.$_REQUEST['file'][$file], $newFileName);
					$item->setURL($g_documentURL . $newFileName);
					$item->setMimeType('application/pdf');
				
					$p = $_REQUEST[$file]['pagefrom'] . "-" . $_REQUEST[$file]['pageto'];
					if ($p != "-") $item->setPagesTimes($p);
					
					if ($_REQUEST[$file]['personal'] == "on") $item->setprivateUserID($user->getUserID());
				
					$item->setGroup('ELECTRONIC');
					$item->setType('ITEM');
							
					$reserve = new reserve();
					$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());
					$reserve->setActivationDate($ci->getActivationDate());	
					$reserve->setExpirationDate($ci->getExpirationDate());
					
					$itemAudit = new itemAudit();
					$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
				}
		
				$this->displayFunction = "displayReserveAdded";
				$this->argList = array($_REQUEST['ci']);
			break;
			
		}	
	}
}
?>