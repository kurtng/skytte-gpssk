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
	case "activate":
		$shot->load($_POST["shotId"]);
		if ($shot->id == $_POST["shotId"]) {
			$_SESSION["shotSession"] = serialize($shot);
			$eshot->load($shot->id);
			$msg = "Du har nu bytt identitet";
		}
		else {
			$msg = "Misslyckades med att byta identitet";
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
<title>Proxy</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["gunCard"].focus();
  		<? if ($act == "activate") { ?>
  		updateMenu();
  		<? } // end if ?>
  }
  
  function activate()
  {
		document.forms[0].elements["myAction"].value = "activate";
		document.forms[0].submit();
  }

  function getGunCard()
  {
		document.forms[0].elements["myAction"].value = "getGunCard";
		document.forms[0].submit();
  }

  function updateMenu()
  {
  		window.top.frames["menuFrame"].location = "Menu.php";
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

</table>

<br>

<? if ($eshot->id > 0) { ?>
<table border="0" width="50%">
	<tr>
		<td>
		<button onClick="javascript:activate();">L&aring;tsas vara denna anv&auml;ndare</button>
		</td>
	</tr>
</table>
<? } // end if ?>

</center>

</form>
</body>
</html>
