<?php
session_start();
include_once "GunnarCore.php";
include_once 'classes/Traning.php';

	$debug = 0;
	$act=$_POST['myAction'];
	$date=$_POST['date'];
	$result=$_POST['result'];
	$shotClassId = $_POST['shotClassId'];
	$gunClassificationId = $_POST["gunClassId"];
	$shotIdSelected = $_POST["shotIdSelected"];
	$shotIdRegistreraSelected = $_POST["shotIdRegistreraSelected"];
	$msg = "";

	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);

	$traning = new Traning();
	$focusPoint = "name";
	
	

	switch ($act) {
		case "save":
			$traning->shotClassId = $shotClassId;
			$traning->shotId = $shotIdRegistreraSelected;
			$traning->result = $result;
			$traning->date = $date;
			$traning->shotIdGodkan = $shotIdSelected;
			$ok = $traning->save();
			if (!$ok) {
				$msg = "Misslyckades med att spara. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Förening finns redan. Välj ett annat namn.";
			}
			else {
				
			}
			break;
		case "pickGunClass":
			break;
			
		case "pick":
			
			break;
		case "delete":
			$traning->delete($_POST['deleteId']);
			break;
		default:
			// Load competition
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Träning</title>
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

  function deleteIt()
  {
		document.forms[0].elements["myAction"].value = "delete";
		document.forms[0].submit();
  }

  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].submit();
  }

  function pickGunClass()
  {
		document.forms[0].elements["myAction"].value = "pickGunClass";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">

<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction">

<table >

<tr>
	<td align="right">Vapen:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getGunClassList();
			$selected = "";
			if ($gunClassificationId == 0)
				$selected = " selected";
		?>
			
		<select name="gunClassId" onChange="javascript:pickGunClass();">
		<option value="0"<?=$selected?>>-- Välj vapen --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $gunClassificationId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>

<? if ($gunClassificationId > 0) { ?>
<tr>
	<td align="right">Tävlingsklass:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getClassList($gunClassificationId, $comp->masterskap);
			$selected = "";
			if ($shotClassId == 0)
				$selected = " selected";
		?>
			
		<select name="shotClassId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj klass --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $shotClassId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>
<? } // if we have a gun class ?>

<? if ($shotClassId > 0) { ?>
<tr>
	<td align="right">Datum:</td>
	<td><input name="date" value="">(i formatet 2011-12-31)</td>
</tr>
<tr>
	<td align="right">Antal skott:</td>
	<td><input name="result" value=""></td>
</tr>
<tr>
		<td align="right">Godkänd av:</td>
		<td>
		<?
			$eshot = new Shot();
			$cps = $eshot->getGunCardList($shot->clubId);
			$selected = "";
		?>

		<select name="shotIdSelected">
		<option selected value="">-- Välj skytt --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $shotIdSelected)
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
		<td align="right">Registrera denna skytt:</td>
		<td>
		<?
			$eshot = new Shot();
			$cps = $eshot->getGunCardList($shot->clubId);
			$selected = "";
		?>

		<select name="shotIdRegistreraSelected">
		<option selected value="<?=$shot->id?>">-- Registrera mig --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $shotIdRegistreraSelected)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
		
	</tr>
<tr><td colspan="2"><button onClick="javascript:save();">Spara</button></td></tr>
<? } // if we have a shot class ?>

</table>



</form>

<table>
<caption>Alla mina träningstillfällen</caption>
<th>Datum</th>
<th>Klass</th>
<th>Antal skott</th>
<th>Godkänd av</th>
<th></th>
	<?
	$col = "grey";
	$entries = $traning->listEntries($shot->id);
	foreach ($entries as $entry)
			{
					
			?>
				<tr class="<?=$col?>">
					<td><?=$entry["Date"]?></td>
					<td><?=$entry["ShotClassName"]?></td>
					<td><?=$entry["Score"]?></td>
					<td><?=$entry["GShotName"]?></td>
					<td>
						<form method="POST">
							<input type="hidden" name="myAction" value="delete"/>
							<input type="hidden" name="deleteId" value="<?=$entry["Id"]?>"/>
							<input type="submit" value="Ta bort"/>
						</form>
					</td>
				</tr>
				
			<?
			if ($col == "grey")
					$col = "pink";
				else
					$col = "grey";
			}
		 	?>
</table>

</body>
</html>
