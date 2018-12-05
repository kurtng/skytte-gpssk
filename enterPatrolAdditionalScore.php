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
/*$compDay = new CompetitionDay();
$patrol = new Patrol();*/
$stationId = 0;
$score = new Score();

$msg = "";

// Load up stuff?
if ($_POST["compId"] > 0) {
	$comp->load($_POST["compId"]);
	/*$compDay->load($_POST["compDayId"]);
	$patrol->load($_POST["patrolId"]);
	$stationId = $_POST["stationId"];*/
	$score->hits = $_POST["hits"];
	$score->targets = $_POST["targets"];
	$score->points = $_POST["points"];
	$comp->numberOfExtraShots = $_POST["numberOfExtraShots"];
	$comp->gunClassificationId = $_POST["gunClassId"];
}

switch ($act) {
	case "save":
		if(isPrecision($comp->scoreType)) {
			$ar1 = $_POST["ar1"];
			$ar2 = $_POST["ar2"];
			$ar3 = $_POST["ar3"];
			$ar4 = $_POST["ar4"];
			$ar5 = $_POST["ar5"];
		} else {
			$arhits = $_POST["arhits"];
			$artargets = $_POST["artargets"];
			$arpoints = $_POST["arpoints"];
		}
		
		$list = $comp->genExtraScoreCards();
		foreach ($list as $card) {
			$score->entryId = $card["EntryId"];
			$score->competitionId = $comp->id;
			//$score->patrolId = $patrol->id;
			
			for ($p=1; $p <= $comp->numberOfExtraShots; $p++) {
				// print $arhits[$card["ShotId"]][$p] . "/" .
					//$artargets[$card["ShotId"]][$p] . "/" .
					//$arpoints[$card["ShotId"]][$p] . "<br>";
					
					$score->stationId = -1*$p;
					if(isPrecision($comp->scoreType)) {
						$alltotal = encodePrecision($ar1[$card["EntryId"]][$p], $ar2[$card["EntryId"]][$p], $ar3[$card["EntryId"]][$p], $ar4[$card["EntryId"]][$p], $ar5[$card["EntryId"]][$p]);
						$score->hits = $alltotal;					
						$score->targets = 0;
						$score->points = 0;
					} else {	
						$score->hits = $arhits[$card["EntryId"]][$p];					
						$score->targets = $artargets[$card["EntryId"]][$p];
						$score->points = $arpoints[$card["EntryId"]][$p];
					}
					$score->id = 0;
					
					$ok = $score->saveExtra();
					if ($ok != "OK")
						break;
			}
		}
		// Did we suceed?
		if ($ok == "OK")
		{
			$msg = "Sparat.";
		}
		else {
			$msg = "Kunde ej spara. " . $ok . " " . $msg;
		}
		
		break;
	case "pick":
			$score->entryId = 0;
			$score->competitionId = $comp->id;
			/*$score->compDayId = $compDay->id;
			$score->patrolId = $patrol->id;*/
			$score->stationId = $stationId;
			$score->hits = "";
			$score->targets = "";
			$score->points = "";
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
<title>EnterPatrolScore</title>
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

  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].submit();
  }
  
  function markUp(elem, col)
  {
	var e = document.getElementById(elem);
	e.className = col;
	// alert("Marking " + elem + " as " + col);
  }

  function checkLessThan6(elem)
  {
	var p = document.getElementById("SaveBut");
	var e = document.getElementById(elem);
	var v = document.forms[0].elements[elem].value;
	if (v > 6)
	{
		alert("OBS! Högst 6 träffar");
		document.forms[0].elements[elem].value = "0";
		e.focus();
	}
  }

  function checkLessThanHits(elemTgt, elemHits)
  {
	var e = document.getElementById(elemTgt);
	var v1 = document.forms[0].elements[elemTgt].value;
	var v2 = document.forms[0].elements[elemHits].value;

	if (v1 > 6)
	{
		alert("OBS! Högst 6 figurer");
		e.focus();
	}
	
	if (v1 + 0 > v2 + 0)
	{
		alert("OBS! Du kan inte ha fler figurer än träff");
		document.forms[0].elements[elemTgt].value = "1";
	}
  }

  function addToTotal(elemNow, elem1,elem2,elem3,elem4,elem5,elemtotal) {
	  var v1 = parseInt(document.forms[0].elements[elem1].value);
	  var v2 = parseInt(document.forms[0].elements[elem2].value);
	  var v3 = parseInt(document.forms[0].elements[elem3].value);
	  var v4 = parseInt(document.forms[0].elements[elem4].value);
	  var v5 = parseInt(document.forms[0].elements[elem5].value);

	  if(isNaN(v1) || v1>10) v1=0;
	  if(isNaN(v2) || v2>10) v2=0;
	  if(isNaN(v3) || v3>10) v3=0;
	  if(isNaN(v4) || v4>10) v4=0;
	  if(isNaN(v5) || v5>10) v5=0;
	  
	  var vNow = parseInt(document.forms[0].elements[elemNow].value);
	  if(isNaN(vNow) || vNow>10) {
		  alert("Var god ange siffror. H�gst tio.");
		  document.forms[0].elements[elemNow].value = "";
		  return false;
	  }

	  document.forms[0].elements[elemtotal].value = v1+v2+v3+v4+v5; 
	  
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction">

<center>

	<table border="0" width="90%">
		<tr>
			<td align="right">Tävling:</td>
				<td>
				<?
					$cps = $comp->getList("> 2"); // Get competitions I can enter scores for
					$num = sizeof($cps);
					$selected = "";
					if ($comp->id == 0)
						$selected = " selected";
				?>
					
				<select name="compId" onChange="">
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
			<td align="right">Vapen:</td>
				<td>
				<?
					// Get a list of Gun classes
					$lst = $shot->getGunClassList($comp->id);
					$selected = "";
					if ($comp->gunClassificationId == 0)
						$selected = " selected";
				?>
					
				<select name="gunClassId" onChange="">
				<option value="0"<?=$selected?>>-- Välj vapen --</option>
					<?
					foreach ($lst as $key => $value)
					{
						$selected = "";
						if ($key == $comp->gunClassificationId)
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
			<td align="right">Antal extra skjutningar</td>
			<td>
				<input type="text" id="numberOfExtraShots" name="numberOfExtraShots" value="<?=$comp->numberOfExtraShots?>"/>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><button onclick="javascript:pick();">Show</button> </td>
		</tr>
	</table>

	<br>

	<? if ($comp->id > 0) {
		$list = $comp->genExtraScoreCards();
		if ($debug)
			print_r($list);
	?>

	<table border="0">
		<? // print headers ?>
		<th>Skytt</th>
		<? for ($i=1; $i<=$comp->numberOfExtraShots; $i++) { ?>
			<th><?=$i?></th>
		<? } ?>
	
		<? // Generate a score table for each shot
		
		$col = "grey";
		$cardc = 0;
		
		foreach ($list as $card) {
			$cardc++;
			// Get the scores for this shot
			$iscore = new Score();
			$scores = $iscore->listExtraScores($comp->id, $card["ShotId"], $comp->scoreType, $comp->gunClassificationId, $comp->numberOfExtraShots);
		
			if ($debug)
				var_dump($scores);
	
			// print shot ?>
			
			<?
			if (isPrecision($comp->scoreType)) { //Precision
				$rows = 6; //5 skotts +  total
				$tdHeaderTexts = array(
					1 => $card["GunCard"].". ".$card["ShotName"], 
					2 => $card["ClubName"] . " (" . $card["ShotClassName"] . ")", 
					3 => "Första resultat: " . $card["Total"],4 => "",5 => "",6 => ""
				);
				$tdLastTexts = array(
					1 => "1:a Skott", 
					2 => "2:a Skott", 
					3 => "3:e Skott",
					4 => "4:e Skott",
					5 => "5:e Skott",
					6 => "Total"
				);
				$scoreArrayTags = array(
					1 => 1, 
					2 => 2, 
					3 => 3,
					4 => 4,
					5 => 5,
					6 => "Totals"
				);
				$jsNames = array(
					1 => "ar1", 
					2 => "ar2", 
					3 => "ar3",
					4 => "ar4",
					5 => "ar5",
					6 => "tot"
					
				);
				$sf="javascript:addToTotal(this.name, 'ar1[".$card["EntryId"]."][%1\$d]','ar2[".$card["EntryId"]."][%1\$d]','ar3[".$card["EntryId"]."][%1\$d]','ar4[".$card["EntryId"]."][%1\$d]','ar5[".$card["EntryId"]."][%1\$d]','tot[".$card["EntryId"]."][%1\$d]');";
				$onChangeFormats = array(
					1 => $sf, 
					2 => $sf, 
					3 => $sf,
					4 => $sf,
					5 => $sf,
					6 => ""
				);
			} else {//Fält
			
				$rows = 3;
				$tdHeaderTexts = array(
					1 => $card["GunCard"].". ".$card["ShotName"], 
					2 => $card["ClubName"], 
					3 => ""
				);
				$tdLastTexts = array(
					1 => "Träffar", 
					2 => "Mål", 
					3 => "Poäng"
				);
				$scoreArrayTags = array(
					1 => "Hits", 
					2 => "Targets", 
					3 => "Points"
				);
				$jsNames = array(
					1 => "arhits", 
					2 => "artargets", 
					3 => "arpoints"
				);
				$onChangeFormats = array(
					1 => "javascript:checkLessThan6(this.name);", 
					2 => "javascript:checkLessThanHits(this.name, 'arhits[".$card["EntryId"]."][%d]');", 
					3 => ""
				);
			}
			for ($k=1 ; $k<=$rows; $k++) {?>
				<tr>
					<td><?=$tdHeaderTexts[$k]?></td><?
					for ($i=1; $i<=$comp->numberOfExtraShots; $i++) {
						$ti = ($k-1) + $cardc * ($comp->numberOfExtraShots*$rows) + ($i - 1) * $rows;
						if (sizeof($scores) >= $i)
							$v = $scores[$i-1][$scoreArrayTags[$k]];
						else 
							$v = "";?>
							<td>
								<input value="<?=$v?>" tabindex="<?=$ti?>"
									onChange="<?=sprintf($onChangeFormats[$k],$i)?>" 
									onFocus="javascript:markUp(this.name,'hi<?=$col?>');" 
									onblur="javascript:markUp(this.name,'<?=$col?>');" 
									class="<?=$col?>" maxlength="2" size="2" 
									name="<?=$jsNames[$k]?>[<?=$card["EntryId"]?>][<?=$i?>]">
							</td>
					<?}?>
					<td><?=$tdLastTexts[$k]?></td>
				</tr>
			<?}?>
			<?
			
				// Change colours
				if ($col == "grey")
					$col = "pink";
				else
					$col = "grey";
		
		} // foreach shot ?>
	
	</table>

<br>

<table border="0" width="50%">
	<tr>
		<td>
		<button id="SaveBut" name="SaveBut" tabIndex="100" onClick="javascript:save();">Spara</button>
		</td>
	</tr>
</table>
<? } // if something has been chosen ?>

</center>

</form>
</body>
</html>
