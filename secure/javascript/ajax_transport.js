/*******************************************************************************
ajax_transport.js

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2008 Emory University, Atlanta, Georgia.

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


*******************************************************************************
This function passes data to a url and indicates to the user that data is being transmitted then that the 
action occurred succeeded or failed

requires the prototype framework prototype.js

*/

/**
 * function ajax_transport(url, notice, successString)
 * @param string url:    		url to call includes any needed query_string args
 * @param string notice: 		DOM id of element to notify user of status
 * @desc method calling foreign method and indicating 
 */
function ajax_transport(url, notice_id)
{	
	var notice = $(notice_id);
	
	new Ajax.Request(url, {
	  method: 'get',
	  onLoading: function() {	    
	    notice.update("<img src='images/hour_glass.gif' width='16px' height='16px'/>").setStyle({display: inline});	  	
	  },
	  onSuccess: function(transport) {
	    notice.update(transport.responseText);
	  },
	  onFailure: function(transport) {
	  	alert("Error! not updated. " + transport.status);
	  	notice.update("");
	  }
	});
}