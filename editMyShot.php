<?php
session_start();
include_once "GunnarCore.php";

$debug = 0;

$act=$_POST['myAction'];
// We must have logged in first
if (!session_is_registered("shotSession"))
	http_redirect("LogIn.php");
	
$shot = new Shot();
$shot = unserialize($_SESSION["shotSession"]);

$eshot = new Shot();

$msg = "";

switch ($act) {
	case "save":
		$eshot->id = $shot->id;
		$eshot->firstName = safeSQL($_POST["firstName"]);
		$eshot->lastName = safeSQL($_POST["lastName"]);
		$eshot->clubId = safeSQL($_POST["clubId"]);
		$eshot->userType = safeSQL($shot->userType);
		$eshot->gunCard = safeSQL($_POST["gunCard"]);
		$eshot->email = safeSQL($_POST["email"]);
		$eshot->password = ""; // We don't change password here
		
		// Try to register
		$ok = $eshot->save();
		//$msg = $shot->msg;
		

		// Did we suceed?
		if ($ok)
		{
			$msg = "Din profil har sparats.";
		}
		else {
			if (eregi("^duplic.*", $msg))
			{
				$msg = "Pistolkortsnummer upptaget. Kontrollera numret. " . $msg;
			}
		}
		
		break;
	case "getGunCard":
		$id = $_POST["shotId"];
		$msg = $eshot->load($id);
		break;
	default:
		$eshot->load($shot->id);
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>MyProfile</title>
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

  function getGunCard()
  {
		document.forms[0].elements["myAction"].value = "getGunCard";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction">

<center>

<table border="0" width="90%">
	<tr>
		<td align="right">Pistolkortsnummer:</td>
		<td><input name="gunCard" value="<?=$eshot->gunCard?>">
		</td>
		
	</tr>
	<tr>
		<td align="right">F&ouml;rnamn:</td>
		<td><input name="firstName" value="<?=$eshot->firstName?>"></td>
	</tr>
	<tr>
		<td align="right">Efternamn:</td>
		<td><input name="lastName" value="<?=$eshot->lastName?>"></td>
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
				if ($key == $eshot->clubId)
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
		<td><input name="email" value="<?=$eshot->email?>"></td>
	</tr>

</table>

<br>

<table border="0" width="50%">
	<tr>
		<td>
		<button onClick="javascript:save();">Spara</button>
		</td>
	</tr>
</table>
</center>

</form>
</body>
</html>
