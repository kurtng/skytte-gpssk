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

if ($shot->userType != "ADMIN") {
	http_redirect("notAllowed.php");
}

$eshot = new Shot();

$msg = "";

switch ($act) {
	case "save":
		$eshot->id = $_POST["shotId"];
		$eshot->firstName = $_POST["firstName"];
		$eshot->lastName = $_POST["lastName"];
		$eshot->clubId = $_POST["clubId"];
		$eshot->userType = $_POST["userType"];
		$eshot->gunCard = $_POST["gunCard"];
		$eshot->email = $_POST["email"];
		$eshot->password = $_POST["passwd"];
		
		// Try to register
		$ok = $eshot->save();
		//$msg = $shot->msg;
		

		// Did we suceed?
		if ($ok)
		{
			$msg = "Sparad.";
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
		<td align="right">Användare:</td>
		<td>
		<?
			$cps = $eshot->getGunCardList();
			$selected = "";
		?>

		<select name="shotId" onChange="javascript:getGunCard();">
		<option value="0">-- Välj pistolkortsnummer --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $eshot->id)
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
		<td align="right">Behörighet:</td>
			<td>
			<select name="userType">
				<option value="USER" <? if ($eshot->userType == "USER") print " selected";?>>Användare</option>
				<option value="OPER" <? if ($eshot->userType == "OPER") print " selected";?>>Funktionär</option>
				<option value="ADMIN" <? if ($eshot->userType == "ADMIN") print " selected";?>>Administratör</option>
			</select>
			</td>
	</tr>
	<tr>
		<td align="right">e-post:</td>
		<td><input name="email" value="<?=$eshot->email?>"></td>
	</tr>
	<tr>
		<td align="right">L&ouml;senord:</td>
		<td><input type="password" name="passwd">
		</td>
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
