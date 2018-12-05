<?php
session_start();
include_once "GunnarCore.php";
	
	$act=$_POST['myAction'];
	$msg = "";
	$focusPoint = "";
	
	$shot = new Shot();
	if (session_is_registered("shotSession"))
		$shot = unserialize($_SESSION["shotSession"]);
			
	switch ($act) {
		case "login":
			http_redirect("LogIn.php");
			break;
		case "sendPassword":
			// Generate a new password
			$email = safeSQL($_POST["email"]);
			$gunCard = safeSQL($_POST["gunCard"]);
			$shot->email = safeSQL($email);
			$shot->gunCard = $gunCard;
			$ok = $shot->resetPassword();

			if ($ok == "OK")
				$msg = "Ditt nya lösenord har skickats till: " . $email;
			else
				$msg .= " " . $ok . " Det gick inte att skicka mejl. Försök igen senare.";
			break;
		default:
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Generate Password</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function sendPassword()
  {
		document.forms[0].elements["myAction"].value = "sendPassword";
		document.forms[0].submit();
  }

  function updateMenu()
  {
  		window.top.frames["menuFrame"].location = "Menu.php";
  }

  function login()
  {
		document.forms[0].elements["myAction"].value = "login";
		document.forms[0].submit();
  }
</script>

<body onLoad="javascript:updateMenu();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction">

<center>
	Här kan du begära ett nytt lösenord som skickas till dig<br><br>
		<table border="0">
			<tr>
				<td>Pistolkortsnr:</td>
				<td><input name="gunCard" value="<?=$gunCard?>"></td>
			</tr>
			<tr>
				<td>e-post adress:</td>
				<td><input name="email" value="<?=$email?>"></td>
			</tr>
			<tr>
				<td><button onClick="javascript:sendPassword();">Sänd mig ett nytt lösenord</button></td>
				<td><button onClick="javascript:login();">Till inloggningen</button></td>
			</tr>
		</table>
<br>

</center>

</form>
</body>
</html>
