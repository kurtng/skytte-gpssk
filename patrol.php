<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	isset( $_POST['myAction'] ) ? $act = $_POST['myAction'] : $act = "";
	$msg = "";

	// We must have selected a competition day first
	if (!session_is_registered("competitionDayId"))
		http_redirect("competitionDay.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	$compDayId = $_SESSION["competitionDayId"];
	
	$compId = $_SESSION["competitionId"];
	$comp = new Competition();
	$comp->load($compId); // Reload to get the guns list

	$patrol = new Patrol();
	
	$focusPoint = "patrolId";
	
	
	switch ($act) {
		case "save":
			$patrol->id = $_POST['patrolId'];
			$patrol->competitionDayId = $_SESSION['competitionDayId'];
			$patrol->sortOrder = $_POST['sortOrder'];
			$patrol->description = $_POST['description'];
			$multi = $_POST["maxMulti"];
			$gc = $_POST["gc"];
			
			if (!is_numeric($multi))
				$multi = 1;
			if ($multi < 1)
				$multi = 1;

			// Generate the necessary patrols
			for ($it = 0; $it < $multi; $it++) {
				if ($multi > 1) {
					$patrol->sortOrder = 0;
					$patrol->id = 0;
				}
				$patrol->startTime = $comp->startDate;
				
				$ok = $patrol->save();
				
				if (!$ok) {
					$msg = "Misslyckades med att spara. " . $msg;
					if(preg_match('/duplic.*/i',$msg))
					//if (eregi("duplic.*", $msg))
						$msg = "Patrullen finns redan.";
					break;
				}
				else {
					$_SESSION["patrolId"] = $patrol->id;
					// Now save away the guns allowed for this patrol
					$patrol->saveAllowedGuns($gc);
				}
			}
						
			if ($debug)
				print_r("<br>$gc");
				
			$patrol->load($patrol->id); // Reload to get the guns list
			
			break;
		case "pick":
			$patrol->load($_POST['patrolId']);
			$_SESSION["patrolId"] = $patrol->id;
			break;
		case "remove":
			$patrol->load($_POST['patrolId']);
			$ok = $patrol->delete();
			if (!$ok) {
				$msg = "Misslyckades med borttagning. " . $msg;
				break;
			}
			else {
				$_SESSION["patrolId"] = 0;
			}
						
			break;
		default:
			// Try to load in the selected day
			if (isset( $_POST['patrolId'] )  && $_POST['patrolId'] > 0)
			{
				$patrol->load($_POST['patrolId']);
				$_SESSION["patrolId"] = $patrol->id;
			}
			break;		
	}
	
	$tx = "Patrull";
	if(isPrecision($_SESSION["scoreType"])){
			$tx = "Start";
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Patrol</title>
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

  function remove()
  {
		document.forms[0].elements["myAction"].value = "remove";
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
	<td><?=$_SESSION["competitionName"]?></td>
</tr>
<tr>
	<td align="right">Tävlings datum:</td>
	<td><?=$comp->startDate?></td>
</tr>
<tr>
	<td align="right">Tävlingsdag:</td>
	<td><?=$_SESSION["competitionDayNo"]?></td>
</tr>
<tr>
	<td align="right"><?=$tx?>:</td>
		<td>
		<?
			$cp = new Patrol();
			$cps = $cp->getList($compDayId);
			$selected = "";
			if ($patrol->id == 0)
				$selected = " selected";
		?>
			
		<select name="patrolId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>Ny <?=$tx?></option>
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
<tr>
	<td align="right"><?=$tx?> nr:</td>
	<td><input size="4" maxlength="4" name="sortOrder" value="<?=$patrol->sortOrder?>">
	Generera&nbsp;<input name="maxMulti" size="3" maxlength="3">
	<?=$tx?>er av denna typ.</td>
</tr>
<tr>
	<td align="right">Kommentar:</td>
	<td><input name="description" value="<?=$patrol->description?>"></td>
</tr>

<tr>
	<td align="right">Starttid:</td>
	<td><input disabled name="starttime" value="<?=$patrol->startTime?>"></td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td><i><u>Tillåtna vapen i denna <?=$tx?></u></i></td>
</tr>

<?
	// Get a list of Gun classes
	$lst = $shot->getGunClassList(0, $compDayId);
	
			foreach ($lst as $key => $value)
			{
				$checked = "";
				if ($patrol->gunList[$key]!="") {
					$checked = "checked";
				}
?>
				<tr><td>&nbsp;</td><td><input type="checkbox" <?=$checked?> name="gc[<?=$key?>]"><?=$value?></td></tr>
<?
			}
?>
	
<tr>
	<td>&nbsp;</td>
	<td><a href="schedule.php">Skapa Starttider</a></td>
</tr>


</table>

<br>

<center>
<table border="0" width="30%">
<tr>
	<td style="text-align: left;">
		<button class="green" onClick="javascript:save();">Spara</button>
	</td>
	<? if ($patrol->id > 0) { ?>
	<td style="text-align: right;">
		<button class="red" onClick="javascript:remove();">Ta bort</button>
	</td>
	<? } // if patrol selected ?>
</tr>
</table>
</center>

</form>
</body>
</html>
