<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	// We must have selected a competition first
	if (!session_is_registered("competitionDayId"))
		http_redirect("competitionDay.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	$compDayId = $_SESSION["competitionDayId"];
	
	$station = new Station();
	
	$focusPoint = "dayNo";
	
	
	switch ($act) {
		case "save":
			$station->id = $_POST['stationId'];
			$station->competitionDayId = $_SESSION['competitionDayId'];
			$station->sortOrder = $_POST['sortOrder'];
			$station->description = $_POST['description'];
			$station->intervalMinutes = $_POST['intervalMinutes'];
			$ok = $station->save();
			
			if (!$ok) {
				$msg = "Misslyckades med att spara. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Stationen finns redan.";
			}
			else {
				$_SESSION["stationId"] = $station->id;
			}
			break;
		case "pick":
			$station->load($_POST['stationId']);
			$_SESSION["stationId"] = $station->id;
			break;
		default:
			// Try to load in the selected day
			if ($_POST['stationId'] > 0)
			{
				$station->load($_POST['stationId']);
				$_SESSION["stationId"] = $station->id;
			}
			else {
				// See if there's something in the session
				if (session_is_registered("stationId")) {
					$station->load($_SESSION['stationId']);
				}
			}
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Station</title>
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
	<td><?=$_SESSION["competitionName"]?></td>
</tr>
<tr>
	<td align="right">Tävlingsdag:</td>
	<td><?=$_SESSION["competitionDayNo"]?></td>
</tr>
<tr>
	<td align="right">Station:</td>
		<td>
		<?
			$cp = new Station();
			$cps = $cp->getList($compDayId);
			$selected = "";
			if ($station->id == 0)
				$selected = " selected";
		?>
			
		<select name="stationId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>Ny station</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $station->id)
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
	<td align="right">Station nr:</td>
	<td><input size="4" maxlength="4" name="sortOrder" value="<?=$station->sortOrder?>"></td>
</tr>
<tr>
	<td align="right">Patrull-intervall (minuter):</td>
	<td><input name="intervalMinutes" value="<?=$station->intervalMinutes?>"></td>
</tr>

</table>

<br>

<table border="0" width="100%">

<tr>
	<td>
		<button onClick="javascript:save();">Spara</button>
	</td>
</tr>
</table>
</form>
</body>
</html>
