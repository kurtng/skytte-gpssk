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

$msg = "";

switch ($act) {
	case "save":
		$newPassword = $_POST["passwd"];
		$confirm = $_POST["passwd2"];
		if ($newPassword != $confirm) {
			$msg = "Bekräftat lösenord ej samma som nytt lösenord.";
		}
		else {
			$ok = $shot->setPassword($newPassword);
			if ($ok)
				$msg = "Ditt lösenord har nu ändrats.";
			else {
				$msg = "Kunde ej spara lösenordet. " . $msg;			
			}
		}
		break;
	default:
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>SetPassword</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">
</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["passwd"].focus();
  }
  
  function save()
  {
		document.forms[0].elements["myAction"].value = "save";
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
		<td align="right">Nytt lösenord:</td>
		<td><input type="password" name="passwd">
		</td>
	</tr>

	<tr>
		<td align="right">Bekräfta lösenord:</td>
		<td><input type="password" name="passwd2">
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
		&nbsp;
		</td>
		<td>
		<button onClick="javascript:save();">Spara</button>
		</td>
	</tr>

</table>

<br>

</center>

</form>
</body>
</html>
