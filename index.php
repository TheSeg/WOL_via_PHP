<?php

require_once("./Includes/wol_class.php");

session_start();
// Check if an array of MAC-addresses is available
if (!$_SESSION['MAC_array'])
{
	if(file_exists('./ext/oui.txt'))
	{
		$_SESSION['MAC_array_source'] = " (data source: file './ext/oui.txt' on local filesystem of webserver)";
		$file_array = file('./ext/oui.txt');
	}
	else
	{
		// Import file to RAM
		$file_array = @file('http://standards.ieee.org/regauth/oui/oui.txt');
		// If no local file, try online
		if($file_array)
		{
			$_SESSION['MAC_array_source'] = " (data source: <a href=\"http://standards.ieee.org/regauth/oui/oui.txt\" target=\"_blank\">remote file</a>)";
			// Put a local copy in the directory of this script
			file_put_contents('./ext/oui.txt', $file_array);
		}
		else
		{
			$_SESSION['MAC_array_source'] = " (data source: locally nor remotely available)";
		}
	}
	if ($file_array)
	{
		// Build an array of MAC-addresses from $_SESSION['MAC_array']
		$i=0;
		foreach ($file_array as $key => $value)
		{
			if(substr_count($value,"   (hex)") == 1)
			{
				$delimiter = strpos($value,"   (hex)");
				$_SESSION['MAC_array'][substr($value,0,$delimiter)]=ltrim(substr($value,$delimiter+8,strlen($value)-($delimiter+8)),"\t");
				$i++;
			}
		}
	}
}
else
{
	$_SESSION['MAC_array_source'] = " (data source: cached from RAM)";
}
// To support proxy users (unreliable/ unsecure function)
if ( !isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )
{
	$current_client_IP = $_SERVER["REMOTE_ADDR"];
}
else
{
	$current_client_IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
}
if (isset($_COOKIE['WOL'])) {
	// Read and decrypt cookie
	require("Includes/Decryption.php");
	$decrypted = Decryption(base64_decode($_COOKIE["WOL"]));
	// Prefill variables with contents of cookie
	$CutCookie = explode("<->", $decrypted);
	$Prefix = $CutCookie[0];
	$http_user_agent = $CutCookie[1];
	$time_string = $CutCookie[2];
	if ($time_string == "+0 seconds")
	{
		$time_string = "";
	}
	$mac_address = $CutCookie[3];
	$secureon = $CutCookie[4];
	$addr = $CutCookie[5];
	if ($addr == $current_client_IP)
	{
		$addr = "";
	}
	$cidr = $CutCookie[6];
	if ($cidr == "0")
	{
		$cidr = "";
	}
	$port = $CutCookie[7];
	$store = $CutCookie[8];
	$Postfix = $CutCookie[9];
	// Check salt (pre- and postfix) to check if cookie has been tampered with
	if (($Prefix != "prefix") || ($Postfix != "postfix")) {
		$set_cookie=1;
	}
	// Compare http_user_agent to check if cookie has been stolen
	if ($http_user_agent != $_SERVER['HTTP_USER_AGENT']) {
		$set_cookie=1;
	}
}
else {
	// If there is no cookie
	$set_cookie=1;
}
// Set default values
if ($set_cookie == 1) {
	$time_string = "+3 seconds";
	$mac_address = "00:00:00:00:00:00";
	$secureon = "";
	$addr = "";
	$cidr = "24";
	$port = "9";
	$store = "No";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></meta>
<link href="./style/wol_main.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="./lib/jquery.min.js"></script>
<script type="text/javascript" src="./lib/wol_js.js"></script>
<script tyle="text/javascript">

</script>
<title>Wake-On-Lan (WOL) - Input</title>
</head>
<body>
<h1>WOL via PHP</h1>
<p>Main functionality of this application: wake up remote devices that support it, such as WOL-enabled clients.</p>
<!-- FORM -->
<form method="POST" action="WOL_script.php" name="WOL_form.php">
<fieldset>
	<legend>Schedule</legend>
	<table>
		<tr>
			<td>
				<label for="WOL_time_string">
					<p>
					Enter either the [date/time at] or [delay after] which the magic packet has to be send.<br></br>
					Enter a value in <a href="http://www.gnu.org/software/tar/manual/html_node/tar_113.html" target="_blank">this</a> input format.<br></br>
					Note: To prevent abuse, a minimum delay of 3 seconds will be set.<br></br>
					Leave the following field empty, if the magic packet needs to be send ASAP, after sending the WOL-request.<br></br>
					Current date and time of the web server: <b><?php echo date("d-m-y H:i:s e",time()); ?></b> (dd-mm-yyyy hh:mm:ss <a href=\"http://convertit.com/Go/ConvertIt/World_Time/Current_Time.ASP\" target=\"_blank\">timezone</a>).<br></br>
					</p>
				</label>
			</td>
			<td>
				<div id="WOL_time_string"><input type="date" name="date_string_ui" /><input type="time" name="time_string_ui" />
					<input type="text" required name="time_string" size="100" value="<?php echo $time_string; ?>"></input><br></br>
				</div>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>MAC-address</legend>
	<table>
		<tr>
			<td>
				<label for="WOL_mac_address">
					<p>
					Enter the MAC-address of the (possibly embedded) Network Interface Card (NIC) of the remote host.<br></br>
					Enter a value in this pattern: "xx-xx-xx-xx-xx-xx"<br></br>
					Manufacturer of NIC: <span id="ajaxDIV"></span><br></br>
					</p>
				</label>
			</td>
			<td>
					<input type="text" required id="WOL_mac_address" name="mac_address" size="17" placeholder="<?php echo $mac_address; ?>" onchange="showValue(this.value)"></input><br></br>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>SecureOn</legend>
	<table>
		<tr>
			<td>
				<label for="WOL_secureon">
					<p>
					Enter a hexadecimal password of a SecureOn enabled Network Interface Card (NIC) of the remote host.<br></br>
					Enter a value in this pattern: "xx-xx-xx-xx-xx-xx"<br></br>
					Leave the following field empty, if SecureOn is not used (for example, because the NIC of the remote host does not support SecureOn).<br></br>
					</p>
				</label>
			</td>
			<td>
					<input type="text" id="WOL_secureon" name="secureon" size="17" value="<?php echo $secureon; ?>"></input><br></br>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>Broadcast address</legend>
	<table>
		<tr>
			<td>
				<label for="WOL_addr">
					<p>
					Enter the host name (e.g. a FQDN from a dynamic DNS) or the IP address of the remote host's broadcast address (or gateway).<br></br>
					Enter 'localhost' to use '127.0.0.1' (i.e. this web server), for example for a technical test.<br></br>
					Leave the following field empty, if the IP address from the current client should be used: <b><?php echo $current_client_IP; ?></b>.<br></br>
					Make sure this IP address  does not change, before the magic packet is send.<br></br>
					</p>
				</label>
			</td>
			<td>
					<input type="text" id="WOL_addr" name="addr" size="15" value="<?php echo $addr; ?>"></input><br></br>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>CIDR</legend>
	<table>
		<tr>
			<td>
				<label for="WOL_cidr">
					<p>
					Enter a number within the range of 0 to 32 in the following field.<br></br>
					Leave the following field empty, if no subnet mask should be used (CIDR = 0).<br></br>
					If the remote host's broadcast address is unknown:<br></br>
					1) enter the host name or IP address of the remote host in the previous field and<br></br>
					2) enter the CIDR subnet mask of the remote host in the following field.<br></br>
					</p>
				</label>
			</td>
			<td>
					<input type="number" id="WOL_cidr" name="cidr" min="0" max="32" value="<?php echo $cidr; ?>"/><br></br>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>Port</legend>
	<table>
		<tr>
			<td>
				<label for="WOL_port">
					<p>
					Enter the port number at which the magic packet should be sent.<br></br>
					Enter a <a href="http://www.iana.org/assignments/port-numbers" target="_blank">port number</a> within the range 0 to 65536.<br></br>
					Note: Port 0 is historically the most common port and 7 or 9 are becoming the most common ports for transferring magic packets.<br></br>
					</p>
				</label>
			</td>
			<td><input type="number" id="WOL_port" name="port" min="0" max="65536" value="<?php echo $port; ?>" /><br></br>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>Store</legend>
	<table>
		<tr>
			<td>
				<label for="Store">
					<p>
					Choose to (not) store this profile within a persistent cookie, which
					<?php
						if (!extension_loaded('mcrypt'))
						{
							echo "will <b>NOT</b> be encrypted (since <a href=\"http://nl.php.net/mcrypt\" target=\"_blank\">mcrypt</a> is unavailable on this web server).";
						}
						else
						{
							echo "<b>WILL</b> be encrypted.";
						}
					?>
					<br></br>
					</p>
				</label>
			</td>
			<td>
				<label>Do NOT store (also deletes an existing cookie): <input type="radio" name="store" value="No"
					<?php
						if ($store == "No")
						{
							echo " checked";
						}
					?>
				></label><br></br>
				This is useful for unsafe and multi-user web browsers (such as in internet cafes).<br></br>
				<label>DO store (overrides an existing cookie): <input type="radio" name="store" value="Yes"
					<?php
						if ($store == "Yes")
						{
							echo " checked";
						}
					?>
				></label><br></br>
				This is useful for safe and single user web browsers.<br></br>
			</td>
		</tr>
	</table>
</fieldset>
<!-- BUTTONS -->
<em unselectable="on">
	<input type="submit" name="submit" value="Send request"></input>
</em>
<!-- BUTTONS -->
</form>
<!-- FORM -->
</body>
</html>