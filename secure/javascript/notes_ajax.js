/************************************

	JS used for adding, editing,
	and deleting notes via AJAX
	
	requires basicAJAX.js
	
	Note: functions are named
		for consistency and to
		prevent name clashes
	
************************************/

//create the basicAJAX object
//and init a new xmlhttprequest
var notes_ajax = new basicAJAX();


/**
 * @param string obj_type - Type of object containing notes (`reserve`/`item`)
 * @param int obj_id - ID of the object
 * @desc Requests an array of note objects for this reserve/item
 */
function notes_fetch_notes(obj_type, obj_id) {
	notes_ajax.setResponseCallback(notes_display_notes, obj_type, obj_id);
	notes_ajax.get("AJAX_functions.php?f=fetchNotes", "obj_type=" + obj_type + "&id=" + obj_id);
}


/**
 * @param int note_id - ID of the note
 * @desc Deletes the specified note
 */
function notes_delete_note(obj_type, obj_id, note_id) {
	if(confirm("Are you sure you want to delete this note?")) {
		notes_ajax.setResponseCallback(notes_fetch_notes, obj_type, obj_id);
		notes_ajax.get("AJAX_functions.php?f=deleteNote", "id=" + note_id);
	}
}


/**
 * @param string obj_type - Type of object containing notes (`reserve`/`item`)
 * @param int obj_id - ID of the object
 * @param obj note_form_obj - the note add/edit form element
 * @param string note_type_element_id - ID of the DOM element containing the type of note to add/edit
 * @param int note_id_element_id - ID of the DOM element containing the ID of the note
 * @desc Send off information to add or edit the note text and type
 */
function notes_save_note(obj_type, obj_id, note_form_obj) {
	var note_text, note_type, note_id;

	//gather all the data
	//must do this before hiding the form, b/c once the form is hidden, some elements are no longer accessible
	note_text = note_form_obj.note_text.value;
	note_id = note_form_obj.note_id.value;
	//have to find the type
	for(var x=0; x<note_form_obj.note_type.length; x++) {
		if(note_form_obj.note_type[x].checked) {
			note_type = note_form_obj.note_type[x].value;
			break;
		}
	}
	
	//hide the note form	
	notes_hide_form();

	if(note_text != "") {	//do not bother doing anything with blank notes
		notes_ajax.setResponseCallback(notes_fetch_notes, obj_type, obj_id);
		notes_ajax.post("AJAX_functions.php?f=saveNote", "obj_type=" + obj_type + "&id=" + obj_id + "&note_text=" + note_text + "&note_type=" + note_type + "&note_id=" + note_id);
	}
}


/**
 * @desc sets the `item_notes` div's contents to ajax response text
 */
function notes_display_notes() {
	if(document.getElementById('item_notes')) {
		document.getElementById('item_notes').innerHTML = notes_ajax.getResponse('text');
	}
}


/**
 * @param note_id Note ID
 * @param note_text Note text
 * @param note_type Type of note
 * @desc Displays add/edit note form, prefilling it with values
 */
function notes_show_form(note_id, note_text, note_type) {
	//set the data
	
	//set the id
	if(document.getElementById('note_id')) {
		document.getElementById('note_id').value = note_id;
	}	
	//set the type
	if(document.getElementById('note_type_' + note_type)) {
		document.getElementById('note_type_' + note_type).checked = true;
	}	
	//finally set the textfield text
	if(document.getElementById('note_text')) {
		document.getElementById('note_text').value = note_text;
	}
	
	//show the block
	if(document.getElementById('noteform_container')) {
		document.getElementById('noteform_container').style.display = 'block';
	}
}


/**
 * @desc Resets and hides the add/edit note form
 */
function notes_hide_form() {
	//reset form
	notes_show_form('', '', '');
	
	//hide it
	if(document.getElementById('noteform_container')) {
		document.getElementById('noteform_container').style.display = 'none';
	}
}