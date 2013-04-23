<?php

include_once("./Includes/wol_class.php");

// No caching
header("Cache-Control: no-cache, must-revalidate");
// Expired date
header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");

// Custom error handler
set_error_handler(array(new WOL(),'customError_NIC')); //set error handler

session_start();
// Get $ajaxVariable from URL
$ajaxVariable=$_GET["ajaxVariable"];
// Start checking only if sufficient characters are available
if (strlen($ajaxVariable) < 8)
{
	// Send "no suggestion"
	echo ("<b>Incomplete MAC-address</b>".$_SESSION['MAC_array_source']);
}
else
{
	if (isset($_SESSION['MAC_array']))
	{
		// Matches the first three hexadecimal bytes of MAC-address
		$ajaxVariable=strtoupper($ajaxVariable);
		$ajaxVariable=str_replace(":", "-", $ajaxVariable);
		$left_nine_chrs = substr($ajaxVariable,0,8);
		if(array_key_exists($left_nine_chrs, $_SESSION['MAC_array'])) {
			// Send result
			echo ("<b>".$_SESSION['MAC_array'][$left_nine_chrs]."</b>".$_SESSION['MAC_array_source']);
		}
		else
		{
			echo ("<b>Unregistered manufacturer</b> (".$left_nine_chrs.")\n<br />".$_SESSION['MAC_array_source']);
		}
	}
	else
	{
		echo ("<b>Manufacturer database inaccessible</b>".$_SESSION['MAC_array_source']);
	}
}
?>