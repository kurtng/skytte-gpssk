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

//if (($shot->userType != "ADMIN") && ($shot->userType != "OPER")) {
//	http_redirect("notAllowed.php");
//}

$comp = new Competition();
$compDay = new CompetitionDay();
$patrol = new Patrol();

$msg = "";

// Load up stuff?
if ($_POST["compId"] > 0) {
	
	$comp->load($_POST["compId"]);
	$_SESSION["competitionId"] = $comp->id;
	$compDay->id = 0;
	if($_POST["compDayId"] > 0) {
		$compDay->load($_POST["compDayId"]);
		$_SESSION["competitionDayId"] = $compDay->id;
	}
}

switch ($act) {
	
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
				$_SESSION["competitionId"] = $comp->id;
				
			}
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Välj tävling</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">


  function setFocus()
  {
  		//document.forms[0].elements[4].focus();
  }
  
  

  function show() {
	  //alert(top.frames["mainFrame"].location.href);
	  //top.frames["mainFrame"].location.replace("http://www.google.com");
	  //top.frames["mainFrame"].location.href = "http://www.google.com";// "resultTeamReport.php"; 
	  document.forms[0].action="resultReport.php";
	  document.forms[0].submit();
  }

  function pick(isTavling)
  {
		document.forms[0].elements["myAction"].value = "pick";
		if(isTavling)
			document.forms[0].elements["compDayId"].value = 0;
		document.forms[0].action="resultReportSelect.php";
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
			<td align="right">Tävling:</td>
				<td>
				<?
					$cps = $comp->getList("between 2 and 4"); // Get competitions I can enter scores for
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
						if ($comp->id == 0 && $num==1) {
							$comp->load($key);
							$_SESSION["competitionId"] = $comp->id;
						}
							
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
						if ($compDay->id == 0 && $num==1) {
							$compDay->load($key);
							$_SESSION["competitionDayId"] = $compDay->id;
						}
							
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
		
	?>

	<table border="0" width="50%">
	<tr>
		<td>
		<button id="ShowBut" name="ShowBut" tabIndex="100" onClick="javascript:show();">Visa individuell resultat i detalj</button>
		</td>
	</tr>
</table>
<? } // if something has been chosen ?>

</center>

</form>
</body>
</html>
