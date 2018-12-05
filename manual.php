<?php
session_start();
include_once "GunnarCore.php";

$act=$_POST['myAction'];
if (session_is_registered("shotSession"))
	$shot = unserialize($_SESSION["shotSession"]);
else
	http_redirect("LogIn.php");

if ($shot->userType != "ADMIN" && $shot->userType != "OPER") {
	http_redirect("notAllowed.php");
}

$msg = "";

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Manual</title>
<STYLE>
@import url(gunnar2.css);
</STYLE>

</head>
<script language="javascript">
	function startMenu()
	{
  		//window.top.frames["menuFrame"].location = "Menu.php";
	}
</script>

<body onLoad="javascript:startMenu();">
<div class="error"><?=$msg?></div>

<form method="POST"><input type="hidden" name="myAction" value="login">

<center>
<h2>Instruktioner</h2>
</center>
<h3>Tågordning</h3>
<ol>
	<li><a href="competition.php">Skapa en ny tävling</a></li>
	<li><a href="competitionDay.php">Lägg till en tävlingsdag</a></li>
	<li><a href="patrol.php">Skapa patruller för den nya tävlingsdagen</a></li>
	<li><a href="schedule.php">Skapa schema för den nya tävlingsdagen</a></li>
	<li>Gå till (2.) ovan om tävlingen har fler tävlingsdagar</li>
	<li><a href="competition.php">Ändra nu status på tävlingen till "Anmälan Öppen"</a></li>
	<li>Vänta tills anmälningstiden gått ut (nu anmäler sig folk)</li>
	<li><a href="competition.php">Ändra status på tävlingen till "Anmälan stängd"</a></li>
	<li>Vänta tills tävlingsdagen</li>
	<li><a href="entryFee.php">Registrera betalningar</a></li>
	<li><a href="patrolPDF.php">Skriv ut score cards patrullerna kan ta med sig</a></li>
	<li><a href="competition.php">Ändra status på tävlingen till "Tävling pågår"</a></li>
	<li><a href="enterPatrolScore.php">Registrera resultat</a></li>
	<li><a href="competition.php">Ändra status på tävlingen till "Resultat färdigt"</a></li>
	<li><a href="showResult.php">Visa resultatlista</a></li>
</ol>

</form>
</body>
</html>
