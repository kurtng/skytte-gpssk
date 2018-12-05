<?php
session_start();
include_once "GunnarCore.php";

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);

	if (($shot->userType != "ADMIN") && ($shot->userType != "OPER")) {
		http_redirect("notAllowed.php");
	}
	
	if ($_POST["compId"] > 0)
	{
		$_SESSION["competitionId"] = $_POST["compId"];
		$_SESSION["compId"] = $_POST["compId"];
	}
	
	$comp = new Competition();
	
	$focusPoint = "name";
	
	switch ($act) {
		case "save":
			$comp->id = $_POST['compId'];
			$comp->name = $_POST['name'];
			$comp->startDate = $_POST['startDate'];
			$comp->location = $_POST['location'];
			$comp->hostClubId = $_POST['hostClubId'];
			$comp->maxPatrolSize = $_POST['maxPatrolSize'];
			$comp->scoreType = $_POST['scoreType'];
			$comp->masterskap = $_POST['masterskap'];
			$comp->onlineBetalning = $_POST['onlineBetalning'];
			$comp->status = $_POST['status'];
			$ok = $comp->save();
			if (!$ok) {
				$msg = "Misslyckades med att spara. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Tävlingen finns redan. Välj ett annat namn eller start-datum.";
			}
			else {
				$_SESSION["competitionId"] = $comp->id;
				$_SESSION["competitionName"] = $comp->name;
				$_SESSION["scoreType"] = $comp->scoreType;
				$_SESSION["masterskap"] = $comp->masterskap;
				$_SESSION["onlineBetalning"] = $comp->onlineBetalning;
			}
			break;
		case "pick":
			// Load competition
			$comp->load($_POST["compId"]);
			$_SESSION["competitionId"] = $comp->id;
			$_SESSION["competitionName"] = $comp->name;
			$_SESSION["scoreType"] = $comp->scoreType;
			$_SESSION["masterskap"] = $comp->masterskap;
			$_SESSION["onlineBetalning"] = $comp->onlineBetalning;
			session_unregister("competitionDayId");
			break;
		default:
			// Load competition
			if (session_is_registered("competitionId"))
				$comp->load($_SESSION["competitionId"]);
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Competition</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function save()
  {
		document.forms[0].elements["myAction"].value = "save";
		document.forms[0].submit();
  }

  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction">

<table border="0" width="100%">
<tr>
	<td align="right">Tävling:</td>
		<td>
		<?
			$cp = new Competition();
			$cps = $cp->getList();
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
			
		<select name="compId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>Ny tävling</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $comp->id)
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
	<td align="right">Tävlingens namn:</td>
	<td><input name="name" value="<?=$comp->name?>"></td>
</tr>
<tr>
	<td align="right">Start datum:</td>
	<td><input name="startDate" value="<?=$comp->startDate?>"></td>
</tr>
<tr>
	<td align="right">Plats:</td>
	<td><input name="location" value="<?=$comp->location?>"></td>
</tr>
<tr>
	<td align="right">Värd-klubb:</td>
		<td>
		<?
			$club = new Club();
			$clubs = $club->getClubList();
		?>
			
		<select name="hostClubId">
			<?
			foreach ($clubs as $key => $value)
			{
				$selected = "";
				if ($key == $comp->hostClubId)
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
	<td align="right">Max antal personer/patrull(fält)<br/> eller start(precision):</td>
	<td><input size="3" maxlength="3" name="maxPatrolSize" value="<?=$comp->maxPatrolSize?>"></td>
</tr>

<tr>
	<td align="right">Poängräkning:</td>
	<td>
		<select name="scoreType">
			<option value="N" <?if ($comp->scoreType == 'N') print(" selected");?>>Poängfält - Fält</option>
			<option value="T" <?if ($comp->scoreType == 'T') print(" selected");?>>Klassisk Räkning - Fält</option>
			<option value="P" <?if ($comp->scoreType == 'P') print(" selected");?>>Precision - Precision</option>
			<option value="S" <?if ($comp->scoreType == 'S') print(" selected");?>>Sportskytte</option>
			<option value="C" <?if ($comp->scoreType == 'C') print(" selected");?>>PPC</option>
		</select>
	</td>
</tr>

<tr>
	<td align="right">Status:</td>
	<td>
		<select name="status">
			<option value="0" <?if ($comp->status == 0) print(" selected");?>>Arbete pågår</option>
			<option value="1" <?if ($comp->status == 1) print(" selected");?>>Anmälan öppen</option>
			<option value="2" <?if ($comp->status == 2) print(" selected");?>>Anmälan stängd</option>
			<option value="3" <?if ($comp->status == 3) print(" selected");?>>Tävling pågår</option>
			<option value="4" <?if ($comp->status == 4) print(" selected");?>>Resultat färdigt</option>
			<option value="5" <?if ($comp->status == 5) print(" selected");?>>Arkiverad</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Mästerskap:</td>
	<td>
		<select name="masterskap">
			<option value="N" <?if ($comp->masterskap == 'N') print(" selected");?>>Nej</option>
			<option value="Y" <?if ($comp->masterskap == 'Y') print(" selected");?>>Ja</option>
			
		</select>
	</td>
</tr>
<tr>
	<td align="right">Online Betalning:</td>
	<td>
		<select name="onlineBetalning">
			<option value="N" <?if ($comp->onlineBetalning == 'N') print(" selected");?>>Nej</option>
			<option value="Y" <?if ($comp->onlineBetalning == 'Y') print(" selected");?>>Ja</option>
			
		</select>
	</td>
</tr>


<tr>
	<td>&nbsp;</td>
	<td><a href="competitionDay.php">Tävlingsdagar</a></td>
</tr>


</table>

<br>

<center>
		<button onClick="javascript:save();">Spara</button>
</center>
</form>
</body>
</html>
