<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	// We must be logged in
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);
	$comp = new Competition();
	$compDay = new CompetitionDay();
	
	switch ($act) {
		case "pick":
			$compId = $_POST["compId"];
			$gunClassId = $_POST["gunClassId"];
			$comp->load($compId);
			break;
		default:
			break;
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Availability</title>
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

<table border="0" width="100%">
<tr>
	<td align="right">Tävling:</td>
		<td>
		<?
			$cp = new Competition();
			$cps = $cp->getList(" < 4");
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
			
		<select name="compId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj Tävling --</option>
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

<tr>
	<td align="right">Vapenklass:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getGunClassList($comp->id);
			$selected = "";
			if ($shotClassId == 0)
				$selected = " selected";
		?>
			
		<select name="gunClassId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj klass --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $gunClassId)
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

<?
if ($compId > 0 && $gunClassId > 0) {
	// List availability for each competition day
	$cp = new CompetitionDay();
	$cps = $cp->getList($compId);
	$lineno = 0;
	$colno = 0;

	foreach ($cps as $key => $value)
	{
		$compDay->load($key);
		// Get availability
		$ava = $cp->listAvailability($key, $gunClassId);
 ?>

<h3>Dag <?=$compDay->dayNo?></h3>

<table border="0" width="95%">
<tr>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
<td width="10%">&nbsp</td>
</tr>
<tr>
<?
		foreach ($ava as $row) {
			$col = "green";
			$maxSeats = $row["PatrolSize"];
			$seats = $row["Availability"];
			settype($maxSeats, "integer");
			settype($seats, "integer");
			
			$perc = $seats / $maxSeats * 100.0;
			
			if ($perc < 50)
				$col = "yellow";
			if ($perc < 25)
				$col = "orange";
			if ($perc < 10)
				$col = "red";
			
			if ($colno > 10) {
				// Wrap to next line after 10 patrols
				$colno = 1;
				$lineno++;				
?>
				</tr>
				<tr>
<?			}
			else {
				$colno++;
			}
?>
			<td class="<?=$col?>"><?=$row["FirstStart"]?> (<b><?=$seats?></b>)</td>
<?
		} // foreach patrol
?>
</tr>
</table>

<?
	} // foreach comp day
} // if posted
?>
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

