/************************************

	JS used for adding/editing
	help tags via AJAX
	
	requires basicAJAX.js
	
	Note: functions are named
		for consistency and to
		prevent name clashes
	
************************************/

//create the basicAJAX object
//and init a new xmlhttprequest
var help_ajax = new basicAJAX();


/**
 * Requests array of tags for given article
 */
function help_fetch_tags(article_id) {
	help_ajax.setResponseCallback(help_display_tags);
	help_ajax.get("AJAX_functions.php?f=fetchHelpTags", "article_id=" + article_id);
}


/**
 * Saves new tags for the article
 */
function help_save_tags(article_id) {
	var tags_string = '';

	if(document.getElementById('help_tags_input')) {
		tags_string = document.getElementById('help_tags_input').value;
		
		help_ajax.setResponseCallback(help_fetch_tags, article_id);
		help_ajax.post("AJAX_functions.php?f=saveHelpTags", "article_id=" + article_id + "&tags_string=" + tags_string);
	}
	
	help_toggle_tag_edit_form(0);
}


/**
 * Displays tags for the article
 */
function help_display_tags() {
	if(document.getElementById('help_tags')) {
		document.getElementById('help_tags').innerHTML = help_ajax.getResponse('text');
	}
}


/**
 * Toggles tag input form
 */
function help_toggle_tag_edit_form(show) {
	if(document.getElementById('help_tag_list_edit')) {
		if(show) {
			document.getElementById('help_tag_list_edit').style.display = '';
		}
		else {
			document.getElementById('help_tag_list_edit').style.display = 'none';
		}
	}
	if(document.getElementById('help_tag_list')) {
		if(show) {
			document.getElementById('help_tag_list').style.display = 'none';
		}
		else {
			document.getElementById('help_tag_list').style.display = '';
		}
	}
	
	return false;
}