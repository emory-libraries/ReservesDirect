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

// Unobrusive Javascript: Separate javascript from html page.
// Note: the following must be defined in the html page:
// var unobtrusive = new domFunction(unobtrusive, { 'Footer' : 'id'});
// Once element id='Footer' has been loaded, these onClick events are available.
function unobtrusive()
{
  if(document.getElementById('edit_url')) { // "Edit URL" button onclick event
      document.getElementById('edit_url').onclick = function() { 
        edit_url(); 
        return false; 
      };  
  }; 
  if(document.getElementById('get_url')) { // "Get URL" button onclick event
      document.getElementById('get_url').onclick = function() {  
        // Set the Document Type Icon to "Link"
        document.getElementById('selectedDocIcon').selectedIndex = 7;
        // Set the Document Type Icon Image to 'www'
        document.getElementById('iconImg').src = 'images/doc_type_icons/doctype-link.gif';
        // Unset radio button and disable the Browse for "Upload a document"
        document.getElementById('userFile').disabled = true; 
        document.getElementById('radioDOC').checked = false;        
        // Unset the radion button and enable to textbox for "Add a link"
        document.getElementById('url').disabled = false;  
        document.getElementById('radioURL').checked = true;
        // Pass in the metadata to openurl function to retrieve the openurl              
        document.getElementById('url').value = getopenurl(this.form);  
        return false; 
      };  
  };   
  if(document.getElementById('preview_url')) { // "Preview URL" button onclick event
      document.getElementById('preview_url').onclick = function() { 
        preview_url(document.getElementById('url').value); 
        return false; 
      };  
  };
  if(document.getElementById('timespagesrange')) { // Times/Pages Range onchange event
    document.getElementById('timespagesrange').onchange = function() { 
      ajaxCopyrightFunction('page_range_update'); 
      return false; 
    };  
  };   
  if(document.getElementById('timespagesused')) { // Total Used Pages onchange event
    document.getElementById('timespagesused').onchange = function() { 
      ajaxCopyrightFunction('used_total_update'); 
      return false; 
    };  
  };
  if(document.getElementById('timespagestotal')) {  // Total Pages in book onchange event
    document.getElementById('timespagestotal').onchange = function() { 
      ajaxCopyrightFunction('used_total_update'); 
      return false; 
    };  
  };
  if(document.getElementById('itemisbn')) {  // ISBN
    document.getElementById('itemisbn').onchange = function() { 
      ajaxCopyrightFunction('isbn_update'); 
      return false; 
    };  
  }; 
  if(document.getElementById('geturl')) { // "Get URL" button onclick event
      document.getElementById('geturl').onclick = function() {         
        // Pass in the metadata to openurl function to retrieve the openurl 
        document.getElementById('url').value = getopenurl(this.form);
      return false; 
    };  
  };
  if(document.getElementById('editurl')) { // "Get URL" button onclick event
      document.getElementById('editurl').onclick = function() {         
        edit_url(); 
      return false; 
    };  
  };  
};

// Onclick action for the "Edit URL" button
function edit_url() { 
  // Open a new window for the Open URL Generator form
  // FIXME: Add the existing metadata to the input parameters for this call.
  openWin('http://ejournals.emory.edu/openurlgen.php',860,550,'Open URL Generator');  
}
// Onclick action for the "Get URL" button      
function getopenurl(frm) {      
  var alertMsg = "";  
  alertMsg = get_url(frm);      
  return alertMsg;       
}
// Onclick action for the "Preview URL" button    
function preview_url(mypage) {
  var alertMsg = "";  
  if (mypage) { openWin(mypage,640,480,'sfxwin'); }
  else {
    alertMsg = 'Please enter a URL in the "Add a link" text box if you would like to "Test URL".';
  }
  document.getElementById('alertMsg').innerHTML = alertMsg;        
} 
  
// Several events trigger this function "ajaxCopyrightFunction" to be called.
// Anytime change in the form id values: times_pages(aka range) or used_times_pages or total_times_pages.
// The results include:
// 1. an update to the used_times_pages (a calculation based on the range), if needed (type=2).
// 2. an update to the percent_times_pages (based on combined ISBN results for course).
// this function calls a php script (secure/calculateCopyrightPercent.php) to calculate this data.

