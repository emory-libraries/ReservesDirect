/************************************

	Very basic AJAX object
	
************************************/

/**
 * @desc basicAJAX class
 */
function basicAJAX() {
	/**
	 * Declaration
	 */
	//ajax stuff
	var ajax = false;			//holds the ajax obj
	this.ajax = false;			//public reference to the ajax object
	//these are to augment the onreadystatechange functionality	
	var onResponseCallback = false;	//name of function to call on response
	var onResponseCallbackArgs;
	//need this for base-64 coding
	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	
	
	/**
	 * @desc initializes the actual ajax object
	 */	
	this.init = function() {		
		try {
			ajax = new XMLHttpRequest();
		}
		catch(e) {	//if the above try did not work, this is probably IE, so start an ActiveXObject
			//there are newer versions of the xmlhttp object, but this one should do
			ajax = new ActiveXObject("Microsoft.XMLHTTP");
		}
					
		if(!ajax) {
			alert("Error: Could not initialize XMLHTTP object");
			return false;
		}
		
		//link the public reference to the object
		this.ajax = ajax;
	}
	
	
	//run the constructor by default
	this.init();
	
	
	/**** END "CONSTRUCTOR" ****/
	

	/**
	 * @desc private method to filter readystate changes; this method must be defined before setResponseCallback()
	 */
	var process_readyState = function() {
		var args_string = '';
		var callback_string = '';

		if(ajax.readyState == 4) {	//complete
			//call the callback function
			if(onResponseCallback) {
				//check if there are arguments to pass
				if(onResponseCallbackArgs.length > 0) {
					//build the arguments string
					for(var x=0; x<onResponseCallbackArgs.length; x++) {
						args_string = args_string + onResponseCallbackArgs[x] + ', ';
					}

					//clean off the trailing `,`
					args_string = args_string.substring(0, args_string.lastIndexOf(','));
				}

				//build the callback string for eval
				callback_string = "onResponseCallback(" + args_string + ");";
										
				//eval the callback
				eval(callback_string);
			}
		}
	}
	
	
	/**
	 * @param string func first parameter is the function 
	 * (@param mixed ... anything else passed to this method will be treated as arguments for the callback function)
	 * @desc method for setting an on-response callback function with arguments
	 */
	this.setResponseCallback = function(func) {
		if(arguments.length == 0) {
			return false;
		}
		else if(arguments.length >= 1) {
			//re-init the ajax object
			this.init();

			//override the readystatechange callback
			ajax.onreadystatechange = process_readyState;
			
			//first argument is the callback function name
			onResponseCallback = arguments[0];
					
			//reset arguments array
			onResponseCallbackArgs = new Array();
			//populate arguments array
			for(var x=1; x<arguments.length; x++) {
				//escape strings with quotes
				if(typeof arguments[x] == 'string') {
					onResponseCallbackArgs[x-1] = '"' + arguments[x] + '"';
				}
				else {
					onResponseCallbackArgs[x-1] = arguments[x];
				}				
			}			
		}
	}
	
	
	/**
	 * @param string url - URL of the ajax responder
	 * @param query - The query string. format: var1=val1&var2=val2&var3....
	 * @desc Sends base64-encoded data as the value for variable `query` via GET
	 */	
	this.get = function(url, query) {
		//do nothing if no url
		//do not check the data, b/c it may be part of the url
		if(url == "") {
			return false;
		}
	
		//send all the data as a base-64 encoded string value for variable `query`
		ajax.open("GET", url + "&query=" + this.base64_encode(query));
		ajax.send(null);
	}
	
	
	/**
	 * @param string url - URL of the ajax responder
	 * @param query - The query string. format: var1=val1&var2=val2&var3....
	 * @desc Sends base64-encoded data as the value for variable `query` via POST
	 */	
	this.post = function(url, query) {
		//do nothing if no url and/or data
		if((url == "") || (query == "")) {
			return false;
		}
		
		ajax.open("POST", url);
		//send necessary headers
		ajax.setRequestHeader("Connection", "close");
        ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
       	ajax.setRequestHeader("Method", "POST " + url + "HTTP/1.1");
       	//send all the data as a base-64 encoded string value for variable `query`
		ajax.send("query=" + this.base64_encode(query));		
	}
	
	
	/**
	 * @param string type Type of response to get - 'xml' or 'text'
	 * @desc returns the ajax response
	 */
	this.getResponse = function(type) {
		if(type=='xml') {
			return ajax.responseXML;
		}
		else {
			return ajax.responseText;
		}
	}
	
	
	/**
	 * @return mixed
	 * @param string - json-encoded data string
	 * @desc Returns decoded json-encoded string
	 */
	this.json_decode = function(encoded_data) {
		eval("var decoded_data = "+encoded_data);
		return decoded_data;
	}	
	
	
	/**
	 * @desc base-64 encoding; taken from www.aardwulf.com
	 */
	this.base64_encode = function(input) {
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
	
	/**
	 * @desc base-64 decoding; taken from www.aardwulf.com
	 */
	this.base64_decode = function(input) {
		var output = "";
		var chr1, chr2, chr3 = "";
		var enc1, enc2, enc3, enc4 = "";
		var i = 0;
		
		// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
		var base64test = /[^A-Za-z0-9\+\/\=]/g;
		if (base64test.exec(input)) {
			alert("There were invalid base64 characters in the input text.\n" +
			"Valid base64 characters are A-Z, a-z, 0-9, '+', '/', and '='\n" +
			"Expect errors in decoding.");
		}
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		
		do {
			enc1 = keyStr.indexOf(input.charAt(i++));
			enc2 = keyStr.indexOf(input.charAt(i++));
			enc3 = keyStr.indexOf(input.charAt(i++));
			enc4 = keyStr.indexOf(input.charAt(i++));
			
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			
			output = output + String.fromCharCode(chr1);
			
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
			
			chr1 = chr2 = chr3 = "";
			enc1 = enc2 = enc3 = enc4 = "";	
		} while (i < input.length);
		
		return output;
	}
}