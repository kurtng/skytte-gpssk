<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	// We must have selected a competition first
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	$compDayId = $_SESSION["competitionDayId"];
	$entry = new Entry();
	
	$comp = new Competition();
	$compDay = new CompetitionDay();
	$patrol = new Patrol();
	
	$focusPoint = "dayNo";
	
	
	switch ($act) {
		case "save":
			$entry->gunClassificationId = $_POST['gunClassId'];
			$entry->shotClassId = $_POST['shotClassId'];
			$entry->patrolId = $_POST['patrolId'];
			$entry->shotId = $shot->id;
			
			$ok = $entry->save();
			
			if (!$ok) {
				$msg = "Misslyckades med anmälan. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Du är redan anmäld.";
			}
			else {
				$msg = "Anmälan mottagen.";
			}

			$patrol->load($_POST['patrolId']);
			$_SESSION["patrolId"] = $patrol->id;
			$compDay->load($_POST['compDayId']);
			$_SESSION["compDayId"] = $compDay->id;
			$comp->load($_POST['compId']);
			$_SESSION["compId"] = $comp->id;
			$entry->gunClassificationId = $_POST["gunClassId"];
			$entry->shotClassId = $_POST['shotClassId'];
			
			break;
		case "pick":
			$patrol->load($_POST['patrolId']);
			$_SESSION["patrolId"] = $patrol->id;
			$compDay->load($_POST['compDayId']);
			$_SESSION["compDayId"] = $compDay->id;
			$comp->load($_POST['compId']);
			$_SESSION["compId"] = $comp->id;
			$entry->gunClassificationId = $_POST["gunClassId"];
			$entry->shotClassId = $_POST['shotClassId'];
			break;
		default:
			// Try to load in the selected patrol
			if ($_POST['patrolId'] > 0)
			{
				$patrol->load($_POST['patrolId']);
				$_SESSION["patrolId"] = $patrol->id;
			}
			else {
				// See if there's something in the session
				if (session_is_registered("patrolId")) {
					$patrol->load($_SESSION['patrolId']);
				}
			}

			// Try to load in the selected comp day
			if ($_POST['compDayId'] > 0)
			{
				$compDay->load($_POST['compDayId']);
				$_SESSION["compDayId"] = $compDay->id;
			}
			else {
				// See if there's something in the session
				if (session_is_registered("compDayId")) {
					$compDay->load($_SESSION['compdayId']);
				}
			}

			// Try to load in the selected competition
			if ($_POST['compId'] > 0)
			{
				$comp->load($_POST['compId']);
				$_SESSION["compId"] = $comp->id;
			}
			else {
				// See if there's something in the session
				if (session_is_registered("compId")) {
					$comp->load($_SESSION['compId']);
				}
			}
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>EditCompetition</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />

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
<input type="hidden" name="myAction" value="nop">

<table border="0" width="100%">
<tr>
	<td align="right">Tävling:</td>
		<td>
		<?
			$cp = new Competition();
			$cps = $cp->listCompetitionsICanEnter($shot->id);
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
			
		<select name="compId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj tävling --</option>
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
	<td align="right">Tävlingsdag:</td>
		<td>
		<?
			$cp = new CompetitionDay();
			$cps = $cp->listDaysToBook($comp->id, $shot->id);
			$selected = "";
			if ($compDay->id == 0)
				$selected = " selected";
		?>
			
		<select name="compDayId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj tävlingsdag --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $compDay->id)
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
	<td align="right">Vapen:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getGunClassList($comp->id);
			$selected = "";
			if ($entry->gunClassificationId == 0)
				$selected = " selected";
		?>
			
		<select name="gunClassId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj vapen --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $entry->gunClassificationId)
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
	<td align="right">Tävlingsklass:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getClassList();
			$selected = "";
			if ($entry->shotClassId == 0)
				$selected = " selected";
		?>
			
		<select name="shotClassId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj klass --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $entry->shotClassId)
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
	<td align="right">Patrull:</td>
		<td>
		<?
			$cp = new Patrol();
			$cps = $compDay->listAvailablePatrols($entry->gunClassificationId);
			$selected = "";
			if ($patrol->id == 0)
				$selected = " selected";
		?>
			
		<select name="patrolId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj patrull --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $patrol->id)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>

</table>

<br>

<table border="0" width="100%">

<tr>
	<td>
		<button onClick="javascript:save();">Anmäl mig!</button>
	</td>
</tr>
</table>
</form>
</body>
</html>