function ajaxCopyrightFunction(type){
  var ajaxRequest;  // Ajax request object
  
  // only do this processing for material type = BOOK_PORTION
  if (document.getElementById('material_type').value != 'BOOK_PORTION') {
    return false;   
  }   

  try{
    // Opera 8.0+, Firefox, Safari Browser Support
    ajaxRequest = new XMLHttpRequest();
  } catch (e) {
    // Internet Explorer Browser Support
    try{
      ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try{
        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {
        // Something went wrong
        alert("Your browser broke!");
        return false;
      }
    }
  }
  // this function receives data sent from the server
  ajaxRequest.onreadystatechange = function(){
    if(ajaxRequest.readyState == 4) {
      //alert("AJAX RESPONSE = " + ajaxRequest.responseText);   
      if (ajaxRequest.responseText != null) {
        var ajaxReturn = ajaxRequest.responseText;
        
        if (type == "page_range_update" || type == "used_total_update") {
          // 1. ajaxUsed = the range for the current item (it may be that the range has not been changed)
          // 2. ajaxPer = the copyright percentage for the current item.
          // 3. ajaxCombo = the combined copyright percentage for all items with the same ISBN in this course.             
          var s1 = ajaxReturn.indexOf(";");
          var s2 = ajaxReturn.indexOf(";", s1+1);
          var ajaxUsed = ajaxReturn.substring(0,s1);
          var ajaxPer = ajaxReturn.substring(s1+1,s2);
          var ajaxCombo = ajaxReturn.substring(s2+1,ajaxReturn.length);
          //alert("AJAX RESPONSE => " + ajaxRequest.responseText + "\najaxUsed = " + ajaxUsed + "\najaxPer = " + ajaxPer + "\najaxCombo = " + ajaxCombo);

          // Populate the "Total pages used in book" value        
          document.getElementById('timespagesused').value = parseInt(ajaxUsed); 
                   
          // Populate the Overall Book Usage Percentage value
          if (parseInt(ajaxCombo) > 0) {
            document.getElementById('percenttimespages').value = parseInt(ajaxCombo);
          }
          else {
            document.getElementById('percenttimespages').value = "";          
          }
        } // end type = 'page_range_updated' or "used_total_update"
        else if (type == "isbn_update") { // update the rightsholder information.
          var rha = eval(ajaxRequest.responseText);                  
          document.getElementById('rh_name').value = rha[0];
          document.getElementById("rh_contact_name").value = rha[1];
          document.getElementById("rh_contact_email").value = rha[2];
          document.getElementById("rh_fax").value = rha[3];
          document.getElementById("rh_rights_url").value = rha[4];
          document.getElementById("rh_policy_limit").value = rha[5];
          document.getElementById("rh_post_address").value = rha[6].replace("\<BR\>", "\n");
        } // end type = isbn_update
      } // end ajax response is not null
    } // end readyState = 4
  } // end onreadystatechange function

  var url_script = "AJAX_copyright.php";
  var url_range = "?range=" + encodeURIComponent(document.getElementById('timespagesrange').value);  
  var url_used = "&used=" + encodeURIComponent(document.getElementById('timespagesused').value);    
  var url_total = "&total=" + encodeURIComponent(document.getElementById('timespagestotal').value);  
  var url_isbn = "&isbn=" + encodeURIComponent(document.getElementById('itemisbn').value);
  var url_type = "&type=" + encodeURIComponent(type);
  var url_item = "&item=" + encodeURIComponent(document.getElementById('itemID').value);
  var url_ci = "&ci=" + encodeURIComponent(document.getElementById('ciid').value);  
  var url_params =  url_script + url_range + url_used + url_total + url_isbn + url_type + url_ci + url_item;
  //alert(url_params);
  ajaxRequest.open("GET", url_params, true);
  ajaxRequest.send(null); 
}

function replaceAll( str, searchTerm, replaceWith, ignoreCase ) {
  var regex = "/"+searchTerm+"/g";
  if( ignoreCase ) regex += "i";
  return str.replace( eval(regex), replaceWith );
}

function validateCopyrightPercentage() {
        
  var type = $('material_type').options[$('material_type').selectedIndex].value;
  if (type == "BOOK_PORTION") {
    $('material_type_other_block').style.display = 'inline';

    // Validation: Is copyright percentage within guideline limit?
    // get the value from Overall Book Usage
    var bookpercentage = document.getElementById('percenttimespages').value; 
    // retrieve the fair use guideline amount config property <copyright_limit>
    var limit = document.getElementById('copyright_limit').value;
    // if the value is present, then check for over the limit.
    if (document.getElementById('percenttimespages') != null && parseInt(bookpercentage) > parseInt(limit)) {          
      // the percentage has exceeded the limit.
      var msg = document.getElementById('copyright_notice').value
      // replace the placeholder in notice with config property <copyright_limit> value.
      msg = msg.replace("copyright_limit", limit);
      // Add some newlines to improve readability to notice (config property <copyright_notice>).
      msg = replaceAll(msg, "[\.] ", ".\n\n", false); 
      // Show confirmation popup box if copyright percentage is over guideline limit.        
      var answer = confirm(msg);
      // If the user selects 'Cancel' to the over limit popup, then abort save.          
      if (!answer) {  
        return false;           
      }
    }
    return true;
  } else {
    return true;
  }    
}
