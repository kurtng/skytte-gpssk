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

if (($shot->userType != "ADMIN") && ($shot->userType != "OPER")) {
	http_redirect("notAllowed.php");
}

$comp = new Competition();
$compDay = new CompetitionDay();
$patrol = new Patrol();

$msg = "";

// Load up stuff?
if ($_POST["compId"] > 0) {
	$comp->load($_POST["compId"]);
	$compDay->id = 0;
	if($_POST["compDayId"] > 0)
		$compDay->load($_POST["compDayId"]);
}

switch ($act) {
	case "save":
		$checks = $_POST["checks"];
		$gomds = $_POST["gomds"];
		
		$patrol->deletePatrulGuns($_POST["compDayId"]);
		
		foreach($checks as $patrulId => $grupps) {
			
			$patrol->id = intval($patrulId);
			$patrol->competitionDayId = $_POST["compDayId"];
			$patrol->saveAllowedGuns($grupps);
			
			$patrol->hidden = $gomds[$patrulId] == "on" ? 1 : 0;
			$patrol->saveHidden();
		}
				
		break;
	case "pick":
			/*
			$score->entryId = 0;
			$score->compDayId = $compDay->id;
			$score->patrolId = $patrol->id;
			$score->stationId = $stationId;
			$score->hits = "";
			$score->targets = "";
			$score->points = "";*/
			// $score->find();
		break;
	default:
			// Try to load in the selected competition
			if (session_is_registered("competitionId"))
			{
				$compId = $_SESSION["competitionId"]; 
				$comp->load($compId);
				
				// If the chosen competition is not status=3 -> lose it
				if ($comp->status != 3) {
					$comp->clear();
					$compId = 0;
				}
			}
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>EnterPatrolMatris</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

document.onkeypress = Check_key;

	function Check_key()
	{
		whichKey = event.keyCode;
		if (whichKey == 34) {
			//document.forms[0].elements["myAction"].value = "save";
			//document.forms[0].submit();
			save();
		}
	}

  function setFocus()
  {
  		document.forms[0].elements[4].focus();
  }
  
  function save()
  {
		document.forms[0].elements["myAction"].value = "save";
		document.forms[0].submit();
  }

  function pick(isTavling)
  {
		document.forms[0].elements["myAction"].value = "pick";
		if(isTavling)
			document.forms[0].elements["compDayId"].value = 0;
		
		document.forms[0].submit();
  }
  
  function markUp(elem, col)
  {
	var e = document.getElementById(elem);
	e.className = col;
	// alert("Marking " + elem + " as " + col);
  }


</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
	
<input type="hidden" name="myAction">
<input type="hidden" name="compDayId">

<center>

	<table border="0" width="90%">
		<tr>
			<td align="right">Tävling:</td>
				<td>
				<?
					$cps = $comp->getList(">= 0"); // Get competitions I can enter scores for
					$num = sizeof($cps);
					$selected = "";
					if ($comp->id == 0)
						$selected = " selected";
				?>
					
				<select name="compId" onChange="javascript:pick(true);">
				<option value="0"<?=$selected?>>-- Välj Tävling --</option>
					<?
					foreach ($cps as $key => $value)
					{
						$selected = "";
						if ($comp->id == 0 && $num==1)
							$comp->load($key);
							
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
					$cps = $compDay->getList($comp->id);
					$num = sizeof($cps);
					$selected = "";
					if ($compDay->id == 0)
						$selected = " selected";
				?>
					
				<select name="compDayId" onChange="javascript:pick(false);">
				<option value="0"<?=$selected?>>-- Välj Tävlingsdag --</option>
					<?
					foreach ($cps as $key => $value)
					{
						$selected = "";
						if ($compDay->id == 0 && $num==1)
							$compDay->load($key);
							
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
		
	</table>

	<br>

	<? if ($compDay->id > 0 ) {
		$listGunClasses = $compDay->getGunClasses();
		$listPatrols = $compDay->getPatrols();
		$list = $compDay->genPatrolChoices();
		if ($debug) {
			print_r($list);
			print_r($listGunClasses);
			print_r($listPatrols);
		}
	?>

	<table border="0">
		<? // print headers ?>
		<th><? if (isPrecision($comp->scoreType)) {
				?>
					Start:
				<?	
					}else{
				?>
					Patrull:
				<? 
					}
				?>
		</th>
		<? foreach ($listPatrols as $card) {  ?>
			<th title="<?=$card["StartTime"]?>"><?=$card["SortOrder"]?></th>
		<? } ?>
		
		
		
		<? // Generate a score table for each shot
		
		$col = "grey";
		$cardc = 0;
		$finns = "";
		
		foreach ($listGunClasses as $guncl) {
			?><tr class="<?=$col?>"> <td><?=$guncl["Grade"]?></td><?
			foreach ($listPatrols as $patr) {
				
				$finns = "";
				foreach($list as $item) {
					if(($item["PatrolId"] == $patr["Id"]) && ($item["GunClassId"] == $guncl["Id"])) {
						$finns = "checked";
						break;
					}
				}
				
			?>
				<td><input type="checkbox" name="checks[<?=$patr["Id"]?>][<?=$guncl["Id"]?>]" <?=$finns?>/> </td>
				
			<?} //for each patrull?>
			</tr><?
			
				// Change colours
				if ($col == "grey")
					$col = "pink";
				else
					$col = "grey";
		
		} // foreach gunclass ?>
		<tr>
			<td>Gömd</td>
		<?
		foreach ($listPatrols as $patr) {
			?>
				<td><input type="checkbox" name="gomds[<?=$patr["Id"]?>]" <?=$patr["Hidden"]==1?"checked" : ""?>/> </td>
				
			<?} //for each patrull?>
		
		</tr>
	</table>

<br>

<table border="0" width="50%">
	<tr>
		<td>
		<button id="SaveBut" name="SaveBut" tabIndex="100" onClick="javascript:save();">Spara</button>
		</td>
	</tr>
</table>
Hoovra på Id för att se starttider
<? } // if something has been chosen ?>

</center>

</form>
</body>
</html>
