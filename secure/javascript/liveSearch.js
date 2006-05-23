	/*
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 LibLime                                           |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Joshua Ferraro <jmf at liblime dot com>                      |
// | Modified by: Jason White <jbwhite at emory dot edu					  |
// | Thanks to Bitflux GmbH <devel at bitflux dot ch>                     |
// +----------------------------------------------------------------------+

*/
//GLOBAL values used to pass parameters to callback function liveSearchKeyPress	
var searchURL = null;			//server URL for data
var form_element = null;		//text box on the form
var div_result = null;			//div tag the displays returned data
var return_function = null;		//javascript function executed when results returned to browser
	
var liveSearchReq = false;
var t = null;
var liveSearchLast = "";
	
var isIE = false;


function liveSearchInitXMLHttpRequest()
{
	// on !IE we only have to initialize it once
	if (window.XMLHttpRequest) { // branch for IE/Windows ActiveX version
		liveSearchReq = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		liveSearchReq = new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function liveSearchInit(form_element) {
	
	if (navigator.userAgent.indexOf("Safari") > 0) {
		form_element.addEventListener("keydown",liveSearchKeyPress,false);
	} else if (navigator.product == "Gecko") {		
		form_element.addEventListener("keypress",liveSearchKeyPress,false);
		form_element.addEventListener("blur",liveSearchHideDelayed,false);
		
	} else {
		form_element.attachEvent('onkeydown',liveSearchKeyPress);

		isIE = true;
	}
	
	form_element.setAttribute("autocomplete","off");

}

function liveSearchHideDelayed() {
	window.setTimeout("liveSearchHide()",1000);
}
	
function liveSearchHide() {
	if (div_result){
		div_result.style.display = "none";
		var highlight = document.getElementById("LSHighlight");
		if (highlight) {
			highlight.removeAttribute("id");
		}
	}		
}

function liveSearchKeyPress(event) {
	if (event.keyCode == 40 )
	//KEY DOWN Arrow
	{
		highlight = document.getElementById("LSHighlight");
		if (!highlight) {
			//if (document.getElementById("LSShadow").firstChild)
			if (div_result.firstChild.firstChild)
			{
				highlight = div_result.firstChild.firstChild.firstChild;
				//highlight = document.getElementById("LSShadow").firstChild.firstChild;
			}
		} else {
			highlight.removeAttribute("id");
			highlight = highlight.nextSibling;
		}
		if (highlight) {
			highlight.setAttribute("id","LSHighlight");
		} 
		if (!isIE) { event.preventDefault(); }
	} 
	//KEY UP Arrow
	else if (event.keyCode == 38 ) {
		highlight = document.getElementById("LSHighlight");
		if (!highlight) {
			highlight = div_result.firstChild.firstChild.lastChild;			
		} 
		else {
			highlight.removeAttribute("id");
			highlight = highlight.previousSibling;
		}
		if (highlight) {
				highlight.setAttribute("id","LSHighlight");
		}
		if (!isIE) { event.preventDefault(); }
	} 
	//ESC
	else if (event.keyCode == 27) {
		highlight = document.getElementById("LSHighlight");
		if (highlight) {			
			highlight.removeAttribute("id");
		}
		div_result.style.display = "none";
	//RET
	} else if (event.keyCode == 13) {
		if (typeof(highlight) != "undefined") {
			form_element.value = htmlEntityDecode(highlight.innerHTML);
		}
		
		liveSearchHide();
		
		doClickEvent(highlight);
		return false;
	//TAB
	} else if (event.keyCode == 9) {
		highlight = document.getElementById("LSHighlight");
		if (highlight) {
			form_element.value = htmlEntityDecode(highlight.innerHTML);
			highlight.removeAttribute("id");
			liveSearchHide();	
			
			doClickEvent(highlight);
			return true;	
			
		} else {
			next=form_element;
			next++
		}
			
		return false;
	}
}

function doClickEvent(el){
		var clickEvent;
		if (isIE)
		{ //IE thinks a function should be enclosed by function anonymous() making it uncallable so we will strip that out
			var f  = el.getAttribute("onClick").toString();
			var c1 = f.indexOf('{') +1;
			var c2 = f.lastIndexOf('}');
			
			clickEvent = f.substring(c1,c2);
		} else {
			clickEvent = el.getAttribute("onClick");
		}
		eval (clickEvent);
}

function liveSearchStart(event, element, s_url, result_div, rf) {
	//ESC closes div
	if(event.keyCode == 27) {
		liveSearchHide();
		return;
	}
	//BACKSPACE/DEL - closes dif if query is empty
	else if ((event.keyCode == 8) || (event.keyCode == 46)) {
		if(element.value.length == 0) {
			liveSearchHide();
			return;
		}
	}
	
	if(event.keyCode != 9 && event.keyCode != 40 && event.keyCode != 38 && event.keyCode != 13) { //TAB, DOWN ARROW and UP ARROW and RETURN
		if (t) {
			window.clearTimeout(t);
		}
		
		//cannot pass function parameters when calling timeout so we will create global values
		form_element = element;
		searchURL = s_url;
		div_result = result_div;
		return_function = rf;
		
		t = window.setTimeout("liveSearchDoSearch()",200);
	}

}

function liveSearchDoSearch() {

	if (typeof liveSearchRoot == "undefined") {
		liveSearchRoot = "";
	}
	if (typeof liveSearchRootSubDir == "undefined") {
		liveSearchRootSubDir = "";
	}
	if (typeof liveSearchParams == "undefined") {
		liveSearchParams = "";
	}

	if (liveSearchLast != form_element) {
		if (liveSearchReq && liveSearchReq.readyState < 4) {
			liveSearchReq.abort();
		}
		if ( form_element == "") {
			liveSearchHide();
			return false;
		}
		
		liveSearchInitXMLHttpRequest();

		liveSearchReq.onreadystatechange = liveSearchProcessReqChange;
		liveSearchReq.open("GET", buildSearchURL());		
		liveSearchReq.send(null);
		
		liveSearchLast = form_element.value;	
	}
}

function buildSearchURL()
{
	if (searchURL.indexOf("?") > 0)
	{	
		url = liveSearchRoot + searchURL + "&qu=" + encode64(form_element.value) + "&rf=" + encode64(return_function);
	} else {
		url = liveSearchRoot + searchURL + "?qu=" + encode64(form_element.value) + "&rf=" + encode64(return_function);
	}
	
	return url;
}

function liveSearchProcessReqChange() {
	if (liveSearchReq.readyState == 4) {
		var  res = div_result;
		res.style.display = "block";
		
		var  sh = div_result.firstChild; //document.getElementById("LSShadow");		
		sh.innerHTML = liveSearchReq.responseText;
	}
}

function liveSearchSubmit() {
	var highlight = document.getElementById("LSHighlight");
	if (highlight && highlight.firstChild) {
		liveSearchHide();
		return true;
	} 
	else {
		//return true;
		return false;
	}
}

// for mouseovers
function liveSearchHover(el) {
		highlight = document.getElementById("LSHighlight");
		if (highlight) {
			highlight.removeAttribute("id");
		}
		el.setAttribute("id","LSHighlight");
}

function liveSearchClicked(el, returnParamList) {		
		if (typeof(el.innerHTML) != "undefined")
			form_element.value = el.innerHTML;
			
		liveSearchHide();
		
		if (typeof returnParamList == "undefined") {
			returnParamList = "";
		}
		eval (return_function + "('" + returnParamList + "')");
}

function htmlEntityEncode(str) {
	str = str.replace(/&/ig, "&amp;");
	str = str.replace(/</ig, "&lt;");
	str = str.replace(/>/ig, "&gt;");
	str = str.replace(/\xA0/g, "&nbsp;");
	str = str.replace(/\x22/ig, "&quot;");
	str = str.replace(/\u00e4/g, "&auml;");
	str = str.replace(/\u00c4/g, "&Auml;");
	str = str.replace(/\u00f6/g, "&ouml;");
	str = str.replace(/\u00d6/g, "&Ouml;");
	str = str.replace(/\u00fc/g, "&uuml;");
	str = str.replace(/\u00dc/g, "&Uuml;");
	str = str.replace(/\u00df/g, "&szlig;");
	return str;
}
function htmlEntityDecode(str) {
	str = str.replace(/\&amp;/ig, "&");
	str = str.replace(/\&lt;/ig, "<");
	str = str.replace(/\&gt;/ig, ">");
	str = str.replace(/\&nbsp;/g, "\xA0");
	str = str.replace(/\&quot;/ig, "\x22");
/*	str = str.replace(/\u00e4/g, "&auml;");
	str = str.replace(/\u00c4/g, "&Auml;");
	str = str.replace(/\u00f6/g, "&ouml;");
	str = str.replace(/\u00d6/g, "&Ouml;");
	str = str.replace(/\u00fc/g, "&uuml;");
	str = str.replace(/\u00dc/g, "&Uuml;");
	str = str.replace(/\u00df/g, "&szlig;");*/
	return str;
}

// Courtesy of aardwulf.com
function encode64(input) {
// for Base64 Encoding
	var keyStr = "ABCDEFGHIJKLMNOP" +
	            "QRSTUVWXYZabcdef" +
	            "ghijklmnopqrstuv" +
	            "wxyz0123456789+/" +
	            "=";	
	
  var output = "";
  var chr1, chr2, chr3 = "";
  var enc1, enc2, enc3, enc4 = "";
  var i = 0;

  do {
     chr1 = input.charCodeAt(i++);
     chr2 = input.charCodeAt(i++);
     chr3 = input.charCodeAt(i++);

     enc1 = chr1 >> 2;
     enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
     enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
     enc4 = chr3 & 63;

     if (isNaN(chr2)) {
        enc3 = enc4 = 64;
     } else if (isNaN(chr3)) {
        enc4 = 64;
     }

     output = output + 
        keyStr.charAt(enc1) + 
        keyStr.charAt(enc2) + 
        keyStr.charAt(enc3) + 
        keyStr.charAt(enc4);
     chr1 = chr2 = chr3 = "";
     enc1 = enc2 = enc3 = enc4 = "";
  } while (i < input.length);

  return output;
}

function decode64(encStr) {
  var base64s = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";	
  var bits, decOut = '', i = 0;
  for(; i<encStr.length; i += 4){
    bits =
     (base64s.indexOf(encStr.charAt(i))    & 0xff) <<18 |
     (base64s.indexOf(encStr.charAt(i +1)) & 0xff) <<12 | 
     (base64s.indexOf(encStr.charAt(i +2)) & 0xff) << 6 |
      base64s.indexOf(encStr.charAt(i +3)) & 0xff;
    decOut += String.fromCharCode(
     (bits & 0xff0000) >>16, (bits & 0xff00) >>8, bits & 0xff);
    }
  if(encStr.charCodeAt(i -2) == 61)
    undecOut=decOut.substring(0, decOut.length -2);
  else if(encStr.charCodeAt(i -1) == 61)
    undecOut=decOut.substring(0, decOut.length -1);
  else undecOut=decOut;
  
  return unescape(undecOut);		//line add for chinese char
  }