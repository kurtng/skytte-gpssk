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
	
	$patrol = new Patrol();
	
	$focusPoint = "dayNo";
	
	
	switch ($act) {
		case "genSchedule":
			$compDay = new CompetitionDay();
			$compDay->load($compDayId);
			if ($compDay->id == 0) {
				$msg = "Kan ej hitta tävlingsdagen. " . $msg;
			}
			else {
				// Generate a schedule
				$ok = $compDay->genSchedule();
				if ($ok != "OK")
					$msg .= ". " . $ok;
			}			
			break;
		default:
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Schedule</title>
<STYLE>
@import url(gunnar1.css);
</STYLE>

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function genSchedule()
  {
		document.forms[0].elements["myAction"].value = "genSchedule";
		document.forms[0].submit();
  }

  function showSchedule()
  {
		document.forms[0].elements["myAction"].value = "showSchedule";
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
	<td width="50%" align="right">Tävling:</td>
	<td><?=$_SESSION["competitionName"]?></td>
</tr>
<tr>
	<td align="right">Tävlingsdag:</td>
	<td><?=$_SESSION["competitionDayNo"]?></td>
</tr>

</table>

<br>
<center>
<table border="0" width="50%">

<tr>
	<td>
		<button onClick="javascript:genSchedule();">Skapa starttider</button>
	</td>
	<td>
		<!-- <button onClick="javascript:showSchedule();">Visa schema</button> -->
	</td>
</tr>
</table>
</center>
</form>

<center>
<? if ($act == "showSchedule") { ?>

<table border="0" cellpadding="2" cellspacing="0" width="90%">
	<th>
		<?if(isPrecision($_SESSION["scoreType"])){?>
			Tavla		
		<?}else{?>
			Station
		<?}?>
	</th>
<th>Start tid</th>
<th>
	<?if(isPrecision($_SESSION["scoreType"])){?>
		Start		
	<?}else{?>
		Patrull
	<?}?>
</th>

<?
	$compDay = new CompetitionDay();
	$compDay->load($compDayId);
	$list = $compDay->listSchedule($_SESSION["scoreType"]);
	$col = "grey";
	
	foreach ($list as $row) { ?>
	<tr class="<?=$col?>">
		<td><?=$row["Station"]?></td>
		<td><?=$row["StartTime"]?></td>
		<td><?=$row["Patrol"]?></td>
	</tr>
<?	
		if ($col == "grey")
			$col = "pink";
		else
			$col = "grey";
	}
  } 
?>
</table>
</center>

</body>
</html>
