<?php
session_start();
include_once "GunnarCore.php";

$act=$_POST['myAction'];
if (session_is_registered("shotSession"))
	$shot = unserialize($_SESSION["shotSession"]);
else
	http_redirect("LogIn.php");

$msg = "";

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>NotAllowed</title>
<STYLE>
@import url(gunnar2.css);
</STYLE>

</head>
<script language="javascript">
	function startMenu()
	{
  		window.top.frames["menuFrame"].location = "Menu.php";
	}
</script>

<body onLoad="javascript:startMenu();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction" value="login">

<center>
<h2>OBEHÖRIG</h2>
<h3>Tyvärr, du har ej tillgång till denna funktion.</h3>

</center>
</form>
</body>
</html>
