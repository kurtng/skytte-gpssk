<?php
session_start();
include_once "GunnarCore.php";


isset( $_POST['myAction'] ) ? $act = $_POST['myAction'] : $act = "";
isset($_POST['gunCard']) ? $gunCard = safeSQL($_POST['gunCard']) : $gunCard = "";
isset($_POST['upasswd']) ? $pwd = $_POST['upasswd'] : $pwd = "";
$msg = "";
$loginFailed = 0;

$focusPoint = "gunCard";
isset($_SESSION["shotSession"]) ? $shot = unserialize($_SESSION["shotSession"]) : $shot = null;

if (session_is_registered("shotSession"))
	http_redirect("welcome.php");

switch ($act) {
	case "login":
		// Store cookies first
		setcookie("gunCard", $_POST['gunCard'], time() + 60*60*24*30);
		$ok = login($gunCard, $pwd);
		if ($ok != 0)
			http_redirect("welcome.php?login=true");
		else {
			$msg = "Fel pistolkortsnummer eller lösenord. Försök igen.";
			$loginFailed = 1;
		}
		$focusPoint = "upasswd";
		break;
	case "registerUser":
		http_redirect("register.php");
		break;
	case "reminder":
		http_redirect("reminder.php");
		break;
	default:
		$gunCard = $_COOKIE["gunCard"];
		break;
}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Log In</title>
<STYLE>
@import url(gunnar2.css);
</STYLE>

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function registerUser()
  {
		document.forms[0].elements["myAction"].value = "registerUser";
		document.forms[0].submit();
  }

  function login()
  {
		document.forms[0].elements["myAction"].value = "login";
		document.forms[0].submit();
  }

  function reminder()
  {
		document.forms[0].elements["myAction"].value = "reminder";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction">

<center>

<table border="0" width="100%">
	<tr>
		<td align="right">Pistolkortsnummer:</td>
		<td><input name="gunCard" value="<?=$gunCard?>"></td>
	</tr>
	<tr>
		<td align="right">Lösenord:</td>
		<td><input type="password" name="upasswd">
		&nbsp;<button onClick="javascript:login();">Logga in</button>
		</td>
	</tr>

</table>

<br/>
<!-- 
<div style="color:red;font-size:20px;">!!! ALLIANSEN 1 för 2016 är flyttad till 19:e Mars !!!</div>
<div style="color:red;font-size:20px;">Alla bokningar flyttas automatisk. Om ni vill byta starttid avboka först era starter och sedan boka om utan att betala på nätet och ta kontakt med sekreteriatet på tävlingsdagen för att flytta betalningarna.</div>
 -->

 <!-- div style="color:red;font-size:20px;">!! Tyvärr fungerar inte online betalning för nuvarande. Swish eller kontant betalning gäller tills vidare på tävlingsdagen. !!</div -->

<div>Du kommer att betala online i samband med bokning<img alt="visa" src="RTEmagicC_RTEmagicC_master_mellem.gif.gif"> <img alt="mastercard" src="RTEmagicC_visa_mellem.gif.gif"> </div>
<br/>
<div><button onclick="alert('Organisationsnummer\n	857209-3394 \n	Kontaktpersons namn, email och telefonnummer \n	Christer Beckman. christer@gpssk.com, 0705-98 88 47 \n 	E-postadress för fakturering \nchrister@gpssk.com \n	Webbadress (URL) till där försäljning äger rum \n	www.okrets.se/skytte');">Föreningsinformation</button></div>
<div><button onclick="alert('1. Kostnad för en start är 100 kr.\n2. Ingen bytesrätt eller ångerrätt \n3. Den betalda starten måste utnyttjas personligen. \n4. Om ni inte betalar online men ändå lyckas boka en start via webbsidan, garanteras ni ändå inte platsen innan ni betalar för den.\n5.Det kommer att bli möjligt att betala och boka på plats i mån av plats. Gäller dock bara funktionärer och ni utan tillgång till kort. Det kan tillkomma en extra avgift.');">Villkor för online köp</button></div>
<br/><br/>

<table border="0" width="100%">

	<tr>
		<td align="right">Inte varit här tidigare?</td>
		<td><button onClick="javascript:registerUser();">Registrera Dig</button></td>
	</tr>
	<tr>
		<td align="right">Kommer inte ihag lösenordet?</td>
		<td>
			<button onClick="javascript:reminder();">Jag har glömt mitt lösenord</button>
		</td>
	</tr>
	<tr>
		<td align="right">Har annat problem?</td>
		<td><a href="mailto:webmaster@gpssk.se?subject=Fråga ang Alliansen">webmaster@GPSSK.se</a></td>
	</tr>
</table>
</center>
</form>
</body>
</html>
