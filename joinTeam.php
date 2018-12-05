<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";
	$done = 0;
	$shotId=$_POST['shotId'];

	// We must have selected a shot first
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	$entry = new Entry();
	
	$comp = new Competition();
	// Try to load in the selected competition
	if (session_is_registered("compId")) {
		$comp->load($_SESSION['compId']);
	}

	$focusPoint = "newTeam";
	
	switch ($act) {
		case "GoBook":
			break;
		// This is when we come in from another page
		case "joinTeam":
			$entry->load($_POST["entryId"]);
			$_SESSION["entryId"] = $entry->id;
			break;
		case "clearTeam":
			$entry->load($_SESSION["entryId"]);
			$entry->teamId = 0;
			$entry->save();
			break;
		case "createTeam":
			$nt = $_POST["newTeam"];
			if ($nt == "") {
				$msg = "Det nya laget måste ha ett namn!";
			}
			else {
				$entry->load($_SESSION["entryId"]);
				$entry->startTeam($_POST["newTeam"]);
				$entry->save();
				$msg .= "Startade nytt lag.";
			}
			break;
			// We have decided to join a team
		case "pickTeam":
			$mmsnew = $entry->listTeamMembers($_POST["teamId"]);
			if(sizeof($mmsnew) < 3) {
				$entry->load($_SESSION["entryId"]);
				$entry->teamId = $_POST["teamId"];
				if($shotId == $shot->id) {
					$entry->save();
				} else {
					$entry->otherShotId = $shotId;
					$otherEntryId = $entry->findBySameGunClassificationId($_SESSION["entryId"], $shotId);
					if($otherEntryId == "")
						$msg .= "Denna skytt har inte anmält sig ännu till en patrull med samma vapengrupp";
					else
						$entry->save($otherEntryId);
				}
				
			} else {
				$msg .= "Max 3 medlemmar per lag";
			}
			break;	
		default:
			break;		
	}
	
	if (session_is_registered("entryId")) {
		$entry->load($_SESSION["entryId"]);
		$patrol = new Patrol();
		$patrol->load($entry->patrolId);
		$compDay = new CompetitionDay();
		$compDay->load($patrol->competitionDayId);
		$comp = new Competition();
		$comp->load($compDay->competitionId);
	}
	
	
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>JoinTeam</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function book(pId)
  {
		document.forms[0].elements["myAction"].value = "bookPatrol";
		document.forms[0].elements["patrolId"].value = pId;
		document.forms[0].submit();
  }

  function cancelEntry(eId)
  {
		document.forms[0].elements["myAction"].value = "cancelEntry";
		document.forms[0].elements["entryId"].value = eId;
		document.forms[0].submit();
  }

  function pickTeam(teamId)
  {
	  	document.forms[0].elements["myAction"].value = "pickTeam";
		document.forms[0].elements["teamId"].value = teamId;
		document.forms[0].submit();
  }
  
  function createTeam()
  {
		document.forms[0].elements["myAction"].value = "createTeam";
		document.forms[0].submit();  
  }
  
  function clearTeam()
  {
		document.forms[0].elements["myAction"].value = "clearTeam";
		document.forms[0].submit();
  }
  
</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>
<? if ($done == 0) { ?>

<form method="POST">
<input type="hidden" name="myAction" value="nop">
<input type="hidden" name="teamId" value="">
<input type="hidden" name="patrolId">
<input type="hidden" name="entryId">

<table border="0" width="100%">
<tr>
	<td width="50%">

<table border="0" width="50%">

<tr>
	<td width="25%" align="right">Tävling:</td>
		<td class="Info"><?=$comp->name?></td>
</tr>

<tr>
	<td align="right">Vapen:</td>
	<?
		// Get gun class name
		$gName = $entry->getGunClassName();
	?>
	<td class="Info">
		<?=$gName?>
	</td>
</tr>


<tr>
	<td align="right">Namn på nytt lag:</td>
	<td>
		<input name="newTeam">
		&nbsp;
		<button class="green" onClick="javascript:createTeam();">Skapa lag</button>
	</td>
</tr>

	<tr>
		<td align="right">Anmäl denna skytt:</td>
		<td>
		<?
			$eshot = new Shot();
			$cps = $eshot->getGunCardList($shot->clubId);
			$selected = "";
		?>

		<select name="shotId">
		<option selected value="<?=$shot->id?>">Anmäl mig</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $shotId)
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
</td>
</tr>
</table>

<br/>
<button class="red" onClick="javascript:clearTeam();">Jag vill inte vara med i något lag</button>
<br/>

<div class="shooters" id="shooters">
</div>


<br>

<?
if ($entry->shotClassId > 0) {
	
	$shotGroup = array();
	$list  = $entry->listClubTeams();
	$totalMembers = array();
		
	// Now get the shooters in each team
	foreach ($list as $pt) {
		$col = "Grey"; // First row in list is grey
		$mms = $entry->listTeamMembers($pt["Id"]);
		$totalMembers[ $pt["Id"] ] = sizeof($mms);
		
		$ss = "";
		
		foreach ($mms as $mm) {
			$colClass = "ShooterList" . $col;
			
			$ss .= "<tr class=\"" . $colClass . "\"><td>" . $mm["FirstName"] . " " . $mm["LastName"] . "</td>" .
				"<td>" . $mm["Grade"] . "</td>" . 
				"<td>" . $mm["ShotClassName"] . "</td>" .
			"</tr>";
			
			// Flip row colour
			if ($col == "Grey")
				$col = "Pink";
			else
				$col = "Grey";
		} 
		$shotGroup[$pt["Id"]] = $ss;
	}
	
?>
<div style="overflow: auto;">
<table class="PickPatrol" onmouseout="javascript:showShooters(0);">
<tr>
	<th class="PickPatrol">Lag</th>
	<th class="PickPatrol">Antal deltagare</th>
</tr>
<?
	foreach ($list as $p) {
		$class = "PickPatrol";
		$onClick = "onClick=\"javascript:pickTeam(" . $p["Id"] . ");\"";
		$mems = $totalMembers[ $p["Id"] ];
?>
	<tr class="<?=$class?>" <?=$onClick?> onmouseover="javascript:showShooters(<?=$p["Id"]?>);">
		<td class="PickPatrol"><?=$p["Name"]?></td>
		<td class="PickPatrol"><?=$mems?></td>
	</tr>
<? } ?>
</table>
</div>

<br/>
<i>Anmäl dig genom att klicka på en rad eller starta ett nytt lag<br/></i>
<script language="javascript">

var shootersHdr = "<table width=\"100%\">" +
	"<tr>" +
		"<th class=\"ShooterList\">Skytt</th>" +
		"<th class=\"ShooterList\">Vapen</th>" +
		"<th class=\"ShooterList\">Klass</th>" +
	"</tr>";
	
var shootersFtr = "</table>";
var obj = document.getElementById("shooters");

function showShooters(pid)
{
	switch (pid) {
<? 
		$x = 0;
		foreach ($shotGroup as $key=>$val) { $x=$x+1;?>
		case <?=$key?>:
			obj.innerHTML = shootersHdr + '<?=$val?>' + shootersFtr;
			obj.style.visibility = "visible";
			obj.style.top = 60 + <?=$x*25?>;
			break;
<?		} // foreach patrol ?>
		default: obj.innerHTML = "";
			obj.style.visibility = "hidden";
			break;
	} // End switch
}


</script>

<? } // if we have chosen shot class ?>

</form>
<? } // unless done ?>

</body>
</html>