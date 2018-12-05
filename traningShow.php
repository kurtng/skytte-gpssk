<?php
session_start();
include_once "GunnarCore.php";
include_once 'classes/Traning.php';

	$debug = 0;
	$act=$_POST['myAction'];
	$selectedDate=$_POST['selectedDate'];
	$selectedKlass=$_POST['selectedKlass'];
	$msg = "";

	if($act == "pick"){
		$selectedKlass = 0;
	}
	
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);

	$traning = new Traning();
	
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Visa träning</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

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

  function pickKlass()
  {
		document.forms[0].elements["myAction"].value = "pickKlass";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<h3>Se alla träningar på GPSSK</h3>
<input type="hidden" name="myAction">

	<select name="selectedDate" onChange="javascript:pick();">
		<option value="0">-- Välj datum --</option>
			<?
			$lst = $traning->getDates();
			foreach ($lst as $ent)
			{
				$selected = "";
				if ($ent["Date"] == $selectedDate)
					$selected = " selected";
			?>
				<option value="<?=$ent["Date"]?>" <?=$selected?>><?=$ent["Date"] . " (" . $ent["Count"] . ")"?></option>
			<?
			}
		 	?>
		</select>

<?if ($selectedDate != "") {?>
	
	<select name="selectedKlass" onChange="javascript:pickKlass();">
		<option value="0">-- Välj klass --</option>
			<?
			$entriesCl = $traning->getClassForDate($selectedDate);
			foreach ($entriesCl as $ent)
			{
				$selected = "";
				if ($ent["ShotClassId"] == $selectedKlass)
					$selected = " selected";
			?>
				<option value="<?=$ent["ShotClassId"]?>"<?=$selected?>><?=$ent["Name"] . " (" . $ent["Count"] . ")"?></option>
			<?
			}
		 	?>
		</select>
	
	<?if ($selectedKlass > 0) {?>
	
		<table>
			<caption>Alla träningar för vald dag</caption>
			<th>Namn</th>
			<th>Klass</th>
			<th>Antal skott</th>
			<th>Godkänd av</th>
			
			<?
			$col = "grey";
			$entries = $traning->getScores($selectedDate, $selectedKlass);
			foreach ($entries as $entry)
					{
							
					?>
						<tr class="<?=$col?>">
							<td><?=$entry["ShotFirstName"] ." " . $entry["ShotLastName"]?></td>
							<td><?=$entry["ShotClassName"]?></td>
							<td><?=$entry["Score"]?></td>
							<td><?=$entry["GShotName"]?></td>
						</tr>
						
					<?
					if ($col == "grey")
							$col = "pink";
						else
							$col = "grey";
					}
				 	?>
		</table>
	<?}?>
<?}?>
	
</form>
</body>
</html>
