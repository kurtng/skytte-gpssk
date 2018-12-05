<?php
session_start();
include_once "GunnarCore.php";
include_once 'classes/Traning.php';

	$debug = 0;
	$act=$_POST['myAction'];
	$shotIdSelected=$_POST['shotIdSelected'];
	$msg = "";

	
	
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
<input type="hidden" name="myAction">
<h3>Se alla träningar medlemmen har godkänt</h3>

		<?
			$eshot = new Shot();
			$cps = $eshot->getGunCardList($shot->clubId);
			$selected = "";
		?>

		<select name="shotIdSelected" onChange="javascript:pick();">
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
		
	

<?if ($shotIdSelected != "") {?>
	
		<table>
			<caption>Alla träningar som skytt har godkänt</caption>
			<th>Namn</th>
			<th>Klass</th>
			<th>Antal skott</th>
			<th>Godkänd av</th>
			<th>Datum</th>
			<?
			$col = "grey";
			$entries = $traning->getScoresReg($shotIdSelected);
			foreach ($entries as $entry)
					{
							
					?>
						<tr class="<?=$col?>">
							<td><?=$entry["ShotFirstName"] ." " . $entry["ShotLastName"]?></td>
							<td><?=$entry["ShotClassName"]?></td>
							<td><?=$entry["Score"]?></td>
							<td><?=$entry["GShotName"]?></td>
							<td><?=$entry["Date"]?></td>
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

	
</form>
</body>
</html>
