<?php
include_once "GunnarCore.php";
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
	
	$focusPoint = "dayNo";
	
	
	switch ($act) {
		case "cancel":
			if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) {
				$cid = $_POST["entryId"];
				$entry->id = $cid;
				$msg = $entry->delete();
				
			}
			break;
		case "edit":
			break;
		case "pick":
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
<title>MyEntries</title>
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
  function cancelBooking(entry)
  {
		document.forms[0].elements["myAction"].value = "cancel";
		document.forms[0].elements["entryId"].value = entry;
		document.forms[0].submit();
  }
  function editBooking(entry)
  {
		document.forms[0].elements["myAction"].value = "edit";
		document.forms[0].elements["entryId"].value = entry;
		document.forms[0].submit();
  }
</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction" value="nop">
<input type="hidden" name="entryId">

<table border="0" width="100%">
<th>Tävling</th>
<th>Skytt</th>
<th>Vapen</th>
<th>Första Start</th>
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
<th>Åtgärd</th>
<? }?>

		<?
			$col = "grey";
			$cp = new Entry();
			$list = $cp->getList(0);
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
				<td><?=$row["Competition"]?></td>
				<td><?=$row["Shot"]?></td>
				<td><?=$row["Gun"]?></td>
				<td><?=$row["FirstStart"]?></td>
			<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
				<td><button onClick="javascript:cancelBooking(<?=$row["Id"]?>);">Avboka</button></td>
			<? } // end if admin/oper ?>
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
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
<table border="0" width="100%">

<tr>
	<td>
		<button onClick="javascript:pick();">Uppdatera</button>
	</td>
</tr>
</table>
<? }?>
</form>
</body>
</html>
