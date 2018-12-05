<?php
session_start();
include_once "GunnarCore.php";

if (session_is_registered("shotSession"))
	$shot=unserialize($_SESSION["shotSession"]);
else
	unset($shot);

header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
<STYLE>
@import url(gunnar2.css);
</STYLE>
<script>
var lastVisitedElement;
function onAClick(element) {
	if(lastVisitedElement) {
		lastVisitedElement.className = lastVisitedElement.oldCss;
	}
	lastVisitedElement = element;
	lastVisitedElement.oldCss = element.className;
	element.className = "menuItemActive";
}
</script>
</head>
<body class="menu">
<table border="0">
<? if (isset($shot)) { ?>
	<tr>
		<td class="user"><i>&nbsp;<?=$shot->firstName?></i></td>
	</tr>
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)" href="manual.php" target="mainFrame" class="menuItem">Hjälp</a></td>
	</tr>
<? } ?>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="logout.php" target="mainFrame" class="menuItem">Logga ut</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="setPassword.php" target="mainFrame" class="menuItem">Byt lösenord</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="editMyShot.php" target="mainFrame" class="menuItem">Min profil</a></td>
	</tr>
	<tr>
		<td class="menuItemBold">Anmälningar</td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="welcome.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&Ouml;versikt</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="availability.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Beläggning</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="enterCompetition.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Ny</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="myEntries.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Mina</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="clubEntries.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Min klubb</a></td>
	</tr>
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="startReport.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Startlista</a></td>
	</tr>
	
	<tr>
		<td><a onclick="onAClick(this)"  href="entryFee.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Betalning</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="unpaidFees.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Obetalda</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="showTeam.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Visa alla lag</a></td>
	</tr>
<? } ?>
	<tr>
		<td><a onclick="onAClick(this)"  onclick="onAClick(this)"  href="allEntries.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Samtliga</a></td>
	</tr>

<? if ($shot->userType == "ADMIN") { ?>
	<tr>
		<td class="menuItemBold">Anv&auml;ndare</td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="editShot.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;Modifiera</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="proxyShot.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;L&aring;tsas vara</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="competition.php" target="mainFrame" class="menuItemBold">T&auml;vling</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="competitionDay.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;T&auml;vlingsdag</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="patrol.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Patrull</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="editPatrolMatris.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Patrull alla</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="schedule.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Skapa Starttider</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="lagesrapport.php" target="mainFrame" class="menuItem">Lägesrapport</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="club.php" target="mainFrame" class="menuItemBold">Förening</a></td>
	</tr>
	
<? } ?>

	<tr>
		<td class="menuItemBold">Resultat</td>
	</tr>
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
	<tr>
		<td><a onclick="onAClick(this)"  href="staPlatsPDF.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Stå plats</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="patrolPDF.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Protokoll</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="enterPatrolScore.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Registrera</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="enterPatrolAdditionalScore.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Registrera finalskjutning</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="missingScores.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Saknas</a></td>
	</tr>
	
	
<? } ?>

	<tr>
		<td><a onclick="onAClick(this)"  href="showResult.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Visa</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="stats.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Stats</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="resultTeamReportSelect.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Resultatlista Lag</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="resultReportSelect.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Resultatlista Skytt detalj</a></td>
	</tr>
<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
	<tr>
		<td><a onclick="onAClick(this)"  href="showYearResult.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Visa årsresultat</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="showEmails.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Visa epost för anmälda</a></td>
	</tr>
<? } ?>
	<?if($shot->clubId == 383 || $shot->clubId == 22) {?>
	<tr>
		<td><a onclick="onAClick(this)"  href="traningShow.php" target="mainFrame" class="menuItemBold">Träningar alla</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="traningRegistrera.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Registrera och visa mina</a></td>
	</tr>
	<tr>
		<td><a onclick="onAClick(this)"  href="traningGodkanShow.php" target="mainFrame" class="menuItem">&nbsp;&nbsp;&nbsp;&nbsp;Visa mina godkännande</a></td>
	</tr>
	<?}?>

<? } else { ?>
<? } ?>
</table>


</body>
</html>
