<?php
session_start();
include_once "GunnarCore.php";

$act=$_POST['myAction'];
$shot = new Shot();

if ($act == "login") {
	http_redirect("LogIn.php");
}

$msg = "";

switch ($act) {
	case "save":
		$shot->id = 0;
		$shot->firstName = safeSQL($_POST["firstName"]);
		$shot->lastName = safeSQL($_POST["lastName"]);
		$shot->clubId = safeSQL($_POST["clubId"]);
		$shot->gunCard = safeSQL($_POST["gunCard"]);
		$shot->email = safeSQL($_POST["email"]);
		$shot->password = $_POST["passwd"];
		
		
		// Try to register
		$ok = $shot->save();
		//$msg = $shot->msg;
		

		// Did we suceed?
		if ($ok)
		{
			$_SESSION["shotSession"] = serialize($shot);
			http_redirect("welcome.php");
		}
		else {
			if (eregi("^duplic.*", $msg))
			{
				$msg = "Pistolkortsnummer upptaget. Kontrollera numret. Har du registrerat dig tidigare?";				
			}
			else {
				$msg = "Registrering misslyckades. " . $msg;
			}
		}
		
		break;
	default:
		$uname = $_COOKIE["uname"];
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Register</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["gunCard"].focus();
  }
  
  function save()
  {
		document.forms[0].elements["myAction"].value = "save";
		document.forms[0].submit();
  }

  function login()
  {
		document.forms[0].elements["myAction"].value = "login";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction" value="login">

<center>
<table border="0" width="90%">
	<tr>
		<td align="right">Pistolkortsnummer:</td>
		<td><input name="gunCard" value="<?=$shot->gunCard?>"></td>
	</tr>
	<tr>
		<td align="right">F&ouml;rnamn:</td>
		<td><input name="firstName" value="<?=$shot->firstName?>"></td>
	</tr>
	<tr>
		<td align="right">Efternamn:</td>
		<td><input name="lastName" value="<?=$shot->lastName?>"></td>
	</tr>
	<tr>
		<td align="right">Klubb:</td>
		<td>
		<?
			$club = new Club();
			$clubs = $club->getClubList();
		?>
			
		<select name="clubId">
			<?
			foreach ($clubs as $key => $value)
			{
				$selected = "";
				if ($key == $shot->clubId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
	</tr>
	<tr>
		<td align="right">e-post:</td>
		<td><input name="email" value="<?=$shot->email?>"></td>
	</tr>
	<tr>
		<td align="right">L&ouml;senord:</td>
		<td><input type="password" name="passwd">
		</td>
	</tr>

</table>

<br>

<table border="0" width="70%">
	<tr>
		<td>
		<button onClick="javascript:save();">Registrera</button>
		</td>
		<td>
		<button onClick="javascript:login();">Tillbaka</button>
		</td>
	</tr>
</table>
</center>

</form>
</body>
</html>
