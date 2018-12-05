<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	// We must have logged in first
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	$entry = new Entry();
	
	$comp = new Competition();
	$compDay = new CompetitionDay();
	$patrol = new Patrol();
	
	$focusPoint = "patrolId";
	
	
	switch ($act) {
		case "save":
			if (session_is_registered("entryId")) {
				$entry->load($_SESSION['entryId']);	
				$comp->load($entry->competitionId);
				$compDay->load($_POST['compDayId']);
				$patrol->load($_POST['patrolId']);
				$st = $entry->savePatrol($_POST['patrolId']);
			}
			break;
		case "pick":
			if (session_is_registered("entryId")) {
				$entry->load($_SESSION['entryId']);	
				$comp->load($entry->competitionId);
				$compDay->load($_POST['compDayId']);
				if ($compDay->id > 0)
					$patrol->load($entry->getPatrolId($compDay->id));
			}
			break;
		default:
			if (session_is_registered("entryId")) {
				$entry->load($_SESSION['entryId']);	
				$patrol->load($entry->patrolId);
				$compDay->load($patrol->competitionDayId);
				$comp->load($compDay->competitionId);
			}
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>EditEntry</title>
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
			$cps = $compDay->getList($comp->id);
			$num = sizeof($cps);
			$selected = "";
			if ($compDay->id == 0)
				$selected = " selected";
		?>
			
		<select name="compDayId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj tävlingsdag --</option>
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

<tr>
	<td align="right">Välj patrull:</td>
		<td>
		<?
			$cps = $compDay->listAvailablePatrols($entry->id);
			$selected = "";
			if ($patrol->id == 0)
				$selected = " selected";
		?>

		<select name="patrolId" onChange="javascript:save();">
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $patrol->id)
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

<? if ($patrol->id > 0) { ?>
<table border="0" width="100%">
<th>Skytt</th>
<th>Vapen</th>
<th>Klass</th>

		<?
			$col = "grey";
			$list = $patrol->listMembers();
			
			foreach ($list as $row)
			{
		?>
			<tr class="<?=$col?>">
				<td><?=$row["FirstName"]?>&nbsp;<?=$row["LastName"]?></td>
				<td><?=$row["GunClass"]?> (<?=$row["GunClassName"]?>)</td>
				<td><?=$row["ShotClass"]?></td>
			</tr>
		<?
			if ($col == "grey")
				$col = "pink";
			else
				$col = "grey";

			}
		?>

</table>
<? } // Patrol ID check ?>

</form>
</body>
</html>
