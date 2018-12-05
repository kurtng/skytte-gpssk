<?php
session_start();
include_once "GunnarCore.php";

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";

	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);

	if ($_POST["clubId"] > 0)
	{
		$_SESSION["clubId"] = $_POST["clubId"];
	}
	
	$club = new Club();
	
	$focusPoint = "name";
	
	switch ($act) {
		case "save":
			$club->id = $_POST['clubId'];
			$club->name = $_POST['name'];
			$ok = $club->save();
			if (!$ok) {
				$msg = "Misslyckades med att spara. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Förening finns redan. Välj ett annat namn.";
			}
			else {
				
			}
			break;
		case "pick":
			// Load competition
			$club->load($_POST["clubId"]);
			$_SESSION["clubId"] = $club->id;
			$_SESSION["clubName"] = $comp->name;
			break;
		default:
			// Load competition
			if (session_is_registered("clubId"))
				$club->load($_SESSION["clubId"]);
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Club</title>
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

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction">

<table border="0" width="100%">
<tr>
	<td align="right">Förening:</td>
		<td>
		<?
		
			$cp = new Club();
			$cps = $cp->getClubList();
			$selected = "";
			if ($club->id == 0)
				$selected = " selected";
		?>
			
		<select name="clubId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>Ny Förening</option>
			<?
			
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $club->id)
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
	<td align="right">Förening namn:</td>
	<td><input name="name" value="<?=$club->name?>"></td>
</tr>



</table>

<br>

<center>
		<button onClick="javascript:save();">Spara</button>
</center>
</form>
</body>
</html>
