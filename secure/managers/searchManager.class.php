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

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/classes/users.class.php");
require_once("secure/displayers/searchDisplayer.class.php");

class searchManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;
	private $search_sql_statement;
	
	function display()
	{
		//echo "attempting to call ". $this->displayClass ."->". $this->displayFunction ."<br>";
		
		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);
			
	}
	
	/**
	 * Manager for Search Tab
	 *
	 * @param string $cmd - current command
	 * @param user $user  - loged in user object
	 * @param array $request - http request object
	 */	
	function searchManager($cmd, $user, $request)
	{
		//echo "searchManager::searchManager($cmd, $user, $request, $hidden_fields=null)<br>";
		global $g_permission, $page, $loc, $ci, $u;
			
		$this->displayClass = "searchDisplayer";
		$page = 'search';		
		
		switch ($cmd)
		{
			case 'doSearch':
				$loc  = "search for documents";
			
				$this->search_sql_statement = (isset($request['sql']) && $request['sql'] != '') ? stripslashes(urldecode($request['sql'])) : null;
				
				$items = $this->doSearch($request['search'], $request['limit'], $request['item'], $request['sort']);
				
				$displayQry = '';
				if (!isset($request['displayQry']))
					for($i=0;$i<count($request['search']);$i++)
					{
						if ($request['search'][$i]['term'] != '')				
						{
							if ($i > 0) $displayQry .= " " . $request['search'][$i]['conjunct'] . " ";
							$displayQry .= $request['search'][$i]['term'];
						}
					}
				else 
					$displayQry = stripslashes($request['displayQry']);						
				
				$hidden_fields = array(
					'cmd'			=> 'addResultsToClass',
					'sql' 			=> urlencode($this->search_sql_statement), 
					'sort' 			=> $request['sort'],
					'displayQry'	=> $displayQry
				);
							
				$this->displayFunction = 'searchResults';				
				$this->argList = array($cmd, $items, $hidden_fields, stripslashes($displayQry));
			break;
			
			case 'addResultsToClass':
				$loc  = "add items to class";
				
				$activate_date = (isset($request['hide_year'])) ? $request['hide_year'] . '-' . $request['hide_month'] . '-' . $request['hide_day'] : date ('Y-m-d');
							
				if (!isset($request['submitButton']) || $request['submitButton'] != 'Add Items to Class')
				{
					//class not yet selected
					$removeItemFromList = (isset($request['removeItem'])) ? $request['removeItem'] : null;

					for($i=0;$i<count($request['itemSelect']);$i++)
						if ($request['itemSelect'][$i] != $removeItemFromList)
							$selectedItem[] = new reserveItem($request['itemSelect'][$i]);				
							
					$this->displayFunction = 'addResultsToClass';				
					$this->argList = array('addResultsToClass', 'storeClassItems', $u, $selectedItem, $request, $activate_date, null);	
				} else {
					//class selected create reserves				
					$requests = (isset($request['requestItem'])) ? $request['requestItem'] : null;
					$reserves = (isset($request['reserveItem'])) ? $request['reserveItem'] : null;						
	
					$ci = new courseInstance($request['ci']);
					$reserveCnt = 0;
	
					//add items to reserve
					if (is_array($reserves) && !empty($reserves)){
						foreach($reserves as $r)
						{
							$reserve = new reserve();
							if ($reserve->createNewReserve($ci->getCourseInstanceID(), $r))
							{
								$reserve->setActivationDate($activate_date);
								$reserve->setExpirationDate($ci->getExpirationDate());
								$reserveCnt++;
							}
						}
					}
	
					//make requests
					if (is_array($requests) && !empty($requests)){
						foreach($requests as $r)
						{
							//store reserve with status processing
							$reserve = new reserve();
							if ($reserve->createNewReserve($ci->getCourseInstanceID(), $r))
							{
								$reserve->setStatus("IN PROCESS");
								$reserve->setActivationDate($activate_date);
								$reserve->setExpirationDate($ci->getExpirationDate());
	
								//create request
								$request = new request();
								$request->createNewRequest($ci->getCourseInstanceID(), $r);
								$request->setRequestingUser($user->getUserID());
								$request->setReserveID($reserve->getReserveID());
							}
						}
					}
				}
				$ci->getPrimaryCourse();
				
				//$loc  = "search for documents";			
								
				$this->displayFunction = 'addComplete';				
				$this->argList = array($cmd, $ci, "$reserveCnt item(s) were successfully added to ". $ci->course->displayCourseNo() . " " . $ci->course->getName());
			break;
				
			case 'searchTab':		
			default:
				$loc  = "search for documents";			
								
				$this->displayFunction = 'searchForDocuments';
				$this->argList = array($cmd, null);
		}
	}
	
	/**
	 * Query Database based on user search options
	 *
	 * @param array $search - search term, field, test and conjunct
	 * @param array $limit  - limit term, field, test and conjunct
	 * @param array $itemGroup - term, test field will always be item_group
	 * @param string $sort - sort field
	 * @return array of reserveItem
	 */
	function doSearch($search, $limit, $itemGroup, $sort)
	{
		global $g_dbConn, $g_permission;

		if (is_null($this->search_sql_statement))
		{
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql_select = "SELECT DISTINCT i.item_id ";
					$sql_from 	= "FROM items as i 
									LEFT JOIN reserves as r ON i.item_id = r.item_id AND i.private_user_id IS NULL AND i.item_type = 'ITEM' 
									LEFT JOIN course_aliases as ca ON r.course_instance_id = ca.course_instance_id
									LEFT JOIN courses as c ON ca.course_id = c.course_id
									LEFT JOIN departments as d ON c.department_id = d.department_id
									LEFT JOIN access as a ON ca.course_alias_id = a.alias_id 
									LEFT JOIN users as u ON a.user_id = u.user_id AND a.permission_level = " . $g_permission['instructor'] . " "
					; 
						
					if ($search[0]['term'] != '') //if 1st term is not set we are going to ignore all others
					{
						$sql_where = "WHERE i.item_type != 'HEADING' AND ";			
						for($i=0;$i<count($search);$i++)
						{
							//$search[$i]['term'] = stripslashes($search[$i]['term']);
							if ($search[$i]['term'] != '')
							{	
								$conjunction = ($i > 0 && $search[$i-1]['term'] != '') ? $search[$i-1]['conjunct'] . " " : ""; 

								switch ($search[$i]['test'])
								{
									case 'LIKE':
										$sql_where .= $conjunction . " match(" . $search[$i]['field'] . ") against ( \"" . strtolower($search[$i]['term']) . "\") ";
									break;
									
									case '<>':
										$sql_where .= $conjunction . " not match(" . $search[$i]['field'] . ") against ( \"" . strtolower($search[$i]['term']) . "\") ";
									break;
									
									case '=':
										$sql_where .= $conjunction . " lower(" . $search[$i]['field'] . ") " . $search[$i]['test'] . " \"" . strtolower($search[$i]['term']) . "\" ";
									default:	
								
								}
							}
						}
						
						if ($itemGroup['term'] != '')
						{
							if ($itemGroup['test'] == "=")
								$sql_where .= " AND item_group " . $itemGroup['test'] . " \"" . $itemGroup['term'] . "\" ";
							else
								$sql_where .= " AND item_group " . $itemGroup['test'] . " \"%" . $itemGroup['term'] . "%\" ";
						}
						for($i=0;$i<count($limit);$i++)
						{
							$conjunction = ($i > 0 && $limit[$i-1]['term'] != '') ? $limit[$i]['conjunct'] . " " : ""; 
							
							if ($limit[$i]['term'] != '')
							{		
								if ($limit[$i]['test'] == '=')
									$test = $limit[$i]['test'] . " \"" . $limit[$i]['term'] . "\" ";
								else
									$test = $limit[$i]['test'] . " \"%" . $limit[$i]['term'] . "%\" ";
	
								
								switch ($limit[$i]['field'])
								{
									default:
									case 'instructor':
										$sql_limit = " AND u.last_name $test ";
									break;
									
									case 'department':
										$sql_limit = " AND (d.name $test OR d.abbreviation $test) ";
									break;
									
									case 'course_name':
										$sql_limit = " AND c.course_name $test ";
								}
								
								$sql_where .= $conjunction . $sql_limit;
												
							}						
						}
					}
					else $sql_where = "";
			}
								
			$this->search_sql_statement = $sql_select . $sql_from . $sql_where;
		}	
		
		if (isset($sort) && !is_null($sort) && $sort != '')
		{
			$sql_sort = " ORDER BY i.$sort ";			
			$raw_sql = split("ORDER BY", $this->search_sql_statement);			
			$this->search_sql_statement = $raw_sql[0] . $sql_sort;
		}	

		$rs = $g_dbConn->query($this->search_sql_statement);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }
		
		
		$results = null;
		while ($row = $rs->fetchRow())
			$results[] = new reserveItem($row[0]);			
			
		return $results;
	}
}

?>
