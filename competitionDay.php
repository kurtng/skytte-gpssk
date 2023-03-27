<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	// We must have selected a competition first
	if (!session_is_registered("competitionId"))
		http_redirect("competition.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	
	if (($shot->userType != "ADMIN") && ($shot->userType != "OPER")) {
		http_redirect("notAllowed.php");
	}

	$comp = new Competition();
	if (session_is_registered("competitionId"))
		$comp->load($_SESSION["competitionId"]);
	
	$compDay = new CompetitionDay();
	
	$focusPoint = "dayNo";
	
	
	switch ($act) {
		case "save":
			$compDay->id = $_POST['compDayId'];
			$compDay->competitionId = $comp->id;
			$compDay->dayNo = $_POST['dayNo'];
			$compDay->firstStart = $_POST['firstStart'];
			$compDay->lastStart = $_POST['lastStart'];
			$compDay->maxStation = $_POST['maxStation'];
			$compDay->patrolSpace = $_POST['patrolSpace'];
			
			$ok = $compDay->save();
			if (!$ok) {
				$msg = "Misslyckades med att spara. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Tävlingsdagen finns redan.";
			}
			else {
				$_SESSION["competitionDayId"] = $compDay->id;
				$_SESSION["competitionDayNo"] = $compDay->dayNo;
			}
			break;
		case "pick":
			$compDay->load($_POST['compDayId']);
			$_SESSION["competitionDayId"] = $compDay->id;
			$_SESSION["competitionDayNo"] = $compDay->dayNo;
			break;
		default:
			// Try to load in the selected day
			if ($_POST['compDayId'] > 0)
			{
				$compDay->load($_POST['compDayId']);
			}
			else {
				// See if there's a day in the session
				if (session_is_registered("competitionDayId")) {
					$compDay->load($_SESSION['competitionDayId']);
				}
			}
			break;		
	}
	if($compDay->competitionId != $comp->id ) {
		//$msg = "INTE SAMMA TÄVLING DAG TILLHÖR INTE TÄVLING " . $compDay->competitionId . " " . $comp->id;
		unset($_SESSION['competitionDayId']);
		unset($_SESSION['competitionDayNo']);
		$compDay = new CompetitionDay();
	} else {
		//$msg = " " . $compDay->competitionId . " " . $comp->id;
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Competition Day</title>
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
<input type="hidden" name="myAction" value="nop">

<table border="0" width="100%">
<tr>
	<td align="right">Tävling:</td>
	<td><?=$comp->name?></td>
</tr>
<tr>
	<td align="right">Tävlingsdag:</td>
		<td>
		<?
			$cp = new CompetitionDay();
			$cps = $cp->getList($comp->id);
			$selected = "";
			if ($compDay->id == 0)
				$selected = " selected";
		?>
			
		<select name="compDayId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>Ny tävlingsdag</option>
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
	<td align="right">Dag nr:</td>
	<td><input name="dayNo" value="<?=$compDay->dayNo?>"></td>
</tr>
<tr>
	<td align="right">Första start (HH:MM):</td>
	<td><input size="5" maxlength="5" name="firstStart" value="<?=$compDay->firstStart?>"></td>
</tr>
<tr>
	<td align="right">Sista start (HH:MM):</td>
	<td><input size="5" maxlength="5" name="lastStart" value="<?=$compDay->lastStart?>"></td>
</tr>
<tr>
	<td align="right">
		<?
			if (isPrecision($comp->scoreType)) {
		?>
			Antal tavlor (a 5 skott):
		<?	
			}else{
		?>
			Antal stationer:
		<? 
			}
		?>
		</td>
	<td><input size="5" maxlength="5" name="maxStation" value="<?=$compDay->maxStation?>"></td>
</tr>
<tr>
	<td align="right">
		<?
			if (isPrecision($comp->scoreType)) {
		?>
			Intervall mellan starter <br/>(minuter):
		<?	
			}else{
		?>
			Patrull-intervall:
		<? 
			}
		?>
	</td>
	<td><input size="5" maxlength="5" name="patrolSpace" value="<?=$compDay->patrolSpace?>"></td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td><a href="patrol.php">
		<?
			if (isPrecision($comp->scoreType)) {
		?>
			Starter
		<?	
			}else{
		?>
			Patruller
		<? 
			}
		?>
	</a></td>
</tr>


</table>

<br>

<center>
		<button onClick="javascript:save();">Spara</button>
</center>

</form>
</body>
</html>
