<?php

class WOL {
  
  // Custom error handler
  public static function customError($error_level,$error_message,$error_file,$error_line,$error_context)
  {
  	global $handler_error;
  	if (substr_count($error_message, "fsockopen() [") && substr_count($error_message, "]: unable to connect to udp://") && substr_count($error_message, " (Permission denied)"))
  	{
  		// Since this error is already caught, it can be surpressed.
  	}
  	else
  	{
  		if (substr_count($error_message, "It is not safe to rely on the system's timezone settings. "))
  		{
  			$_SESSION['local_timezone_set'] = "Since no timezone was specified, the timezone of the webserver will be used.<br>\n";
  		}
  		else
  		{
  			$_SESSION['handler_error'] = $_SESSION['handler_error']."<br>error_level: ".$error_level."<br>error_message: <b>".$error_message."</b><br>error_file: ".$error_file."<br>error_line: ".$error_line."<br>error_context: ".$error_context."<br>\n";
  		}
  	}
  	return;
  }
  
  
  // Custom error handler
  public static function customError_NIC($error_level,$error_message,$error_file,$error_line,$error_context)
{
	echo ("<br>error_level: ".
	$error_level."<br>error_message: <b>".
	$error_message."</b><br>error_file: ".
	$error_file."<br>error_line: ".
	$error_line."<br>error_context: ".
	//$error_context['handler_error']."<br><br>"
	print_r($error_context['_REQUEST'])."<br><br>"
	);
	return;
}
  
  
}
?>