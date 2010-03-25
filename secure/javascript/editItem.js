/** common javascript functions for addDigitalItem and editItem **/

/* NOTE: these functions assume you have initialized a javascript variable called 
   materialType_details from php like this:
   
  <script type="text/javascript">
    var materialType_details = <?= json_encode(common_materialTypesDetails()) ?>;
  </script>

  Also assumes there is a table with class editItem.
*/

// update form based on type of material selected
function typeOfMaterial() {
  var type = $('material_type').options[$('material_type').selectedIndex].value;
  var type_details = materialType_details[type];
  for (var field in type_details) {
    var tr = $(field);
    tr.show();
    if (type_details[field]["label"]) {
      tr.cells[0].innerHTML = type_details[field]["label"] + ":";
    }
    if (type_details[field]["required"]) {
      tr.className = "required";
    } else {
      tr.className = "";
    }
    if (type_details[field]["options"]) {
      $(field + "_option0").innerHTML = type_details[field]["options"][0];
      $(field + "_option1").innerHTML = type_details[field]["options"][1];
      
    }
  }
  var edit_tables = $$('.editItem');
  var table = edit_tables[0];
  // hide all rows not listed in current type config
  for (var i = 0; i < table.rows.length; i++) {
    row = table.rows[i];
    if (row.id && ! type_details[row.id]) {
      // FIXME: should inputs be made inactive?
      $(row.id).hide();
    }
  }
  
  // show/hide type of material text input field for type 'OTHER'
  if (type == "OTHER") {
    $('material_type_other_block').style.display = 'inline';
  } else {
    $('material_type_other_block').hide();
  }

  // check for uploading file as journal article (only on addItem)
  if ((type == "JOURNAL_ARTICLE") && document.forms[0].documentType[0].checked 
  && document.forms[0].documentType[0].value == "DOCUMENT") {
    $('openurl_link').show();
  } else if ($('openurl_link')) {
    $('openurl_link').hide();
  }
  

} 


// do form-validation for material-type portion of add/edit form
function checkMaterialTypes(form) {
  // remove any 'incomplete' markings
  var edit_tables = $$('.editItem');
  var table = edit_tables[0];
  for (var i = 0; i < table.rows.length; i++) {
    row = table.rows[i];
    if (row.cells[1]) {
      row.cells[1].className = "";
    }
  }
  var alertMsg = '';

  // material type is now required
  if ($('material_type').options[$('material_type').selectedIndex].value == '') {
    alertMsg += 'Please select type of material.<br/>';
    form.material_type.parentNode.className = 'incomplete';
  } else {
    var type = $('material_type').options[$('material_type').selectedIndex].value;
   
    // special-case for material type 'other' 
    if ((type == "OTHER") && ($('material_type_other').getValue() == '')) {
      alertMsg += 'Type of material must be specified when "Other" is selected.<br/>';
      form.material_type.parentNode.className = 'incomplete';
      form.material_type.parentNode.className = 'incomplete';
    } else {
      form.material_type.parentNode.className = '';
    }
    
    // check all required fields for current type of material
    var type_details = materialType_details[type];
    for (var field in type_details) {
      if (type_details[field]["required"]) {
  var tr = $(field);
  var inputs = tr.select('input[type="text"]');
  var radio_inputs = tr.select('input[type="radio"]');
  if ((inputs.length && inputs[0].getValue() == '') ||
      (radio_inputs.length && (! radio_inputs[0].checked)
       && (! radio_inputs[1].checked))) {
    alertMsg += type_details[field]['label'] + ' is required.<br/>';
    tr.cells[1].className = 'incomplete';
  }
      }
    }
  }
  
  return alertMsg;
}
