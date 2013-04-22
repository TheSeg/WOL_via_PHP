<!--
// This AJAX-function retrieves the value of the "Manufacturer of NIC" field.
function showValue(str) {
	var xmlHttp=null;
	// try to instantiate AJAX-object in "xmlHttp"
	try {
		// AJAX for Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e) {
		//
		try {
			// AJAX for Internet Explorer 6.0+
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				// AJAX for Internet Explorer 5.5+
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				// browser does not support AJAX
				alert ("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	// check whether AJAX-object exists
	if (xmlHttp==null) {
		// browser does not support AJAX
		return false;
	}
	// Build and send URL
	var url="NIC_manufacturer.php";
	url=url+"?ajaxVariable="+str;
	url=url+"&sid="+Math.random();
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	// If webserver returns a response
	xmlHttp.onreadystatechange=function()
	{
		// fill innerHTML in class="ajaxDIV" with the AJAX-response from server
		if(xmlHttp.readyState==4) {
			if (xmlHttp.status==200) {
				var ajaxDisplay=document.getElementById("ajaxDIV");
				if(ajaxDisplay != null) {
					ajaxDisplay.innerHTML=xmlHttp.responseText;
				}
			}
			else {
				// Problem retrieving XML data
				return false;
			}
		}
	}
}

function showPrefill() {
	// Prefill contents based on value of input-field (in case user has not changed anything)
	document.getElementById("ajaxDIV").innerHTML=showValue(document.getElementById('WOL_mac_address').value);
}
//-->