<?php
include "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	if ($act == "edit") {
		$_SESSION["entryId"] = $_POST["entryId"];
		http_redirect("editEntry.php");
	}
	
	// We must be logged in
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);
	$compDayId = $_SESSION["competitionDayId"];
	$entry = new Entry();
	
	$comp = new Competition();
	$compDay = new CompetitionDay();
	$patrol = new Patrol();
	
	$focusPoint = "";
	
	
	switch ($act) {
		case "cancel":
			if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) {
				$cid = $_POST["entryId"];
				$entry->id = $cid;
				$msg = $entry->delete();
				
			}
			break;
		case "pick":
			// Load up the competition
			$compId = $_POST["compId"];
			$comp->load($compId);
			break;
		case "sendMejl":
			// Load up the competition
			$compId = $_POST["compId"];
			$comp->load($compId);
			
			$cp = new Entry();
			$listUnpaid = $cp->listUnpaid($comp->id);
			$selected = "";
			if ($comp->id == 0) {
				$msg = "Välj en tävling först";
			} else {
				$i = 0;
				foreach ($listUnpaid as $row)
				{
					$i ++;
					$shot = new Shot();
					$shot->load($row["ShotId"]);
				
					$ok = $shot->sendMailOmBetalningPaminnelse($shot->email, $comp->name, $row["Gun"], $row["PatrolNumber"], $row["RegisterDate"]);
					if($ok != "OK") {
						$msg = $msg . " Det gick inte att skicka mejl ";
						$i--;
						break;
					} 
				}
				$msg = $i . " mejl skickades.";
			}
			break;
		case "sendMejlInd":
			// Load up the competition
			$compId = $_POST["compId"];
			$shotId = $_POST["shotId"];
			$comp->load($compId);
			
			$cp = new Entry();
			$listUnpaid = $cp->listUnpaid($comp->id);
			$selected = "";
			if ($comp->id == 0) {
				$msg = "Välj en tävling först";
			} else {
				$i = 0;
				foreach ($listUnpaid as $row)
				{
					
					if($row["ShotId"] == $shotId) {
						$i ++;
						$shot = new Shot();
						$shot->load($row["ShotId"]);
					
						$ok = $shot->sendMailOmBetalningPaminnelse($shot->email, $comp->name, $row["Gun"], $row["PatrolNumber"], $row["RegisterDate"]);
						if($ok != "OK") {
							$msg = $msg . " Det gick inte att skicka mejl ";
							$i--;
							break;
						} 
					}
				}
				$msg = $msg . $i . " mejl skickades.";
			}
			break;
		default:
			// Try to load in the selected competition
			if (session_is_registered("competitionId"))
			{
				$compId = $_SESSION["competitionId"]; 
				$comp->load($compId);
				
				// If the chosen competition is not status=1 -> lose it
				if ($comp->status != 1) {
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
<title>UnpaidFees</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].submit();
  }

  function sendMejl()
  {
		document.forms[0].elements["myAction"].value = "sendMejl";
		document.forms[0].submit();
  }
  function sendMejlInd(shotId)
  {
	  document.forms[0].elements["myAction"].value = "sendMejlInd";
	  document.forms[0].elements["shotId"].value = shotId;
	  document.forms[0].submit();
  }
  function cancelBooking(entry)
  {
		document.forms[0].elements["myAction"].value = "cancel";
		document.forms[0].elements["entryId"].value = entry;
		document.forms[0].submit();
  }
 
</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction" value="nop">
<input type="hidden" name="shotId" value="nop">
<input type="hidden" name="entryId">

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
		<option value="0"<?=$selected?>>Välj tävling</option>
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
</table>

<br>

<table border="0" width="100%">
<th>Patrull</th>
<th>Pistolkort</th>
<th>Skytt</th>
<th>Vapen</th>
<th>Anmälningsdatum</th>
<th>Bokad Av</th>

		<?
			$col = "grey";
			$cp = new Entry();
			$list = $cp->listUnpaid($comp->id);
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
		
		<?
			foreach ($list as $row)
			{
				$selected = "";
		?>
			<tr class="<?=$col?>">
				<td><?=$row["PatrolNumber"]?></td>
				<td><?=$row["GunCard"]?></td>
				<td><?=$row["Shot"]?></td>
				<td><?=$row["Gun"]?></td>
				<td><?=$row["RegisterDate"]?></td>
				<td><?=$row["BokadAvShot"]?></td>
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
				<td><button onClick="javascript:cancelBooking(<?=$row["Id"]?>);">Avboka</button></td>
			<? } // end if admin/oper ?>				<td><button onClick="javascript:sendMejlInd('<?=$row["ShotId"]?>');">Skicka påminnelse</button></td>
			</tr>
		<?
			if ($col == "grey")
				$col = "pink";
			else
				$col = "grey";

			}
		?>
</table>

<br>

<table border="0" width="100%">

<tr>
	<td>
		<button onClick="javascript:pick();">Uppdatera</button>
	</td>
	<td>
		<button onClick="javascript:sendMejl();">Skicka påminnelse till alla</button>
	</td>
</tr>
</table>
</form>
</body>
</html>
