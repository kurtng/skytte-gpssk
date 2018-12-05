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

$entry = new Entry();
$comp = new Competition();
$compDay = new CompetitionDay();
$patrol = new Patrol();
$stationId = 0;
$score = new Score();

$msg = "";

// Load up stuff?
if ($_POST["compId"] > 0) {
	$entry->load($_POST["entryId"]);
	$comp->load($_POST["compId"]);
	$compDay->load($_POST["compDayId"]);
	$patrol->load($_POST["patrolId"]);
	$stationId = $_POST["stationId"];
	$score->hits = $_POST["hits"];
	$score->targets = $_POST["targets"];
	$score->points = $_POST["points"];
}


switch ($act) {
	case "save":
		$score->entryId = $entry->id;
		$score->compDayId = $compDay->id;
		$score->patrolId = $patrol->id;
		$score->stationId = $stationId;
		$ok = $score->save();

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
			$score->entryId = $entry->id;
			$score->compDayId = $compDay->id;
			$score->patrolId = $patrol->id;
			$score->stationId = $stationId;
			$score->hits = "";
			$score->targets = "";
			$score->points = "";
			$score->find();
		break;
	default:
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>EnterScore</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["hits"].focus();
  }
  
  function save()
  {
		document.forms[0].elements["myAction"].value = "save";
		document.forms[0].submit();
  }

  function pick(elem)
  {
		document.forms[0].elements["myAction"].value = "pick";
		
		if (elem == "comp") {
			document.forms[0].elements["stationId"].value = "0";
			document.forms[0].elements["compDayId"].value = "0";
			document.forms[0].elements["patrolId"].value = "0";
			document.forms[0].elements["entryId"].value = "0";
		}
		if (elem == "compDay") {
			document.forms[0].elements["stationId"].value = "0";
			document.forms[0].elements["patrolId"].value = "0";
			document.forms[0].elements["entryId"].value = "0";
		}
		if (elem == "patrol") {
			document.forms[0].elements["stationId"].value = "0";
			document.forms[0].elements["entryId"].value = "0";
		}
		
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
			$cps = $comp->getList(3); // Get competitions I can enter scores for
			$num = sizeof($cps);
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
			
		<select name="compId" onChange="javascript:pick('comp');">
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
			
		<select name="compDayId" onChange="javascript:pick('compDay');">
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
<tr>
	<td align="right">Patrull:</td>
		<td>
		<?
			$cps = $patrol->getList($compDay->id);
			$num = sizeof($cps);
			$selected = "";
			if ($patrol->id == 0)
				$selected = " selected";
		?>
			
		<select name="patrolId" onChange="javascript:pick('patrol');">
		<option value="0"<?=$selected?>>-- Välj Patrull --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($patrol->id == 0 && $num==1)
					$patrol->load($key);
					
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

<tr>
	<td align="right">Station:</td>
		<td>
		<select name="stationId" onChange="javascript:pick('station');">
		<option value="0"<?=$selected?>>-- Välj Station --</option>
			<?
			for ($i=1; $i <= $compDay->maxStation; $i++)
			{
				$selected = "";
				if ($i == $stationId)
					$selected = " selected";
			?>
				<option value="<?=$i?>"<?=$selected?>><?=$i?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>
	<tr>
		<td align="right">Skytt:</td>
		<td>
		<?
			$cps = $patrol->listMembers();
			$num = sizeof($cps);
			$selected = "";
		?>

		<select name="entryId" onChange="javascript:pick('entry');">
		<option value="0">-- Välj skytt --</option>
			<?
			foreach ($cps as $row)
			{
				$selected = "";
				$key = $row["EntryId"];
				
				if ($entry->id == 0 && $num==1)
					$entry->load($key);
				
				if ($key == $entry->id)
					$selected = " selected";
					
				$value = $row["FirstName"] . " " . $row["LastName"] . " (" . $row["GunClassName"] . ")";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="3">
			<table border="0">
				<tr>
					<td>Träffar</td>
					<td>Mål</td>
					<td>Poäng</td>
				</tr>
				<tr>
					<td><input size="5" maxlength="5" name="hits" value="<?=$score->hits?>"></td>
					<td><input size="5" maxlength="5" name="targets" value="<?=$score->targets?>"></td>
					<td><input size="5" maxlength="5" name="points" value="<?=$score->points?>"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
	</tr>
</table>

<br>

<table border="0" width="50%">
	<tr>
		<td>
		<button onClick="javascript:save();">Spara</button>
		</td>
	</tr>
</table>
</center>

</form>
</body>
</html>
