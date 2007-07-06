/*******************************************************************************
request_ajax.js

Created by Dmitriy Panteleyev (dpantel@gmail.com)

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


*******************************************************************************

	JS used for adding items
	to courses (filling requests)
	via AJAX
	
	requires basicAJAX.js
	
	Note: functions are named
		for consistency and to
		prevent name clashes
	
******************************************************************************/

//create the basicAJAX object
//and init a new xmlhttprequest
var request_ajax = new basicAJAX();


/**
 * @desc If successfully create an 'ajax' object, then will add an ajax call as "onsubmit" event to all 'create_reserve_form' elements; Basically this is a safe way to add AJAX which will not break the form, should Javascript be disabled
 */
function request_ajaxify_forms() {
	//if we have a valid ajax object, then ajaxify forms
	if(request_ajax.ajax) {
		//grab all the forms
		var forms = document.getElementsByName('create_reserve_form');
		//add an onsubmit call to the ajax version of form-submit
		for(var x=0; x < forms.length; x++) {
			//also make sure that the form has a CI element
			//too much depends on it and should not try ajax without it
			if(forms[x].elements['ci']) {
				//by registering the onsubmit event in this fashion
				//`this` inside request_submit_form() will refer to the form object
				forms[x].onsubmit = request_submit_form;
			}
		}
	}
}


/**
 * @desc Function called by the "onsubmit" event; Sends form data via AJAX to the responder
 */
function request_submit_form() {
	//need to get the value of 'ci' field, because all the element IDs are based on that value
	var ci_id = this.elements['ci'].value;
	
	//grab all the data from the form
	//var form_data = request_get_form_data(this);
	var form_data = request_ajax.get_form_data(this);

	//hide the form and show a "working on it" message
	request_display_inprogress(ci_id);
	
	//make ajax call
	request_ajax.setResponseCallback(request_display_results, ci_id);
	request_ajax.post("AJAX_functions.php?f=storeRequest", form_data);
	
	//do not submit the form the old-fashioned way
	return false;
}


/**
 * @desc Replaces the create-reserve form with "in progress" message;  May flicker on-and-off if the ajax call is very quick
 */
function request_display_inprogress(ci_id) {
	if(document.getElementById('add_' + ci_id)) {
		//replace form with "please wait" message
		document.getElementById('add_' + ci_id).innerHTML = '<div class="borders" style="margin:10px; padding:10px; background:lightblue; text-align:center"><strong>Creating reserve... please wait</strong></div>';
	}
}


/**
 * @desc Replaces "in progress" message with success/failure message from ajax responder
 */
function request_display_results(ci_id) {
	//disable radio button so that this course is no longer selectable
	if(document.getElementById('select_ci_' + ci_id)) {
		document.getElementById('select_ci_' + ci_id).disabled = true;
	}
	//show result message
	if(document.getElementById('add_' + ci_id)) {
		//show result
		document.getElementById('add_' + ci_id).innerHTML = request_ajax.getResponse('text');
		
		//this is a really hacky way to make sure that this div can no longer be manipulated
		//this is done so that if user selects another class, this message is not hidden
		document.getElementById('add_' + ci_id).id = 'add_' + ci_id + '_done';
	}
}