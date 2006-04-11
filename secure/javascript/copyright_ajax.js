/************************************

	JS used for adding/editing
	copyright contacts via AJAX
	
	requires basicAJAX.js
	
	Note: functions are named
		for consistency and to
		prevent name clashes
	
************************************/

//create the basicAJAX object
//and init a new xmlhttprequest
var copyright_ajax = new basicAJAX();
		
		
function contact_toggle_form(show) {
	if(document.getElementById('contactform_container')) {
		if(show) {
			document.getElementById('contactform_container').style.display = '';
		}
		else {
			document.getElementById('contactform_container').style.display = 'none';
		}
	}
}

function contact_edit_contact() {
	var contact_id;
	
	if(document.getElementById('contact_id')) {
		contact_id = document.getElementById('contact_id').value;
	}
	else {
		return false;
	}
	
	copyright_ajax.setResponseCallback(contact_populate_form);
	copyright_ajax.get("AJAX_functions.php?f=fetchCopyrightContact", "contact_id=" + contact_id);
}

function contact_add_contact() {
	//reset form
	if(document.getElementById('contact_form')) {
		document.getElementById('contact_form').reset();
	}
	
	//show form
	contact_toggle_form(1);	
}

function contact_populate_form() {
	//decode json data
	var contact = copyright_ajax.json_decode(copyright_ajax.getResponse('text'));

	//populate form
	if(document.getElementById('edit_contact_id')) {
		document.getElementById('edit_contact_id').value = contact['contact_id'];
	}
	if(document.getElementById('contact_org_name')) {
		document.getElementById('contact_org_name').value = contact['org_name'];
	}
	if(document.getElementById('contact_address')) {
		document.getElementById('contact_address').value = contact['address'];
	}
	if(document.getElementById('contact_phone')) {
		document.getElementById('contact_phone').value = contact['phone'];
	}
	if(document.getElementById('contact_email')) {
		document.getElementById('contact_email').value = contact['email'];
	}
	if(document.getElementById('contact_www')) {
		document.getElementById('contact_www').value = contact['www'];
	}
	if(document.getElementById('contact_name')) {
		document.getElementById('contact_name').value = contact['contact_name'];
	}
	
	//show form
	contact_toggle_form(1);			
}

function contact_save_contact(formObj) {
	var id, org_name, address, phone, email, www, contact_name;
	
	if(document.getElementById('edit_contact_id')) {
		id = document.getElementById('edit_contact_id').value;
	}
	if(document.getElementById('contact_org_name')) {
		org_name = document.getElementById('contact_org_name').value;
	}
	if(document.getElementById('contact_address')) {
		address = document.getElementById('contact_address').value;
	}
	if(document.getElementById('contact_phone')) {
		phone = document.getElementById('contact_phone').value;
	}
	if(document.getElementById('contact_email')) {
		email = document.getElementById('contact_email').value;
	}
	if(document.getElementById('contact_www')) {
		www = document.getElementById('contact_www').value;
	}
	if(document.getElementById('contact_name')) {
		contact_name = document.getElementById('contact_name').value;
	}
	
	
	//hide the form	
	contact_toggle_form(0);

	copyright_ajax.setResponseCallback(null);
	copyright_ajax.post("AJAX_functions.php?f=saveCopyrightContact", "contact_id=" + id + "&org_name=" + org_name + "&address=" + address + "&phone=" + phone + "&email=" + email + "&www=" + www + "&contact_name=" + contact_name);
}

function contact_set_contact() {
	var item_id, contact_id;
	if(document.getElementById('contact_item_id') && document.getElementById('contact_id')) {
		item_id = document.getElementById('contact_item_id').value;
		contact_id = document.getElementById('contact_id').value;
	}
	else {
		return false;
	}

	copyright_ajax.setResponseCallback(null);
	copyright_ajax.get("AJAX_functions.php?f=setCopyrightContact", "item_id=" + item_id + "&contact_id=" + contact_id);
}