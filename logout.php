<?php
session_start();
include_once "GunnarCore.php";
	
	$act=$_POST['myAction'];
	$msg = "";
	$focusPoint = "";
	
	closeDB();
	unset($_SESSION["shotSession"]);
	session_destroy();
	
	switch ($act) {
		case "login":
			http_redirect("LogIn.php");
			break;
		default:
			break;		
	}
?>
<html>

<head>
<title>Log Out</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function login()
  {
		document.forms[0].elements["myAction"].value = "login";
		document.forms[0].submit();
  }

  function updateMenu()
  {
  		window.top.frames["menuFrame"].location = "Menu.php";
  }
</script>

<body onLoad="javascript:updateMenu();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction">

<center>
	Du &auml;r nu utloggad.<br><br>
	
		<button onClick="javascript:login();">Logga in</button>
<br>

</center>

</form>
</body>
</html>
