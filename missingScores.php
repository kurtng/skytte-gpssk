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

	// We must have chosen a competition
	if (!session_is_registered("competitionId"))
		http_redirect("competition.php");

	$shot = unserialize($_SESSION["shotSession"]);
	$compId = $_SESSION["competitionId"];
	$comp = new Competition();
	$comp->load($compId);
	
	
	switch ($act) {
		case "pick":
			break;
		default:
			break;
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>MissingScores</title>
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
</script>

<body>
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction" value="nop">
<input type="hidden" name="entryId">

<table border="0" width="100%">
<th>Tävling</th>
<th>Dag</th>
<th>Patrull</th>
<th>Station</th>
<th>Skytt</th>

		<?
			$col = "grey";
			$cp = new Score();
			$list = $cp->listMissingScores($compId);
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
				<td><a href="javascript:enterScore(<?=$compid?>,<?=$row["CompDayId"]?>)"><?=$comp->name?></a></td>
				<td><?=$row["DayNo"]?></td>
				<td><?=$row["PatrolNo"]?></td>
				<td><?=$row["StationId"]?></td>
				<td><?=$row["ShotName"]?></td>
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
</tr>
</table>
</form>
</body>
</html>
